@extends('layouts.app')

@section('title', 'Kartu RFID')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary mb-0">
            <i class="bi bi-credit-card-2-front me-2"></i> Manajemen Kartu RFID
        </h3>
    </div>

    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FORM TAMBAH KARTU --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body pb-2">
            <h6 class="fw-semibold text-secondary mb-3">Tambah Kartu RFID</h6>
            <form action="{{ route('kartu.store') }}" method="POST" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-5">
                    <label class="form-label small fw-semibold text-secondary">UID Kartu</label>
                    <input type="text" name="uid" class="form-control rounded-pill shadow-sm" placeholder="Contoh: RFID-123456" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small fw-semibold text-secondary">NIS Siswa</label>
                    <input type="text" name="nis" class="form-control rounded-pill shadow-sm" placeholder="Masukkan NIS siswa" required>
                </div>
                <div class="col-md-2 text-end">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm px-4 mt-2">
                        <i class="bi bi-plus-circle"></i> Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- TABEL RFID --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="fw-semibold text-secondary mb-0">Daftar Kartu RFID</h6>
        </div>
        <div class="card-body px-4 pb-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>UID</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kartu as $k)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $k['uid'] }}</td>
                                <td>{{ $k['nis'] ?? '-' }}</td>
                                <td>{{ $k['nama_siswa'] ?? '-' }}</td>
                                <td>
                                    <span class="badge rounded-pill {{ $k['status'] == 'Aktif' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $k['status'] }}
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('kartu.destroy', $k['id']) }}" method="POST" onsubmit="return confirm('Hapus kartu ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-muted py-3">Belum ada data kartu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
