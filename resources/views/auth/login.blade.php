<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — Sistem Presensi SMPN 1 Sedati</title>

  {{-- Bootstrap --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  {{-- Bootstrap Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  {{-- Google Font --}}
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #0d6efd, #004ecb);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-card {
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.1);
      padding: 40px 35px;
      width: 100%;
      max-width: 420px;
      animation: fadeIn 0.6s ease-in-out;
    }

    .login-card h3 {
      font-weight: 700;
      color: #004ecb;
      margin-bottom: 10px;
    }

    .login-card p {
      color: #6c757d;
      font-size: 14px;
      margin-bottom: 25px;
    }

    .form-control {
      border-radius: 10px;
      padding: 10px 15px;
      font-size: 14px;
    }

    .btn-login {
      border-radius: 10px;
      padding: 10px;
      font-weight: 600;
      font-size: 15px;
      background: linear-gradient(135deg, #0d6efd, #004ecb);
      border: none;
    }

    .btn-login:hover {
      background: linear-gradient(135deg, #004ecb, #003b9f);
    }

    .logo {
      width: 70px;
      height: 70px;
      margin-bottom: 15px;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>

<body>
  <div class="login-card text-center">
      <img src="LogoSmp1.png" alt="Logo" class="logo">

      <h3>Sistem Presensi</h3>
      <p>SMP Negeri 1 Sedati</p>

      <form action="{{ route('dashboard') }}" method="GET">
          <div class="mb-3 text-start">
              <label class="form-label fw-semibold">Username</label>
              <input type="text" name="username" class="form-control" placeholder="Masukkan username..." required>
          </div>

          <div class="mb-4 text-start">
              <label class="form-label fw-semibold">Password</label>
              <input type="password" name="password" class="form-control" placeholder="Masukkan password..." required>
          </div>

          <button type="submit" class="btn btn-login w-100 text-white">Masuk</button>
      </form>

      <p class="mt-4 small text-muted">
        © {{ date('Y') }} SMPN 1 Sedati — Sistem Presensi
      </p>
  </div>

  {{-- Bootstrap JS --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
