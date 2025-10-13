@extends('layouts.app')

@section('title', 'Input Manual Absensi')

@section('content')
<div class="container-fluid px-4 py-3">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary mb-0">📝 Input Manual Absensi</h3>
    </div>

    {{-- ALERT --}}
    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ================== FORM INPUT MANUAL ================== --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <h6 class="fw-bold text-secondary mb-3">Tambah Absensi Manual Hari Ini</h6>
            <form action="{{ route('absensi.manual') }}" method="POST">
                @csrf
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-secondary">Nama / NIS</label>
                        <select name="nis" class="form-select form-select-sm rounded-3" required>
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($siswa as $s)
                                <option value="{{ $s->nis }}">{{ $s->nis }} - {{ $s->nama }} ({{ $s->kelas->nama_kelas ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-secondary">Status Kehadiran</label>
                        <select name="status_harian" class="form-select form-select-sm rounded-3">
                            <option value="HADIR">Hadir</option>
                            <option value="SAKIT">Sakit</option>
                            <option value="ALPA">Alpa</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-secondary">Catatan</label>
                        <input type="text" name="catatan" class="form-control form-control-sm rounded-3" placeholder="Opsional...">
                    </div>

                    <div class="col-md-2 text-md-end text-start mt-3 mt-md-0">
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ================== TABEL ABSENSI HARI INI ================== --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="fw-semibold text-secondary mb-0">Daftar Absensi Hari Ini ({{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }})</h6>
        </div>
        <div class="card-body px-4 pb-3">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-center small">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Jam Masuk</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="text-center small">
                        @forelse($absensi as $a)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $a->siswa->nis ?? '-' }}</td>
                                <td>{{ $a->siswa->nama ?? '-' }}</td>
                                <td>{{ $a->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>
                                    <span class="badge 
                                        @if($a->status_harian === 'HADIR') bg-success 
                                        @elseif($a->status_harian === 'SAKIT') bg-warning text-dark 
                                        @else bg-danger @endif 
                                        rounded-pill px-3 py-2">
                                        {{ $a->status_harian }}
                                    </span>
                                </td>
                                <td>{{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '-' }}</td>
                                <td>{{ $a->catatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-muted py-3">Belum ada data absensi hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3 d-flex justify-content-center">
                {{ $absensi->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .form-label {
        font-size: 13px;
        color: #555;
    }

    .form-control-sm, .form-select-sm {
        font-size: 13px;
        border-radius: 8px;
        padding: 6px 10px;
    }

    .btn-sm {
        font-size: 13px;
    }

    .table {
        font-size: 13px;
    }

    .table th, .table td {
        vertical-align: middle !important;
        padding-top: 8px !important;
        padding-bottom: 8px !important;
    }

    .card {
        border-radius: 10px;
    }

    .btn-primary {
        background-color: #0d6efd;
        border: none;
    }

    .btn-primary:hover {
        background-color: #0b5ed7;
    }
</style>
@endpush
