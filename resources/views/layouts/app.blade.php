<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />

@php
  $isAdmin   = session('is_admin') ? true : false;
  $appTitle  = $isAdmin ? '給与明細システム（管理）' : '給与明細システム';
  $homeUrl   = $isAdmin ? route('admin.home') : route('user.home');
@endphp

<title>{{ $title ?? $appTitle }}</title>
<!-- ファビコン -->
<link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
<link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
<!-- Tailwind CDN (開発簡易用) -->
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
<header class="bg-white shadow">
  <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
    {{-- ▼ ロゴ（遷移先と表示名を役割で切り替え） --}}
    <a href="{{ $homeUrl }}" class="font-bold">{{ $appTitle }}</a>

    <nav class="flex items-center gap-3">
      @if(session('user_id'))
        <form method="POST" action="{{ route('logout') }}">@csrf
          <button class="text-sm text-gray-600">ログアウト</button>
        </form>
      @endif

      @if($isAdmin)
        <a class="text-sm text-indigo-600" href="{{ route('admin.home') }}">管理</a>
        <form method="POST" action="{{ route('admin.logout') }}" class="inline">@csrf
          <button class="text-sm text-gray-600">管理ログアウト</button>
        </form>
      @endif
    </nav>
  </div>
</header>

<main class="max-w-5xl mx-auto p-4">@yield('content')</main>
</body>
</html>
