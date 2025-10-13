@extends('layouts.app')
@section('title','Dashboard')
@section('content')
  <h1 class="page-title">Dashboard</h1>
  <p class="page-sub">Selamat datang di Sistem Presensi SMPN 1 Sedati</p>

  <div class="cards">
    <div class="card"><div class="card-title">Jumlah Siswa</div><div class="card-value">{{ $cards['siswa'] }}</div></div>
    <div class="card"><div class="card-title">Jumlah Kelas</div><div class="card-value">{{ $cards['kelas'] }}</div></div>
    <div class="card"><div class="card-title">Jumlah Kartu</div><div class="card-value">{{ $cards['kartu'] }}</div></div>
    <div class="card"><div class="card-title">Jumlah User</div><div class="card-value">{{ $cards['user'] }}</div></div>
  </div>

  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">Grafik Kehadiran</div>
    </div>
    <div class="panel-body">
      <canvas id="chartAttendance" height="100"></canvas>
    </div>
  </div>

  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">Log Kehadiran Real-time</div>
      <div class="panel-sub">Pantau data masuk & pulang hari ini</div>
    </div>
    <div class="panel-body">
      <table class="table">
        <thead>
          <tr><th>Nama Siswa</th><th>Kelas</th><th>Jam Masuk</th><th>Jam Pulang</th><th>Status</th></tr>
        </thead>
        <tbody>
          @forelse($logs as $row)
            <tr>
              <td>{{ $row->siswa->nama }}</td>
              <td>{{ $row->siswa->kelas->nama_kelas ?? '-' }}</td>
              <td>{{ $row->jam_masuk?->format('H:i') ?? '-' }}</td>
              <td>{{ $row->jam_pulang?->format('H:i') ?? '-' }}</td>
              <td>{{ $row->status_harian ?? '-' }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="muted">Belum Ada Data Siswa</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection

@push('scripts')
<script src="/js/dashboard.js"></script>
<script>
  window.__chartData = {
    labels: @json($labels),
    series: @json($series),
  };
</script>
@endpush
