@extends('layouts.app')

@section('title', 'Manajemen Data Kelas')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary mb-0">🏫 Manajemen Data Kelas</h3>
    </div>

    {{-- ALERT --}}
    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ================== FORM TAMBAH KELAS ================== --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body pb-2">
            <h6 class="fw-semibold text-secondary mb-3">Tambah Kelas Baru</h6>

            <form action="{{ route('kelas.store') }}" method="POST" class="row g-3 align-items-end">
                @csrf
                {{-- Nama Kelas --}}
                <div class="col-md-5">
                    <label class="form-label small fw-semibold text-secondary">Nama Kelas</label>
                    <div class="input-group input-group-lg shadow-sm rounded-pill">
                        <span class="input-group-text bg-white border-0 ps-3">
                            <i class="bi bi-building text-primary"></i>
                        </span>
                        <input type="text" name="nama_kelas" class="form-control border-0 rounded-end-pill"
                               placeholder="Contoh: IX-A" required>
                    </div>
                </div>

                {{-- Wali Kelas --}}
                <div class="col-md-5">
                    <label class="form-label small fw-semibold text-secondary">Wali Kelas</label>
                    <div class="input-group input-group-lg shadow-sm rounded-pill">
                        <span class="input-group-text bg-white border-0 ps-3">
                            <i class="bi bi-person-badge text-success"></i>
                        </span>
                        <input type="text" name="wali_kelas" class="form-control border-0 rounded-end-pill"
                               placeholder="Contoh: Pak Budi" required>
                    </div>
                </div>

                {{-- Tombol Simpan --}}
                <div class="col-md-2 text-end">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm px-4 mt-2">
                        <i class="bi bi-plus-circle"></i> Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================== TABEL KELAS ================== --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="fw-semibold text-secondary mb-0">Daftar Kelas</h6>
        </div>

        <div class="card-body px-4 pb-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-center small">
                        <tr>
                            <th>No</th>
                            <th>Nama Kelas</th>
                            <th>Wali Kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-center small">
                        @forelse($kelas as $k)
                            <tr>
                                <td>{{ $loop->iteration + ($kelas->currentPage() - 1) * $kelas->perPage() }}</td>
                                <td>
                                    <span class="badge bg-info text-white rounded-pill px-3 py-2">
                                        {{ $k->nama_kelas }}
                                    </span>
                                </td>
                                <td>{{ $k->wali_kelas }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        {{-- Edit --}}
                                        <a href="{{ route('kelas.edit', $k->id) }}" class="btn btn-sm btn-warning rounded-pill px-3">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>

                                        {{-- Hapus --}}
                                        <form action="{{ route('kelas.destroy', $k->id) }}" method="POST" onsubmit="return confirm('Yakin hapus kelas ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-muted py-3">Belum ada data kelas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-3 d-flex justify-content-center">
                {{ $kelas->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
    .input-group-lg .form-control {
        font-size: 14px;
        border-radius: 50px !important;
        box-shadow: none !important;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0d6efd, #0052cc);
        border: none;
        transition: all 0.2s ease-in-out;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        background: linear-gradient(135deg, #0052cc, #003d99);
    }

    .btn-warning {
        color: #fff;
        background: linear-gradient(135deg, #ffb02e, #ff8800);
        border: none;
    }

    .btn-warning:hover {
        background: linear-gradient(135deg, #ff8800, #cc6b00);
    }

    .badge {
        font-size: 12.5px;
        letter-spacing: 0.3px;
    }

    .card {
        border-radius: 14px;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush
