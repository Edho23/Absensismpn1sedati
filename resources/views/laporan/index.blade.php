@extends('layouts.app')
@section('title','Laporan Kehadiran')

@section('content')
<div class="container-fluid px-4 py-3">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary mb-0">
            <i class="bi bi-file-earmark-text me-2"></i>Laporan Kehadiran
        </h3>

        {{-- Tombol Export (buka modal opsi export) --}}
        <button class="btn btn-success rounded-pill" data-bs-toggle="modal" data-bs-target="#exportModal">
            <i class="bi bi-download me-2"></i>Unduh Laporan
        </button>
    </div>

    {{-- ===== Filter ===== --}}
    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body">
            <form id="filterForm" method="GET" action="{{ route('laporan.index') }}" class="row g-3 align-items-end" autocomplete="off">
                <input type="hidden" name="mode" value="{{ $mode ?? 'detail' }}">

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ $tanggalMulai }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ $tanggalSelesai }}" class="form-control">
                </div>

                {{-- Kelas (VI/VII/VIII) --}}
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Kelas</label>
                    <select name="kelas" id="kelasSelect" class="form-select">
                        <option value="">Semua Kelas</option>
                        @foreach($daftarKelas ?? [] as $k)
                            <option value="{{ $k }}" {{ ($kelas ?? request('kelas'))==$k ? 'selected':'' }}>
                                {{ $k }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Paralel -> depend on kelas --}}
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Kelas Paralel</label>
                    <select name="kelas_paralel" id="paralelSelect" class="form-select">
                        <option value="">Semua Paralel</option>
                        @foreach($daftarParalel ?? [] as $p)
                            <option value="{{ $p }}" {{ ($paralel ?? request('kelas_paralel'))==$p ? 'selected':'' }}>
                                {{ $p }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status (hanya untuk detail) --}}
                @if(($mode ?? 'detail') === 'detail')
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua</option>
                            @foreach (['HADIR'=>'Hadir','SAKIT'=>'Sakit','IZIN'=>'Izin','ALPA'=>'Alpa'] as $v=>$t)
                                <option value="{{ $v }}" {{ (request('status')==$v)?'selected':'' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Gender --}}
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Semua</option>
                        <option value="L" {{ request('gender')==='L' ? 'selected':'' }}>L</option>
                        <option value="P" {{ request('gender')==='P' ? 'selected':'' }}>P</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('laporan.index', ['mode'=>$mode ?? 'detail']) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Reset
                    </a>
                </div>

                {{-- Toggle mode --}}
                <div class="col-md-4 d-flex gap-2">
                    <a href="{{ route('laporan.index', array_merge(request()->query(), ['mode'=>'detail'])) }}"
                       class="btn btn-outline-primary {{ ($mode ?? 'detail')==='detail' ? 'active' : '' }}">
                       Detail
                    </a>
                    <a href="{{ route('laporan.index', array_merge(request()->query(), ['mode'=>'rekap'])) }}"
                       class="btn btn-outline-primary {{ ($mode ?? 'detail')==='rekap' ? 'active' : '' }}">
                       Rekap Per Siswa
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    @if(($mode ?? 'detail') === 'rekap')
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>No</th><th>NIS</th><th>Nama</th><th>Kelas</th>
                            <th>H</th><th>S</th><th>I</th><th>A</th><th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
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
                            <tr><td colspan="9" class="text-muted py-3">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3 d-flex justify-content-center">
                    {{ $rekap->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>No</th><th>NIS</th><th>Nama</th><th>Gender</th>
                            <th>Kelas</th><th>Paralel</th><th>Tanggal</th><th>Jam Masuk</th>
                            <th>Jam Pulang</th><th>Sumber</th><th>Status</th><th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
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
                                    <span class="badge {{ $a->sumber=='MANUAL'?'bg-primary':'bg-success' }}">{{ $a->sumber }}</span>
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
                                <td>{{ $a->catatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="12" class="text-muted py-3">Tidak ada data.</td></tr>
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

{{-- ===== Modal Export ===== --}}
<div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="GET" action="{{ route('laporan.export') }}">
      <div class="modal-header">
        <h5 class="modal-title">Opsi Unduh Laporan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        {{-- bawa filter yang sama --}}
        @foreach (['kelas','kelas_paralel','gender'] as $carry)
            <input type="hidden" name="{{ $carry }}" value="{{ request($carry) }}">
        @endforeach

        <div class="mb-3">
            <label class="form-label">Jenis Laporan</label>
            <select name="jenis" id="jenisSelect" class="form-select" required>
                <option value="absen_bulan">Absen 1 Bulan</option>
                <option value="absen_bulan_rekap">Absen 1 Bulan + Rekap</option>
                <option value="semua_bulan_rekap">Semua Bulan (Jan–Des) + Rekap</option>
            </select>
        </div>

        <div id="bulanGroup" class="mb-2">
            <label class="form-label">Bulan</label>
            <select name="bulan" class="form-select">
                @for($m=1;$m<=12;$m++)
                    <option value="{{ $m }}" {{ (int)date('n')===$m ? 'selected':'' }}>
                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-success"><i class="bi bi-download me-2"></i>Unduh</button>
      </div>
    </form>
  </div>
</div>

{{-- Auto-refresh paralel saat kelas berubah --}}
@push('scripts')
<script>
document.getElementById('kelasSelect')?.addEventListener('change', function () {
    document.getElementById('filterForm').submit(); // auto submit (YA)
});

const jenisSelect = document.getElementById('jenisSelect');
const bulanGroup  = document.getElementById('bulanGroup');
function toggleBulan(){
    bulanGroup.style.display = (jenisSelect.value === 'semua_bulan_rekap') ? 'none' : 'block';
}
jenisSelect?.addEventListener('change', toggleBulan);
toggleBulan();
</script>
@endpush

@endsection
