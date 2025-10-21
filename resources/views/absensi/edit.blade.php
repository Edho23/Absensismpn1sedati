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

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Tanggal</th>
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
                                <form action="{{ route('absensi.update', $a->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <td>{{ $a->siswa->nama ?? '-' }}</td>
                                    <td>{{ $a->siswa->kelas->nama_kelas ?? '-' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d-m-Y') }}</td>
                                    <td>
                                        <input type="time" name="jam_masuk" value="{{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '' }}" class="form-control form-control-sm rounded-pill">
                                    </td>
                                    <td>
                                        <input type="time" name="jam_pulang" value="{{ $a->jam_pulang ? \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') : '' }}" class="form-control form-control-sm rounded-pill">
                                    </td>
                                    <td>
                                        <select name="status_harian" class="form-select form-select-sm rounded-pill">
                                            <option value="HADIR" {{ $a->status_harian == 'HADIR' ? 'selected' : '' }}>Hadir</option>
                                            <option value="SAKIT" {{ $a->status_harian == 'SAKIT' ? 'selected' : '' }}>Sakit</option>
                                            <option value="ALPA" {{ $a->status_harian == 'ALPA' ? 'selected' : '' }}>Alpa</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="catatan" value="{{ $a->catatan }}" class="form-control form-control-sm rounded-pill">
                                    </td>
                                    <td>
                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                            <i class="bi bi-save"></i> Simpan
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-muted py-3">Belum ada data Presensi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-center">{{ $absensi->links() }}</div>
        </div>
    </div>
</div>
@endsection
