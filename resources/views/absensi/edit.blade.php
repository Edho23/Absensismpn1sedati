@extends('layouts.app')

@section('title', 'Edit Data Absensi')

@section('content')
<div class="container-fluid px-4 py-3">
    <h3 class="fw-bold text-primary mb-4">✏️ Edit Data Absensi</h3>

    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="fw-semibold text-secondary mb-0">Daftar Absensi</h6>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>Tanggal</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @forelse($absensi as $a)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d-m-Y') }}</td>
                                <td>{{ $a->siswa->nama ?? '-' }}</td>
                                <td>{{ $a->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>
                                    <form action="{{ route('absensi.update', $a->id) }}" method="POST" class="d-flex justify-content-center">
                                        @csrf
                                        <input type="time" name="jam_masuk" class="form-control form-control-sm" value="{{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '' }}" style="width:110px;">
                                </td>
                                <td>
                                        <input type="time" name="jam_pulang" class="form-control form-control-sm" value="{{ $a->jam_pulang ? \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') : '' }}" style="width:110px;">
                                </td>
                                <td>
                                        <select name="status_harian" class="form-select form-select-sm" style="width:110px;">
                                            <option value="HADIR" {{ $a->status_harian=='HADIR' ? 'selected' : '' }}>Hadir</option>
                                            <option value="SAKIT" {{ $a->status_harian=='SAKIT' ? 'selected' : '' }}>Sakit</option>
                                            <option value="ALPA" {{ $a->status_harian=='ALPA' ? 'selected' : '' }}>Alpa</option>
                                        </select>
                                </td>
                                <td>
                                        <input type="text" name="catatan" class="form-control form-control-sm" value="{{ $a->catatan }}">
                                </td>
                                <td class="d-flex justify-content-center gap-1">
                                        <button type="submit" class="btn btn-sm btn-success px-3">💾</button>
                                    </form>

                                    <form action="{{ route('absensi.destroy', $a->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?')" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger px-3">🗑️</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-muted py-4">Belum ada data absensi.</td></tr>
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
