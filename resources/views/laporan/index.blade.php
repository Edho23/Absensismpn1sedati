@extends('layouts.app')
@section('title','Laporan Kehadiran')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-primary mb-0">
                <i class="bi bi-file-earmark-text me-2"></i>Laporan Kehadiran
            </h3>
            <small class="text-muted">
                Kelola dan unduh laporan kehadiran berdasarkan rentang tanggal, kelas, dan filter lainnya.
            </small>
        </div>

        {{-- Tombol Export (buka modal opsi export) --}}
        <button class="btn btn-success rounded-pill d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#exportModal">
            <i class="bi bi-download"></i>
            <span>Unduh Laporan</span>
        </button>
    </div>

    {{-- =============== FILTER PANEL =============== --}}
    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body">
            <form id="filterForm" method="GET" action="{{ route('laporan.index') }}" class="row g-3" autocomplete="off">
                <input type="hidden" name="mode" value="{{ $mode ?? 'detail' }}">

                {{-- Baris 1: Tanggal & Mode --}}
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold small text-secondary">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ $tanggalMulai }}" class="form-control form-control-sm">
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-semibold small text-secondary">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ $tanggalSelesai }}" class="form-control form-control-sm">
                </div>

                {{-- Kelas --}}
                <div class="col-lg-2 col-md-4">
                    <label class="form-label fw-semibold small text-secondary">Kelas</label>
                    <select name="kelas" id="kelasSelect" class="form-select form-select-sm">
                        <option value="">Semua Kelas</option>
                        @foreach($daftarKelas ?? [] as $k)
                            <option value="{{ $k }}" {{ ($kelas ?? request('kelas'))==$k ? 'selected':'' }}>
                                {{ $k }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Paralel --}}
                <div class="col-lg-2 col-md-4">
                    <label class="form-label fw-semibold small text-secondary">Kelas Paralel</label>
                    <select name="kelas_paralel" id="paralelSelect" class="form-select form-select-sm">
                        <option value="">Semua Paralel</option>
                        @foreach($daftarParalel ?? [] as $p)
                            <option value="{{ $p }}" {{ ($paralel ?? request('kelas_paralel'))==$p ? 'selected':'' }}>
                                {{ $p }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status (detail mode saja) --}}
                @if(($mode ?? 'detail') === 'detail')
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label fw-semibold small text-secondary">Status Kehadiran</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach (['HADIR'=>'Hadir','SAKIT'=>'Sakit','IZIN'=>'Izin','ALPA'=>'Alpa'] as $v=>$t)
                                <option value="{{ $v }}" {{ (request('status')==$v)?'selected':'' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Gender --}}
                <div class="col-lg-2 col-md-4">
                    <label class="form-label fw-semibold small text-secondary">Gender</label>
                    <select name="gender" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        <option value="L" {{ request('gender')==='L' ? 'selected':'' }}>L</option>
                        <option value="P" {{ request('gender')==='P' ? 'selected':'' }}>P</option>
                    </select>
                </div>

                {{-- Tombol Filter & Reset --}}
                <div class="col-lg-3 col-md-8 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary w-100 btn-sm d-flex justify-content-center align-items-center gap-1">
                        <i class="bi bi-search"></i><span>Filter</span>
                    </button>
                    <a href="{{ route('laporan.index', ['mode'=>$mode ?? 'detail']) }}"
                       class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                    </a>
                </div>

                {{-- Toggle mode: Detail / Rekap --}}
                <div class="col-lg-4 col-md-12 d-flex gap-2 align-items-end flex-wrap mt-2">
                    <span class="small text-muted me-2">Tampilan:</span>
                    <div class="btn-group" role="group">
                        <a href="{{ route('laporan.index', array_merge(request()->query(), ['mode'=>'detail'])) }}"
                           class="btn btn-sm btn-outline-primary {{ ($mode ?? 'detail')==='detail' ? 'active' : '' }}">
                            <i class="bi bi-list-ul me-1"></i>Detail
                        </a>
                        <a href="{{ route('laporan.index', array_merge(request()->query(), ['mode'=>'rekap'])) }}"
                           class="btn btn-sm btn-outline-primary {{ ($mode ?? 'detail')==='rekap' ? 'active' : '' }}">
                            <i class="bi bi-grid-3x3-gap me-1"></i>Rekap Per Siswa
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- =============== TABEL LAPORAN =============== --}}
    @if(($mode ?? 'detail') === 'rekap')
        {{-- REKAP PER SISWA --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h6 class="fw-semibold text-secondary mb-0">
                        <i class="bi bi-grid-3x3-gap me-2"></i>
                        Rekap Kehadiran Per Siswa
                    </h6>
                    <small class="text-muted">
                        Menampilkan jumlah Hadir (H), Sakit (S), Izin (I), dan Alpa (A) pada rentang tanggal terpilih.
                    </small>
                </div>
                <span class="badge bg-primary-subtle text-primary">
                    Total: {{ $rekap->total() }} siswa
                </span>
            </div>
            <div class="card-body table-responsive px-4 pb-3">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>H</th>
                            <th>S</th>
                            <th>I</th>
                            <th>A</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($rekap as $i => $r)
                            <tr>
                                <td>{{ $rekap->firstItem() + $i }}</td>
                                <td>{{ $r->nis }}</td>
                                <td class="text-start">{{ $r->nama }}</td>
                                <td>{{ $r->kelas_label }}</td>
                                <td><span class="badge bg-success">{{ $r->hadir }}</span></td>
                                <td><span class="badge bg-warning text-dark">{{ $r->sakit }}</span></td>
                                <td><span class="badge bg-info text-dark">{{ $r->izin }}</span></td>
                                <td><span class="badge bg-danger">{{ $r->alpa }}</span></td>
                                <td class="fw-bold">{{ $r->total }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-muted py-3">Tidak ada data rekap untuk filter tersebut.</td>
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
        {{-- DETAIL ABSENSI --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h6 class="fw-semibold text-secondary mb-0">
                        <i class="bi bi-list-ul me-2"></i>
                        Detail Kehadiran Siswa
                    </h6>
                    <small class="text-muted">
                        Menampilkan log presensi per hari berdasarkan filter yang dipilih.
                    </small>
                </div>
                <span class="badge bg-primary-subtle text-primary">
                    Total: {{ $absensi->total() }} baris presensi
                </span>
            </div>
            <div class="card-body table-responsive px-4 pb-3">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Gender</th>
                            <th>Kelas</th>
                            <th>Paralel</th>
                            <th>Tanggal</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Sumber</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($absensi as $i => $a)
                            <tr>
                                <td>{{ $absensi->firstItem() + $i }}</td>
                                <td>{{ $a->siswa->nis ?? '-' }}</td>
                                <td class="text-start">{{ $a->siswa->nama ?? '-' }}</td>
                                <td>{{ $a->siswa->gender ?? '-' }}</td>
                                <td>{{ $a->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $a->siswa->kelas->kelas_paralel ?? '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '-' }}</td>
                                <td>{{ $a->jam_pulang ? \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') : '-' }}</td>
                                <td>
                                    <span class="badge {{ $a->sumber=='MANUAL'?'bg-primary':'bg-success' }}">
                                        {{ $a->sumber }}
                                    </span>
                                </td>
                                <td>
                                    @php $st=$a->status_harian; @endphp
                                    <span class="badge
                                        @switch($st)
                                            @case('HADIR') bg-success @break
                                            @case('SAKIT') bg-warning text-dark @break
                                            @case('IZIN')  bg-info text-dark @break
                                            @case('ALPA')  bg-danger @break
                                            @default bg-secondary
                                        @endswitch
                                    ">{{ $st }}</span>
                                </td>
                                <td class="text-start">{{ $a->catatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-muted py-3">Tidak ada data presensi untuk filter tersebut.</td>
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

{{-- =============== MODAL EXPORT =============== --}}
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content rounded-4 border-0 shadow" method="GET" action="{{ route('laporan.export') }}">
      <div class="modal-header">
        <h5 class="modal-title fw-semibold">
            <i class="bi bi-download me-2 text-success"></i>Opsi Unduh Laporan
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        {{-- bawa filter yang sama (kelas, paralel, gender) --}}
        @foreach (['kelas','kelas_paralel','gender'] as $carry)
            <input type="hidden" name="{{ $carry }}" value="{{ request($carry) }}">
        @endforeach

        <div class="mb-3">
            <label class="form-label fw-semibold small text-secondary">Jenis Laporan</label>
            <select name="jenis" id="jenisSelect" class="form-select form-select-sm" required>
                <option value="absen_bulan">Absen 1 Bulan</option>
                <option value="absen_bulan_rekap">Absen 1 Bulan + Rekap</option>
                <option value="semua_bulan_rekap">Semua Bulan (Jan–Des) + Rekap</option>
            </select>
        </div>

        <div id="bulanGroup" class="mb-2">
            <label class="form-label fw-semibold small text-secondary">Bulan</label>
            <select name="bulan" class="form-select form-select-sm">
                @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" {{ (int)date('n')===$m ? 'selected':'' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
        </div>

        <small class="text-muted d-block mt-1">
            File laporan akan dibuat dalam format spreadsheet yang siap dicetak atau diolah lebih lanjut.
        </small>
      </div>
      <div class="modal-footer border-0">
        <button class="btn btn-success d-flex align-items-center gap-2">
            <i class="bi bi-download"></i>
            <span>Unduh</span>
        </button>
      </div>
    </form>
  </div>
</div>

@push('styles')
<style>
    .form-label { font-size: 13px; }
    .form-select-sm, .form-control-sm { font-size: 13px; border-radius: 8px; }
    .table { font-size: 13px; }
    .table th, .table td {
        vertical-align: middle !important;
        padding-top: 8px !important;
        padding-bottom: 8px !important;
    }
</style>
@endpush

{{-- Auto-refresh paralel saat kelas berubah + toggle bulan --}}
@push('scripts')
<script>
document.getElementById('kelasSelect')?.addEventListener('change', function () {
    document.getElementById('filterForm').submit();
});

const jenisSelect = document.getElementById('jenisSelect');
const bulanGroup  = document.getElementById('bulanGroup');
function toggleBulan(){
    if (!jenisSelect || !bulanGroup) return;
    bulanGroup.style.display = (jenisSelect.value === 'semua_bulan_rekap') ? 'none' : 'block';
}
jenisSelect?.addEventListener('change', toggleBulan);
toggleBulan();
</script>
@endpush

@endsection
