<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Sistem Presensi</title>

    
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    
    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Google Fonts: Poppins --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Custom Global CSS (semua styling dipusatkan di sini) --}}
    <link rel="stylesheet" href="/css/app.css">

    @stack('styles')
</head>
<body class="layout">

    {{-- =============== NAVBAR =============== --}}
    @include('layouts.navbar')

    {{-- =============== BODY (SIDEBAR + CONTENT) =============== --}}
    <div class="layout-container">
        @include('layouts.sidebar')

        <main class="main">
            @yield('content')
        </main>
    </div>

    {{-- JS Library --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/js/app.js"></script>

    @stack('scripts')
</body>
</html>
