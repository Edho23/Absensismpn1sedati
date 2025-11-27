@extends('layouts.app')

@section('title', 'Edit Data Absensi')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary mb-0">
            <i class="bi bi-clock-history me-2"></i> Edit Data Presensi
        </h3>
        <a href="{{ route('absensi.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FILTER BAR --}}
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('absensi.edit') }}" class="row g-2">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        @foreach (['HADIR','SAKIT','IZIN','ALPA'] as $st)
                            <option value="{{ $st }}" {{ $filter_status===$st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Kelas</label>
                    <select name="kelas" class="form-select">
                        <option value="">Semua</option>
                        @foreach($daftarKelas as $k)
                            <option value="{{ $k }}" {{ $kelas===$k ? 'selected':'' }}>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Paralel</label>
                    <select name="kelas_paralel" class="form-select">
                        <option value="">Semua</option>
                        @foreach($daftarParalel as $p)
                            <option value="{{ $p }}" {{ $kelasParalel==$p ? 'selected':'' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-semibold">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">-</option>
                        <option value="L" {{ $gender==='L' ? 'selected':'' }}>L</option>
                        <option value="P" {{ $gender==='P' ? 'selected':'' }}>P</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Cari NIS/Nama</label>
                    <input type="text" name="q" value="{{ $qterm }}" class="form-control" placeholder="mis. 2210 / Budi">
                </div>
                <div class="col-12 d-flex gap-2 mt-2">
                    <button class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('absensi.edit') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- BULK ACTION BAR --}}
    <form method="POST" action="{{ route('absensi.bulk') }}" id="bulkForm">
        @csrf
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="checkAll">
                        <label for="checkAll" class="form-check-label">Pilih Semua</label>
                    </div>
                    <select name="status" class="form-select form-select-sm w-auto">
                        <option value="SAKIT">Set Sakit (S)</option>
                        <option value="IZIN">Set Izin (I)</option>
                        <option value="ALPA">Set Alpa (A)</option>
                        <option value="HADIR">Set Hadir (H)</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                        <i class="bi bi-check2-all me-1"></i>Terapkan ke terpilih
                    </button>
                </div>
                <small class="text-muted">Tanggal: <strong>{{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }}</strong></small>
            </div>

            <div class="card-body px-4 pb-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th></th>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                                <th>Catatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($absensi as $a)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $a->id }}" class="row-check">
                                    </td>
                                    <td>{{ $a->siswa->nis ?? '-' }}</td>
                                    <td class="text-start">{{ $a->siswa->nama ?? '-' }}</td>
                                    <td>{{ ($a->siswa->kelas->nama_kelas ?? '-') . ' ' . ($a->siswa->kelas->kelas_paralel ?? '-') }}</td>
                                    <td>{{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '-' }}</td>
                                    <td>{{ $a->jam_pulang ? \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') : '-' }}</td>
                                    <td>
                                        <form action="{{ route('absensi.update', $a->id) }}" method="POST" class="d-flex align-items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <select name="status_harian" class="form-select form-select-sm rounded-pill">
                                                @foreach (['HADIR','SAKIT','IZIN','ALPA'] as $st)
                                                    <option value="{{ $st }}" {{ $a->status_harian===$st ? 'selected':'' }}>{{ $st }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-sm btn-outline-primary rounded-pill">Simpan</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form action="{{ route('absensi.update', $a->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="catatan" value="{{ $a->catatan }}" class="form-control form-control-sm">
                                        </form>
                                    </td>
                                    <td>
                                        <form action="{{ route('absensi.destroy', $a->id) }}" method="POST" onsubmit="return confirm('Hapus baris ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger rounded-pill">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-muted py-3">Belum ada data presensi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-center">
                    {{ $absensi->links() }}
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('checkAll')?.addEventListener('change', function(e){
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = e.target.checked);
});
</script>
@endpush
