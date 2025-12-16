{{-- resources/views/layouts/navbar.blade.php --}}
<nav class="app-navbar">
  <div class="navbar-left">
    <img src="/LogoSmp1.png" alt="Logo" class="navbar-logo">
    <div class="navbar-brand">
      <div class="brand-title">SMP Negeri 1 Sedati</div>
      <div class="brand-sub">Sistem Presensi</div>
    </div>
  </div>
  <div class="navbar-right">
    <form action="{{ route('admin.logout') }}" method="POST" class="d-inline">
      @csrf
      <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
    </form>
  </div>
</nav>
