<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  {{-- 画面個別で @section('title') を出せる。無指定なら管理用タイトル --}}
  <title>@yield('title', '給与明細システム（管理）')</title>

  <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  @stack('head') {{-- ページ固有の <head> 追加（任意） --}}
</head>
<body class="bg-gray-50 text-gray-800">

  {{-- ヘッダー（管理専用） --}}
  <header class="bg-white shadow">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
      {{-- ロゴは必ず管理トップへ --}}
      <a href="{{ route('admin.home') }}" class="font-bold">給与明細システム（管理）</a>
    </div>
  </header>

  {{-- パンくず（任意） --}}
  @hasSection('breadcrumb')
    <div class="max-w-6xl mx-auto px-4 py-2 text-sm text-gray-500">
      @yield('breadcrumb')
    </div>
  @endif

  {{-- メイン --}}
  <main class="max-w-6xl mx-auto p-4">
    @yield('content')
  </main>

  @stack('scripts') {{-- ページ固有の <script> 追加（任意） --}}
</body>
</html>
