@extends('layouts.app')
@section('title','Profil Admin')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary mb-0">
            <i class="bi bi-person-gear me-2"></i>Profil Admin
        </h3>
    </div>

    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('pengaturan.update') }}" autocomplete="off" class="row g-3">
                @csrf

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control" value="{{ old('username',$admin->username) }}" required>
                    <div class="form-text">Gunakan nama unik untuk login.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Terakhir Login</label>
                    <input type="text" class="form-control" value="{{ $admin->last_login_at ? \Carbon\Carbon::parse($admin->last_login_at)->format('d/m/Y H:i') : '-' }}" disabled>
                </div>

                <hr class="mt-3">

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Password Lama</label>
                    <input type="password" name="current_password" class="form-control" placeholder="Isi jika ingin ganti password">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 8 karakter">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                </div>

                <div class="col-12 text-end mt-3">
                    <button class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ===================== LOG ADMIN (HIDDEN ACCESS HERE) ===================== --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body d-flex align-items-center justify-content-between">
            <div>
                <h6 class="fw-bold mb-1">Log Admin</h6>
                <small class="text-muted">Riwayat tindakan admin (hapus/ganti/tambah) untuk audit.</small>
            </div>
            <a href="{{ route('admin.logs') }}" class="btn btn-outline-primary rounded-pill px-4">
                <i class="bi bi-shield-lock me-1"></i> Buka Log Admin
            </a>
        </div>
    </div>
</div>
@endsection
