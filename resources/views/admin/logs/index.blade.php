@extends('layouts.admin')

@section('content')
<div class="bg-white shadow rounded-xl p-4">
  <div class="flex items-center justify-between mb-4">
    <h1 class="font-bold">ログ閲覧(※ダミー)</h1>
    <a href="{{ route('admin.home') }}" class="text-sm border rounded px-3 py-1 hover:bg-gray-50">戻る</a>
  </div>

  {{-- タブ --}}
  @php
    $tabs = [
      'auth'     => '認証/認可',
      'view'     => '閲覧アクセス',
      'audit'    => '変更監査',
      'error'    => 'エラー',
      'user_pdf' => 'PDF',
    ];
  @endphp

  <div class="flex gap-2 mb-4">
    @foreach($tabs as $key => $label)
      <a href="{{ route('admin.logs.index', array_merge(request()->except('page'), ['type' => $key])) }}"
         class="px-3 py-1 rounded border {{ $type === $key ? 'bg-gray-800 text-white' : 'hover:bg-gray-50' }}">
        {{ $label }}
      </a>
    @endforeach
  </div>

  {{-- フィルタ --}}
  <form method="GET" action="{{ route('admin.logs.index') }}" class="mb-4">
    <input type="hidden" name="type" value="{{ $type }}">
    <div class="flex flex-col sm:flex-row gap-2 items-end">
      <div>
        <label class="block text-xs text-gray-600">開始日</label>
        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="border rounded px-3 py-2">
      </div>
      <div>
        <label class="block text-xs text-gray-600">終了日</label>
        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="border rounded px-3 py-2">
      </div>
      <div>
        <label class="block text-xs text-gray-600">ID</label>
        <input type="number" name="actor_id" value="{{ $filters['actor_id'] ?? '' }}" class="border rounded px-3 py-2 w-32">
      </div>
      <div>
        <button class="px-3 py-2 border rounded hover:bg-gray-50">適用</button>
      </div>
    </div>
  </form>

  {{-- 一覧 --}}
  @if ($type === 'auth')
    <x-admin.log-table>
      <x-slot:head>
        <th class="p-2 text-left">日時</th>
        <th class="p-2 text-left">ユーザーID</th>
        <th class="p-2 text-left">ロール</th>
        <th class="p-2 text-left">結果</th>
        <th class="p-2 text-left">理由</th>
        <th class="p-2 text-left">IP</th>
        <th class="p-2 text-left">UA</th>
      </x-slot:head>
      @foreach($logs as $r)
        <tr class="border-t">
          <td class="p-2">{{ $r->ts }}</td>
          <td class="p-2">{{ $r->user_id }}</td>
          <td class="p-2">{{ $r->role ?? '-' }}</td>
          <td class="p-2">{{ $r->result }}</td>
          <td class="p-2">{{ $r->reason ?? '-' }}</td>
          <td class="p-2">{{ $r->ip }}</td>
          <td class="p-2 truncate max-w-[280px]" title="{{ $r->ua }}">{{ $r->ua }}</td>
        </tr>
      @endforeach
    </x-admin.log-table>

  @elseif ($type === 'view')
    <x-admin.log-table>
      <x-slot:head>
        <th class="p-2 text-left">日時</th>
        <th class="p-2 text-left">実行者ID</th>
        <th class="p-2 text-left">対象種別</th>
        <th class="p-2 text-left">対象ID</th>
        <th class="p-2 text-left">IP</th>
        <th class="p-2 text-left">UA</th>
        <th class="p-2 text-left">ReqID</th>
      </x-slot:head>
      @foreach($logs as $r)
        <tr class="border-t">
          <td class="p-2">{{ $r->ts }}</td>
          <td class="p-2">{{ $r->actor_id }}</td>
          <td class="p-2">{{ $r->entity }}</td>
          <td class="p-2">{{ $r->entity_id }}</td>
          <td class="p-2">{{ $r->ip }}</td>
          <td class="p-2 truncate max-w-[280px]" title="{{ $r->ua }}">{{ $r->ua }}</td>
          <td class="p-2">{{ $r->request_id }}</td>
        </tr>
      @endforeach
    </x-admin.log-table>

  @elseif ($type === 'audit')
    <x-admin.log-table>
      <x-slot:head>
        <th class="p-2 text-left">日時</th>
        <th class="p-2 text-left">実行者ID</th>
        <th class="p-2 text-left">アクション</th>
        <th class="p-2 text-left">対象</th>
        <th class="p-2 text-left">対象ID</th>
        <th class="p-2 text-left">Before</th>
        <th class="p-2 text-left">After</th>
      </x-slot:head>
      @foreach($logs as $r)
        <tr class="border-t align-top">
          <td class="p-2 whitespace-nowrap">{{ $r->ts }}</td>
          <td class="p-2">{{ $r->actor_id }}</td>
          <td class="p-2">{{ $r->action }}</td>
          <td class="p-2">{{ $r->entity }}</td>
          <td class="p-2">{{ $r->entity_id }}</td>
          <td class="p-2 text-xs">
            <pre class="whitespace-pre-wrap">{{ Str::limit(json_encode(json_decode($r->before_json), JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), 1000) }}</pre>
          </td>
          <td class="p-2 text-xs">
            <pre class="whitespace-pre-wrap">{{ Str::limit(json_encode(json_decode($r->after_json),  JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), 1000) }}</pre>
          </td>
        </tr>
      @endforeach
    </x-admin.log-table>

  @elseif ($type === 'error')
    <x-admin.log-table>
      <x-slot:head>
        <th class="p-2 text-left">日時</th>
        <th class="p-2 text-left">レベル</th>
        <th class="p-2 text-left">メッセージ</th>
        <th class="p-2 text-left">ReqID</th>
      </x-slot:head>
      @foreach($logs as $r)
        <tr class="border-t">
          <td class="p-2">{{ $r->ts }}</td>
          <td class="p-2">{{ $r->level }}</td>
          <td class="p-2"><pre class="whitespace-pre-wrap text-xs">{{ $r->message }}</pre></td>
          <td class="p-2">{{ $r->request_id }}</td>
        </tr>
      @endforeach
    </x-admin.log-table>

  @elseif ($type === 'user_pdf')
    <x-admin.log-table>
      <x-slot:head>
        <th class="p-2 text-left">日時</th>
        <th class="p-2 text-left">ID</th>
        <th class="p-2 text-left">範囲/対象</th>
        <th class="p-2 text-left">チェックサム</th>
        <th class="p-2 text-left">保存先</th>
        <th class="p-2 text-left">ReqID</th>
      </x-slot:head>
      @foreach($logs as $r)
        <tr class="border-t">
          <td class="p-2">{{ $r->ts }}</td>
          <td class="p-2">{{ $r->actor_id }}</td>
          <td class="p-2">{{ $r->scope }}</td>
          <td class="p-2 text-xs">{{ $r->checksum }}</td>
          <td class="p-2 truncate max-w-[320px]" title="{{ $r->path }}">{{ $r->path }}</td>
          <td class="p-2">{{ $r->request_id }}</td>
        </tr>
      @endforeach
    </x-admin.log-table>
  @endif

  <div class="mt-3">{{ $logs->links() }}</div>
</div>
@endsection

{{-- シンプルなテーブル用コンポーネント（resources/views/components/admin/log-table.blade.php） --}}
@once
  @push('components')
  @endpush
@endonce
