<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','Dashboard') — Sistem Absensi</title>
  <link rel="stylesheet" href="/css/app.css">
</head>
<body class="layout">
  @include('layouts.navbar')
  <div class="container">
    @include('layouts.sidebar')
    <main class="main">
      @yield('content')
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="/js/app.js"></script>
  @stack('scripts')
</body>
</html>
