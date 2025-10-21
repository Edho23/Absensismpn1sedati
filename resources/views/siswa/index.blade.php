@extends('layouts.app')

@section('title', 'Manajemen Data Siswa')

@section('content')
<div class="container-fluid px-4 py-3">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary mb-0">🧑‍🏫 Manajemen Data Siswa</h3>
    </div>

    {{-- ================== FORM TAMBAH SISWA (PREMIUM STYLE) ================== --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body pb-2">
            <h6 class="fw-semibold text-secondary mb-3">Tambah Siswa Baru</h6>

            <form action="{{ route('siswa.store') }}" method="POST" class="row g-3 align-items-end">
                @csrf

                {{-- NIS --}}
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">NIS</label>
                    <div class="input-group input-group-lg shadow-sm rounded-pill">
                        <span class="input-group-text bg-white border-0 ps-3">
                            <i class="bi bi-person-badge text-primary"></i>
                        </span>
                        <input type="text" name="nis" class="form-control border-0 rounded-end-pill"
                               placeholder="Masukkan NIS" required>
                    </div>
                </div>

                {{-- Nama --}}
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Nama Siswa</label>
                    <div class="input-group input-group-lg shadow-sm rounded-pill">
                        <span class="input-group-text bg-white border-0 ps-3">
                            <i class="bi bi-person-fill text-success"></i>
                        </span>
                        <input type="text" name="nama" class="form-control border-0 rounded-end-pill"
                               placeholder="Masukkan Nama Lengkap" required>
                    </div>
                </div>

                {{-- Kelas --}}
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Kelas</label>
                    <div class="input-group input-group-lg shadow-sm rounded-pill">
                        <span class="input-group-text bg-white border-0 ps-3">
                            <i class="bi bi-building text-info"></i>
                        </span>
                        <select name="id_kelas" class="form-select border-0 rounded-end-pill" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>



                {{-- Tombol --}}
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm px-4 mt-2">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================== TABEL SISWA ================== --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-semibold text-secondary mb-0">Daftar Siswa</h6>
        </div>

        <div class="card-body px-4 pb-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-center small">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-center small">
                        @forelse($siswa as $s)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $s->nis }}</td>
                                <td class="fw-semibold text-dark">{{ $s->nama }}</td>
                                <td><span class="badge bg-info text-white rounded-pill px-3 py-2">{{ $s->kelas->nama_kelas ?? '-' }}</span></td>
                                <td>
                                    <span class="badge {{ $s->status_aktif ? 'bg-success' : 'bg-secondary' }} rounded-pill px-3 py-2">
                                        {{ $s->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('siswa.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Yakin hapus siswa ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted py-3">Belum ada data siswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
    .input-group-lg .form-control, .input-group-lg .form-select {
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

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .badge {
        font-size: 12.5px;
        letter-spacing: 0.3px;
    }

    .card {
        border-radius: 14px;
    }
</style>
@endpush
