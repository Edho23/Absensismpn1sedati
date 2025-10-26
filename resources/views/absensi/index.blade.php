@extends('layouts.app')

@section('title', 'Input Manual Absensi')

@section('content')
<div class="container-fluid px-4 py-3">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary mb-0">📝 Input Manual Presensi</h3>
    </div>

    {{-- ALERT SUKSES --}}
    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ALERT ERROR --}}
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

    {{-- ================== FORM INPUT MANUAL ================== --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <h6 class="fw-bold text-secondary mb-3">Tambah Presensi Manual Hari Ini</h6>
            <form action="{{ route('absensi.manual') }}" method="POST" autocomplete="off">
                @csrf
                <div class="row g-3 align-items-center">
                    {{-- NIS dengan Typeahead --}}
                    <div class="col-md-5 position-relative">
                        <label class="form-label fw-semibold small text-secondary">NIS / Nama</label>
                        <input type="text"
                               name="nis"
                               id="field-nis"
                               class="form-control form-control-sm rounded-3"
                               placeholder="Ketik NIS atau Nama siswa..."
                               value="{{ old('nis') }}"
                               required>
                        <div id="nis-suggest" class="typeahead-list" style="display:none;"></div>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-secondary">Status Kehadiran</label>
                        <select name="status_harian" class="form-select form-select-sm rounded-3" required>
                            <option value="HADIR" {{ old('status_harian')==='HADIR' ? 'selected':'' }}>Hadir</option>
                            <option value="SAKIT" {{ old('status_harian')==='SAKIT' ? 'selected':'' }}>Sakit</option>
                            <option value="IZIN"  {{ old('status_harian')==='IZIN'  ? 'selected':'' }}>Izin</option>
                            <option value="ALPA"  {{ old('status_harian')==='ALPA'  ? 'selected':'' }}>Alpa</option>
                        </select>
                    </div>

                    {{-- Catatan --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-secondary">Catatan</label>
                        <input type="text"
                               name="catatan"
                               class="form-control form-control-sm rounded-3"
                               placeholder="(boleh kosong)"
                               value="{{ old('catatan') }}">
                    </div>

                    {{-- Submit --}}
                    <div class="col-md-1 text-md-end text-start mt-3 mt-md-0">
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ================== TABEL Presensi HARI INI ================== --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="fw-semibold text-secondary mb-0">
                Daftar Presensi Hari Ini ({{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }})
            </h6>
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
                        @forelse($absensi as $i => $a)
                            <tr>
                                <td>{{ $absensi->firstItem() + $i }}</td>
                                <td>{{ $a->siswa->nis ?? '-' }}</td>
                                <td>{{ $a->siswa->nama ?? '-' }}</td>
                                <td>{{ $a->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>
                                    <span class="badge
                                        @if($a->status_harian === 'HADIR') bg-success
                                        @elseif($a->status_harian === 'SAKIT') bg-warning text-dark
                                        @else bg-danger @endif
                                        rounded-pill px-3 py-2">
                                        {{ $a->status_harian ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '-' }}</td>
                                <td>{{ $a->catatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-muted py-3">Belum ada data presensi hari ini.</td>
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
    .form-label { font-size: 13px; color: #555; }
    .form-control-sm, .form-select-sm { font-size: 13px; border-radius: 8px; padding: 6px 10px; }
    .btn-sm { font-size: 13px; }
    .table { font-size: 13px; }
    .table th, .table td { vertical-align: middle !important; padding-top: 8px !important; padding-bottom: 8px !important; }
    .card { border-radius: 10px; }
    .btn-primary { background-color: #0d6efd; border: none; }
    .btn-primary:hover { background-color: #0b5ed7; }

    /* Typeahead dropdown */
    .typeahead-list{
        position:absolute; top:68px; left:0; right:0; z-index:30;
        background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;
        box-shadow:0 8px 24px rgba(0,0,0,.06);
    }
    .typeahead-item{
        padding:8px 12px; cursor:pointer; font-size:13px; display:flex; justify-content:space-between; gap:12px;
    }
    .typeahead-item:hover{ background:#f3f4f6 }
    .typeahead-nis{ font-weight:700 }
    .typeahead-nama{ color:#374151 }
    .typeahead-kelas{ color:#6b7280; font-size:12px }
</style>
@endpush

@push('scripts')
<script src="/js/absensi.js"></script>
@endpush
