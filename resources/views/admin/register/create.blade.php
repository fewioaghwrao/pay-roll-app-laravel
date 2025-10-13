@extends('layouts.admin')

@section('content')
<div class="bg-white shadow rounded-xl p-6 max-w-lg mx-auto">
<div class="flex items-center justify-between mb-4">
  <h1 class="font-bold text-lg">管理者登録</h1>

  <div class="flex items-center gap-2">
    <a href="{{ route('admin.home') }}" class="text-sm text-gray-600 hover:underline">戻る</a>

    {{-- 管理者解除（自分をadminsから削除 → ログアウト） --}}
    <form method="POST"
          action="{{ route('admin.unregister') }}"
          onsubmit="return confirm('本当に管理者登録を解除してログアウトしますか？\n（この操作は元に戻せません）');">
      @csrf
      @method('DELETE')
      <button type="submit"
              class="text-sm px-3 py-1 border rounded text-red-700 border-red-300 hover:bg-red-50">
        管理者解除
      </button>
    </form>
  </div>
</div>

  @if (session('status'))
    <p class="text-green-700 text-sm mb-3">{{ session('status') }}</p>
  @endif

  @if ($errors->any())
    <div class="mb-3 text-sm text-red-600">
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.register.store') }}" class="space-y-4">
    @csrf

    <div>
      <label class="block text-sm mb-1" for="name">名前</label>
      <input id="name" name="name" type="text" value="{{ old('name') }}"
             class="w-full border rounded px-3 py-2" required>
    </div>

    <div>
      <label class="block text-sm mb-1" for="email">メールアドレス</label>
      <input id="email" name="email" type="email" value="{{ old('email') }}"
             class="w-full border rounded px-3 py-2" required>
    </div>

    <div>
      <label class="block text-sm mb-1" for="password">パスワード</label>
      <input id="password" name="password" type="password"
             class="w-full border rounded px-3 py-2" required>
      <p class="text-xs text-gray-500 mt-1">8文字以上</p>
    </div>

    <div>
      <label class="block text-sm mb-1" for="password_confirmation">パスワード（確認）</label>
      <input id="password_confirmation" name="password_confirmation" type="password"
             class="w-full border rounded px-3 py-2" required>
    </div>

    <div class="pt-2">
      <button type="submit" class="px-4 py-2 border rounded hover:bg-gray-50">
        登録する
      </button>
    </div>
  </form>
</div>
@endsection
