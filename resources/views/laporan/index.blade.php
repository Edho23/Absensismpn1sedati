@extends('layouts.app')
@section('title', 'Laporan Kehadiran')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary mb-0">
            <i class="bi bi-file-earmark-text me-2"></i>Laporan Kehadiran
        </h3>
        <div class="d-flex gap-2">
            <a href="{{ route('laporan.index', array_merge(request()->query(), ['mode'=>'detail'])) }}"
               class="btn btn-outline-primary rounded-pill {{ ($mode ?? 'detail')==='detail' ? 'active' : '' }}">
               Detail
            </a>
            <a href="{{ route('laporan.index', array_merge(request()->query(), ['mode'=>'rekap'])) }}"
               class="btn btn-outline-primary rounded-pill {{ ($mode ?? 'detail')==='rekap' ? 'active' : '' }}">
               Rekap per Siswa
            </a>
            <a href="{{ route('laporan.export') }}" class="btn btn-success rounded-pill">
                <i class="bi bi-download me-2"></i>Unduh Laporan
            </a>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body">
            <form method="GET" action="{{ route('laporan.index') }}" class="row g-3 align-items-end">
                <input type="hidden" name="mode" value="{{ $mode ?? 'detail' }}">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ $tanggalMulai }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ $tanggalSelesai }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Kelas</label>
                    <select name="kelas" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach ($daftarKelas as $k)
                            <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>
                                {{ $k }}
                            </option>
                        @endforeach
                    </select>
                </div>
                {{-- Status hanya relevan untuk DETAIL --}}
                @if(($mode ?? 'detail') === 'detail')
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="HADIR" {{ request('status') == 'HADIR' ? 'selected' : '' }}>Hadir</option>
                            <option value="SAKIT" {{ request('status') == 'SAKIT' ? 'selected' : '' }}>Sakit</option>
                            <option value="IZIN"  {{ request('status') == 'IZIN'  ? 'selected' : '' }}>Izin</option>
                            <option value="ALPA"  {{ request('status') == 'ALPA'  ? 'selected' : '' }}>Alpa</option>
                        </select>
                    </div>
                @endif
                <div class="col-md-2 d-flex">
                    <button type="submit" class="btn btn-primary w-100 me-2">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== MODE: REKAP PER SISWA ========== --}}
    @if(($mode ?? 'detail') === 'rekap')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Hadir</th>
                        <th>Sakit</th>
                        <th>Izin</th>
                        <th>Alpa</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekap as $i => $r)
                        <tr>
                            <td>{{ $rekap->firstItem() + $i }}</td>
                            <td>{{ $r->nis }}</td>
                            <td class="text-start">{{ $r->nama }}</td>
                            <td>{{ $r->nama_kelas }}</td>
                            <td><span class="badge bg-success">{{ $r->hadir }}</span></td>
                            <td><span class="badge bg-warning text-dark">{{ $r->sakit }}</span></td>
                            <td><span class="badge bg-info text-dark">{{ $r->izin }}</span></td>
                            <td><span class="badge bg-danger">{{ $r->alpa }}</span></td>
                            <td class="fw-bold">{{ $r->total }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-muted py-3">Tidak ada data rekap.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3 d-flex justify-content-center">
                {{ $rekap->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
    @else
    {{-- ========== MODE: DETAIL (ASAL) ========== --}}
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
                            <td>{{ $absensi->firstItem() + $i }}</td>
                            <td>{{ $item->siswa->nis ?? '-' }}</td>
                            <td class="text-start">{{ $item->siswa->nama ?? '-' }}</td>
                            <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}</td>
                            <td>{{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') : '-' }}</td>
                            <td>{{ $item->jam_pulang ? \Carbon\Carbon::parse($item->jam_pulang)->format('H:i') : '-' }}</td>
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
                                @elseif ($item->status_harian == 'IZIN')
                                    <span class="badge bg-info text-dark">Izin</span>
                                @elseif ($item->status_harian == 'ALPA')
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

            <div class="mt-3 d-flex justify-content-center">
                {{ $absensi->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
