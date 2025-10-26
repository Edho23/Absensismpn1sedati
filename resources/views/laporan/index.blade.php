@extends('layouts.app')
@section('title', 'Laporan Kehadiran')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary mb-0"><i class="bi bi-file-earmark-text me-2"></i>Laporan Kehadiran</h3>
        <a href="{{ route('laporan.export') }}" class="btn btn-success rounded-pill">
            <i class="bi bi-download me-2"></i>Unduh Laporan
        </a>
    </div>

    {{-- Filter Section --}}
    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Kelas</label>
                    <select name="kelas" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach ($daftarKelas ?? [] as $k)
                            <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="HADIR" {{ request('status') == 'HADIR' ? 'selected' : '' }}>Hadir</option>
                        <option value="SAKIT" {{ request('status') == 'SAKIT' ? 'selected' : '' }}>Sakit</option>
                        <option value="ALPA" {{ request('status') == 'ALPA' ? 'selected' : '' }}>Alpa</option>
                        <option value="IZIN" {{ request('status') == 'IZIN' ? 'selected' : '' }}>Izin</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex">
                    <button type="submit" class="btn btn-primary w-100 me-2">
                        <i class="bi bi-search me-2"></i>Filter
                    </button>
                    <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Laporan Kehadiran --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Sumber</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($absensi ?? [] as $i => $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->siswa->nis ?? '-' }}</td>
                            <td class="text-start">{{ $item->siswa->nama ?? '-' }}</td>
                            <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') : '-' }}</td>
                            <td>
                                <span class="badge {{ $item->sumber == 'MANUAL' ? 'bg-primary' : 'bg-success' }}">
                                    {{ $item->sumber }}
                                </span>
                            </td>
                            <td>
                                @if ($item->status_harian == 'HADIR')
                                    <span class="badge bg-success">Hadir</span>
                                @elseif ($item->status_harian == 'SAKIT')
                                    <span class="badge bg-warning text-dark">Sakit</span>
                                @else
                                    <span class="badge bg-danger">Alpa</span>
                                @endif
                            </td>
                            <td>{{ $item->catatan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-muted py-3">Tidak ada data absensi yang ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="mt-3 d-flex justify-content-center">
                {{ $absensi->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
