@extends('layouts.app')

@section('title', 'Input Manual Absensi')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0 text-primary">📋 Input Manual Absensi</h3>
    </div>

    {{-- Alert sukses --}}
    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Berhasil!</strong> {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ================= FORM CARD ================= --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="fw-semibold mb-0 text-secondary">Tambah Data Absensi Manual</h6>
        </div>
        <div class="card-body px-4 pb-4">
            <form action="{{ route('absensi.manual') }}" method="POST" class="row g-3">
                @csrf

                {{-- Nama Siswa --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-muted">Nama Siswa</label>
                    <select name="id_siswa" class="form-select form-select-lg" required>
                        <option value="">-- Pilih Siswa --</option>
                        @foreach($siswa as $s)
                            <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->kelas->nama_kelas ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Kehadiran --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-muted">Status Kehadiran</label>
                    <select name="status_harian" class="form-select form-select-lg" required>
                        <option value="HADIR">Hadir</option>
                        <option value="SAKIT">Sakit</option>
                        <option value="ALPA">Alpa</option>
                    </select>
                </div>

                {{-- Catatan --}}
                <div class="col-12">
                    <label class="form-label fw-semibold text-muted">Catatan (Opsional)</label>
                    <textarea name="catatan" class="form-control form-control-lg" rows="2" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>

                {{-- Tombol Simpan --}}
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill">
                        💾 Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= TABLE CARD ================= --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-semibold mb-0 text-secondary">Data Absensi Hari Ini</h6>
            <small class="text-muted">Tanggal: {{ now()->format('d F Y') }}</small>
        </div>
        <div class="card-body px-4 pb-3">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Jam Masuk</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @forelse($absensi as $a)
                            <tr>
                                <td>{{ $a->siswa->nama ?? '-' }}</td>
                                <td>{{ $a->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '-' }}</td>
                                <td>
                                    <span class="badge rounded-pill
                                        @if($a->status_harian=='HADIR') bg-success
                                        @elseif($a->status_harian=='SAKIT') bg-warning text-dark
                                        @else bg-danger @endif">
                                        {{ $a->status_harian }}
                                    </span>
                                </td>
                                <td>{{ $a->catatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted py-4">Belum ada data absensi hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if(method_exists($absensi, 'links'))
                <div class="mt-3">{{ $absensi->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
