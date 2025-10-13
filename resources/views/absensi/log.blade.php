@extends('layouts.app')
@section('title', 'Log Absensi')

@section('content')
<div class="container-fluid">

  {{-- ===== TITLE ===== --}}
  <h3 class="page-title"><i class="bi bi-journal-text"></i> Log Absensi</h3>

  {{-- ===== FILTER CARD ===== --}}
  <div class="card-custom mb-4">
    <form action="{{ route('absensi.log') }}" method="GET" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label fw-semibold">Tanggal</label>
        <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Kelas</label>
        <select name="kelas" class="form-select">
          <option value="">-- Semua Kelas --</option>
          @foreach($daftarKelas as $k)
            <option value="{{ $k }}" {{ request('kelas')==$k ? 'selected' : '' }}>{{ $k }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Status Kehadiran</label>
        <select name="status" class="form-select">
          <option value="">-- Semua Status --</option>
          <option value="HADIR" {{ request('status')=='HADIR' ? 'selected':'' }}>Hadir</option>
          <option value="SAKIT" {{ request('status')=='SAKIT' ? 'selected':'' }}>Sakit</option>
          <option value="ALPA"  {{ request('status')=='ALPA'  ? 'selected':'' }}>Alpa</option>
        </select>
      </div>
      <div class="col-md-3 text-end">
        <button type="submit" class="btn btn-primary px-4 mt-3"><i class="bi bi-search"></i> Tampilkan</button>
      </div>
    </form>
  </div>

  {{-- ===== DATA TABLE ===== --}}
  <div class="card-custom">
    <h6 class="fw-semibold mb-3 text-secondary">Data Log Absensi</h6>

    <div class="table-responsive">
      <table class="table align-middle text-center">
        <thead class="table-light">
          <tr>
            <th>No</th>
            <th>NIS</th>
            <th>Nama Siswa</th>
            <th>Kelas</th>
            <th>Tanggal</th>
            <th>Jam Masuk</th>
            <th>Sumber</th>
            <th>Status</th>
            <th>Catatan</th>
          </tr>
        </thead>
        <tbody>
          @forelse($absensi as $i => $a)
            <tr>
              <td>{{ $absensi->firstItem() + $i }}</td>
              <td>{{ $a->nis }}</td>
              <td>{{ $a->siswa->nama ?? '-' }}</td>
              <td>{{ $a->siswa->kelas->nama_kelas ?? '-' }}</td>
              <td>{{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('d M Y') }}</td>
              <td>{{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '-' }}</td>
              <td><span class="badge bg-info text-dark">{{ $a->sumber }}</span></td>
              <td>
                @if($a->status_harian == 'HADIR')
                  <span class="badge bg-success">Hadir</span>
                @elseif($a->status_harian == 'SAKIT')
                  <span class="badge bg-warning text-dark">Sakit</span>
                @else
                  <span class="badge bg-danger">Alpa</span>
                @endif
              </td>
              <td>{{ $a->catatan ?? '-' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="text-muted py-3">Belum ada data absensi.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-3">
      {{ $absensi->links() }}
    </div>
  </div>
</div>
@endsection
