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

    {{-- ====== Profil Admin (as is) ====== --}}
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

    {{-- ====== Hari Libur Sekolah ====== --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-1"><i class="bi bi-calendar-x me-2"></i>Hari Libur Sekolah</h5>
                    <small class="text-muted">Tanggal yang ditandai sebagai hari libur akan diwarnai merah pada laporan absen bulanan dan tidak dihitung ke rekap.</small>
                </div>
            </div>

            <form action="{{ route('pengaturan.hari_libur.store') }}" method="POST" class="row g-3 align-items-end mb-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Nama Hari Libur</label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Hari Guru Nasional" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Berulang Tiap Tahun?</label>
                    <select name="berulang" class="form-select">
                        <option value="1">Ya</option>
                        <option value="0">Tidak</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success w-100 rounded-pill">
                        <i class="bi bi-plus-circle me-1"></i> Tambah
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width:70px">#</th>
                            <th>Tanggal</th>
                            <th>Nama</th>
                            <th>Berulang</th>
                            <th style="width:120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($libur as $i => $h)
                            <tr>
                                <td class="text-center">{{ $i+1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($h->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ $h->nama }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $h->berulang ? 'bg-primary' : 'bg-secondary' }}">
                                        {{ $h->berulang ? 'Ya' : 'Tidak' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('pengaturan.hari_libur.destroy',$h->id) }}" method="POST" onsubmit="return confirm('Hapus hari libur ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger rounded-pill">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">Belum ada data hari libur.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- (opsional) Log admin --}}
    <div class="card border-0 shadow-sm rounded-4 mt-4">
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
