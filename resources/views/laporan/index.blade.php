@extends('layouts.app')

@section('title', 'Laporan Kehadiran')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary mb-0">
            <i class="bi bi-bar-chart-line me-2"></i> Laporan Kehadiran
        </h3>
        <a href="{{ route('laporan.export') }}" class="btn btn-success rounded-pill shadow-sm px-4">
            <i class="bi bi-download"></i> Unduh Laporan
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body">
            <p class="text-muted mb-4">Gunakan menu ini untuk meninjau atau mengunduh laporan absensi berdasarkan periode tertentu.</p>
            <form method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Dari Tanggal</label>
                    <input type="date" name="dari" class="form-control rounded-pill shadow-sm" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Sampai Tanggal</label>
                    <input type="date" name="sampai" class="form-control rounded-pill shadow-sm" required>
                </div>
                <div class="col-md-4 text-end">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm px-4 mt-2">
                        <i class="bi bi-search"></i> Tampilkan
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporan as $l)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($l->tanggal)->format('d-m-Y') }}</td>
                                <td>{{ $l->siswa->nama ?? '-' }}</td>
                                <td>{{ $l->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $l->status_harian }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted py-3">Tidak ada data untuk periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
