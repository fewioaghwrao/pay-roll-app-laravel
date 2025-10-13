@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto bg-white rounded-xl shadow p-6">
<h1 class="text-lg font-bold mb-4">社員ログイン</h1>
@if($errors->any())<p class="text-red-600 text-sm mb-2">{{ $errors->first() }}</p>@endif
<form method="POST" action="{{ route('login.do') }}" class="space-y-3">
@csrf
<label class="block">
<span class="text-sm">メールアドレス</span>
<input name="email" type="email" class="mt-1 w-full border rounded p-2" required />
</label>
<label class="block">
<span class="text-sm">パスワード</span>
<input name="password" type="password" class="mt-1 w-full border rounded p-2" required />
</label>
<button class="w-full bg-indigo-600 text-white py-2 rounded">ログイン</button>
</form>
</div>
@endsection