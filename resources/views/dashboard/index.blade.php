@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- ======= Judul Halaman ======= --}}
    <h3 class="fw-bold text-primary mb-4">
        <i class="bi bi-speedometer2 me-2"></i>Dashboard Presensi
    </h3>

    {{-- ======= Statistik Kartu Utama ======= --}}
    <div class="row g-4 mb-4">

        {{-- Total Siswa --}}
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-4">
                    <i class="bi bi-people-fill text-primary fs-2 mb-2"></i>
                    <h5 class="fw-bold mb-1">{{ $cards['siswa'] ?? 0 }}</h5>
                    <small class="text-muted">Total Siswa</small>
                </div>
            </div>
        </div>

        {{-- Total Kelas --}}
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-4">
                    <i class="bi bi-journal-bookmark-fill text-success fs-2 mb-2"></i>
                    <h5 class="fw-bold mb-1">{{ $cards['kelas'] ?? 0 }}</h5>
                    <small class="text-muted">Total Kelas</small>
                </div>
            </div>
        </div>

        {{-- Total Kartu RFID --}}
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-4">
                    <i class="bi bi-credit-card-2-front-fill text-warning fs-2 mb-2"></i>
                    <h5 class="fw-bold mb-1">{{ $cards['kartu'] ?? 0 }}</h5>
                    <small class="text-muted">Total Kartu RFID</small>
                </div>
            </div>
        </div>

        {{-- Jumlah User (Admin) --}}
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-4">
                    <i class="bi bi-person-badge-fill text-info fs-2 mb-2"></i>
                    <h5 class="fw-bold mb-1">{{ $cards['user'] ?? 1 }}</h5>
                    <small class="text-muted">Jumlah Admin</small>
                </div>
            </div>
        </div>

        {{-- Belum Tapping Hari Ini (Tambahan Baru) --}}
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body text-center py-4">
                    <i class="bi bi-exclamation-circle-fill text-secondary fs-2 mb-2"></i>
                    <h5 class="fw-bold mb-1">{{ $belumTapping ?? 0 }}</h5>
                    <small class="text-muted">Belum Tapping Hari Ini</small>
                </div>
            </div>
        </div>
    </div>

    {{-- ======= Grafik Presensi Mingguan ======= --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 px-4 py-3">
            <h6 class="fw-semibold text-secondary mb-0">
                <i class="bi bi-bar-chart-line me-2"></i>Statistik Kehadiran Mingguan
            </h6>
        </div>
        <div class="card-body px-4 pb-4">
            <canvas id="chartPresensi" height="100"></canvas>
        </div>
    </div>

    {{-- ======= Log Aktivitas Hari Ini ======= --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 px-4 py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-semibold text-secondary mb-0">
                <i class="bi bi-clock-history me-2"></i>Log Aktivitas Hari Ini
            </h6>
            <span class="badge bg-primary-subtle text-primary">Terbaru</span>
        </div>

        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Jam Masuk</th>
                            <th>Status</th>
                            <th>Sumber</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $i => $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->siswa->nis ?? '-' }}</td>
                                <td class="text-start">{{ $item->siswa->nama ?? '-' }}</td>
                                <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') : '-' }}</td>
                                <td>
                                    @if($item->status_harian === 'HADIR')
                                        <span class="badge bg-success">Hadir</span>
                                    @elseif($item->status_harian === 'SAKIT')
                                        <span class="badge bg-warning text-dark">Sakit</span>
                                    @elseif($item->status_harian === 'ALPA')
                                        <span class="badge bg-danger">Alpa</span>
                                    @else
                                        <span class="badge bg-secondary">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $item->sumber === 'MANUAL' ? 'bg-primary' : 'bg-success' }}">
                                        {{ $item->sumber ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-muted py-3">Belum ada log aktivitas hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ======= Chart.js Script ======= --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('chartPresensi');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($labels ?? []) !!},
            datasets: [{
                label: 'Jumlah Kehadiran',
                data: {!! json_encode($series ?? []) !!},
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { precision:0 } }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>
@endpush
@endsection
