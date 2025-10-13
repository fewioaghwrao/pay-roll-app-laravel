@extends('layouts.admin')
@section('content')
{{-- 見出し行のすぐ下あたりに追加 --}}
<form method="GET" action="{{ route('admin.home') }}" class="mb-4">
  <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-end">
    <div>
      <label class="block text-xs text-gray-600">検索（社員番号 or 氏名）</label>
      <input type="text" name="q" value="{{ request('q') }}" placeholder="例）10023 or 山田太郎"
             class="border rounded px-3 py-2 w-64">
    </div>

    {{-- 任意：雇用形態で絞り込みたい場合 --}}
    <div>
      <label class="block text-xs text-gray-600">雇用形態</label>
      <select name="employment_type" class="border rounded px-3 py-2">
        <option value="">すべて</option>
        <option value="正社員"   @selected(request('employment_type')==='正社員')>正社員</option>
        <option value="契約社員" @selected(request('employment_type')==='契約社員')>契約社員</option>
      </select>
    </div>

<div class="flex gap-2">
  <button class="px-3 py-2 border rounded hover:bg-gray-50">検索</button>

  <a href="{{ route('admin.home') }}" class="px-3 py-2 border rounded">クリア</a>

  {{-- 🔽 追加：CSV取込ボタン --}}
  <a href="{{ route('admin.csv.create') }}"
     class="px-3 py-2 border rounded bg-indigo-600 text-white hover:bg-indigo-500 transition">
    CSV取込
  </a>
  <a href="{{ route('admin.logs.index') }}"
   class="px-3 py-2 border rounded hover:bg-gray-50">
  ログ閲覧
  </a>
</div>
  </div>
</form>
<div class="bg-white shadow rounded-xl p-4">
<div class="flex items-center justify-between mb-4">
  <h1 class="font-bold">管理画面</h1>

  {{-- 右側：ログアウト + ユーザー画面へ --}}
<div class="flex items-center gap-2">
  <form method="POST" action="{{ route('admin.logout') }}">
    @csrf
    <button type="submit" class="px-3 py-1 text-sm border rounded hover:bg-gray-50">
      ログアウト
    </button>
  </form>

  <a href="{{ route('user.home') }}" class="px-3 py-1 text-sm border rounded hover:bg-gray-50">
    ユーザー画面へ
  </a>

  {{-- 追加：社員登録ボタン --}}
  <a href="{{ route('admin.employee.create') }}"
     class="px-3 py-1 text-sm border rounded hover:bg-gray-50">
    社員登録
  </a>

  {{-- 既存：管理者登録ボタン --}}
  <a href="{{ route('admin.register.create') }}"
     class="px-3 py-1 text-sm border rounded hover:bg-gray-50">
    管理者登録
  </a>
</div>
</div>


@if(session('status'))<p class="text-green-700 text-sm mb-3">{{ session('status') }}</p>@endif


<table class="w-full text-sm border">
<thead class="bg-gray-100">
<tr>
<th class="p-2 text-left">社員番号</th>
<th class="p-2 text-left">社員名</th>
<th class="p-2 text-left">雇用形態</th>
<th class="p-2 text-left">メールアドレス</th>
<th class="p-2"></th>
</tr>
</thead>
<tbody>
@if ($users->count() === 0)
  <tr>
    <td colspan="5" class="p-4 text-center text-gray-500">
      該当する社員が見つかりませんでした。
    </td>
  </tr>
@else
@foreach($users as $u)
<tr class="border-t">
<td class="p-2">{{ $u->employee_number ?? '-' }}</td>
<td class="p-2">{{ $u->name }}</td>
<td class="p-2">{{ $u->employment_type }}</td>
<td class="p-2">{{ $u->email }}</td>
<td class="p-2 text-right">
<a href="{{ route('admin.user.show',$u) }}" class="px-3 py-1 border rounded">詳細</a>
</td>
</tr>
@endforeach
@endif
</tbody>
</table>


<div class="mt-3">{{ $users->links() }}</div>
</div>
@endsection