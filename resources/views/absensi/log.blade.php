@extends('layouts.app')
@section('title', 'Log Absensi')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary mb-0"><i class="bi bi-clock-history me-2"></i>Log Absensi</h3>
    </div>

    {{-- Filter Form --}}
    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body">
            <form method="GET" action="{{ route('absensi.log') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Kelas</label>
                    <select name="kelas" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($daftarKelas as $k)
                            <option value="{{ $k }}" {{ $kelas == $k ? 'selected' : '' }}>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="HADIR" {{ $status == 'HADIR' ? 'selected' : '' }}>Hadir</option>
                        <option value="SAKIT" {{ $status == 'SAKIT' ? 'selected' : '' }}>Sakit</option>
                        <option value="IZIN"  {{ $status == 'IZIN'  ? 'selected' : '' }}>Izin</option> {{-- ✅ Tambahan baru --}}
                        <option value="ALPA"  {{ $status == 'ALPA'  ? 'selected' : '' }}>Alpa</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex">
                    <button type="submit" class="btn btn-primary w-100 me-2">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('absensi.log') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Log Absensi --}}
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
                        <th>Jam Pulang</th>
                        <th>Sumber</th>
                        <th>Status</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($absensi as $i => $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->siswa->nis ?? '-' }}</td>
                            <td class="text-start">{{ $item->siswa->nama ?? '-' }}</td>
                            <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                            <td>
                                {{ $item->jam_masuk 
                                    ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') 
                                    : '-' }}
                            </td>
                            <td>
                                {{ $item->jam_pulang 
                                    ? \Carbon\Carbon::parse($item->jam_pulang)->format('H:i') 
                                    : '-' }}
                            </td>
                            <td>
                                <span class="badge {{ $item->sumber === 'MANUAL' ? 'bg-primary' : 'bg-success' }}">
                                    {{ $item->sumber ?? '-' }}
                                </span>
                            </td>
                            <td>
                                @if($item->status_harian === 'HADIR')
                                    <span class="badge bg-success">Hadir</span>
                                @elseif($item->status_harian === 'SAKIT')
                                    <span class="badge bg-warning text-dark">Sakit</span>
                                @elseif($item->status_harian === 'IZIN')
                                    <span class="badge bg-info text-dark">Izin</span> {{-- ✅ Tambahan baru --}}
                                @elseif($item->status_harian === 'ALPA')
                                    <span class="badge bg-danger">Alpa</span>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </td>
                            <td>{{ $item->catatan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-muted py-3">Tidak ada data absensi ditemukan.</td>
                        </tr>
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
