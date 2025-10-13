{{-- resources/views/admin/auth/login.blade.php --}}
@extends('layouts.admin')

@section('content')
<div class="bg-white shadow rounded-xl p-6 max-w-md mx-auto">
  <h1 class="font-bold mb-4">管理者ログイン</h1>

  @if ($errors->any())
    <div class="mb-3 text-sm text-red-600">
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-4">
    @csrf
    <div>
      <label class="block text-sm mb-1">メールアドレス</label>
      <input type="email" name="email" value="{{ old('email') }}" required class="w-full border rounded px-3 py-2">
    </div>
    <div>
      <label class="block text-sm mb-1">パスワード</label>
      <input type="password" name="password" required class="w-full border rounded px-3 py-2">
    </div>
    <label class="inline-flex items-center gap-2 text-sm">
      <input type="checkbox" name="remember"> ログイン状態を保持
    </label>
    <div>
      <button class="px-4 py-2 border rounded hover:bg-gray-50">ログイン</button>
    </div>
  </form>
</div>
@endsection

