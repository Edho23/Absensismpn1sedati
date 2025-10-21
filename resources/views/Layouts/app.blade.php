<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Sistem Presensi</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- Custom Global CSS --}}
    <link rel="stylesheet" href="/css/app.css">

    {{-- Google Fonts: Poppins --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Tambahan Style dari Komponen (Sidebar, Page, dsb) --}}
    @stack('styles')

    <style>
        /* =============== GLOBAL STYLING =============== */
        body.layout {
            font-family: 'Poppins', sans-serif;
            background-color: #f7f9fc;
            color: #333;
            margin: 0;
            overflow-x: hidden;
        }

        .layout {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        .container {
            display: flex;
            flex: 1;
            padding: 0;
        }

        /* Sidebar dan Main */
        aside.sidebar {
            width: 250px;
            flex-shrink: 0;
            z-index: 1000;
        }

        main.main {
            flex: 1;
            padding: 30px;
            margin-left: 250px;
            background-color: #f8fafc;
            min-height: 100vh;
            transition: all 0.3s ease-in-out;
        }

        /* Navbar */
        .navbar {
            background: linear-gradient(135deg, #0d6efd, #56ADF7);
            color: #fff;
            padding: 12px 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .navbar .nav-brand {
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 0.3px;
        }

        .navbar .nav-actions i {
            font-size: 1.3rem;
            margin-left: 18px;
            cursor: pointer;
            opacity: 0.85;
            transition: 0.2s;
        }

        .navbar .nav-actions i:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        /* Scrollbar lembut */
        ::-webkit-scrollbar {
            width: 7px;
        }
        ::-webkit-scrollbar-thumb {
            background-color: rgba(0,0,0,0.15);
            border-radius: 10px;
        }

        /* Page Title */
        .page-title {
            font-weight: 600;
            color: #0d6efd;
            margin-bottom: 20px;
        }

        /* Card styling global */
        .card-custom {
            background: #fff;
            border-radius: 12px;
            border: none;
            padding: 20px 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body class="layout">

    {{-- =============== NAVBAR =============== --}}
    @include('layouts.navbar')

    {{-- =============== BODY (SIDEBAR + CONTENT) =============== --}}
    <div class="container">
        @include('layouts.sidebar')

        <main class="main">
            @yield('content')
        </main>
    </div>

    {{-- JS Library --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/js/app.js"></script>

    {{-- Tambahan script dari halaman lain --}}
    @stack('scripts')

</body>
</html>
