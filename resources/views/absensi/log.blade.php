@extends('layouts.app')

@section('title', 'Log Absensi')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0 text-primary">📜 Log Absensi</h3>
    </div>

    {{-- Filter Form --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('absensi.log') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted">Kelas</label>
                    <select name="kelas" class="form-select">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($daftarKelas as $k)
                            <option value="{{ $k }}" {{ $kelas == $k ? 'selected' : '' }}>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-muted">Status</label>
                    <select name="status" class="form-select">
                        <option value="">-- Semua Status --</option>
                        <option value="HADIR" {{ $status=='HADIR' ? 'selected' : '' }}>Hadir</option>
                        <option value="SAKIT" {{ $status=='SAKIT' ? 'selected' : '' }}>Sakit</option>
                        <option value="ALPA" {{ $status=='ALPA' ? 'selected' : '' }}>Alpa</option>
                    </select>
                </div>

                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-primary px-4 rounded-pill">
                        🔍 Tampilkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Log Absensi --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-semibold text-secondary mb-0">Data Riwayat Absensi</h6>
            <small class="text-muted">{{ $absensi->total() }} total data</small>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Sumber</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @forelse($absensi as $a)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ $a->siswa->nama ?? '-' }}</td>
                                <td>{{ $a->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '-' }}</td>
                                <td>{{ $a->jam_pulang ? \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') : '-' }}</td>
                                <td>
                                    <span class="badge rounded-pill
                                        @if($a->status_harian=='HADIR') bg-success
                                        @elseif($a->status_harian=='SAKIT') bg-warning text-dark
                                        @else bg-danger @endif">
                                        {{ $a->status_harian }}
                                    </span>
                                </td>
                                <td>{{ $a->catatan ?? '-' }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $a->sumber ?? 'RFID' }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-muted py-4">
                                    Belum ada data log absensi ditemukan.
                                </td>
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

