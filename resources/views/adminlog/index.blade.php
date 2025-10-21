@extends('layouts.app')
@section('title', 'Log Aktivitas Admin')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary mb-0">🧾 Log Aktivitas Admin</h3>
        <a href="{{ route('admin.logs') }}" class="btn btn-outline-primary rounded-pill">
            <i class="bi bi-arrow-clockwise me-2"></i>Refresh
        </a>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.logs') }}">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Aksi (route/action)</label>
                    <input type="text" name="aksi" value="{{ $aksi }}" class="form-control" placeholder="misal: siswa.store">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Cari</label>
                    <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="route / user agent / method">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100 rounded-pill">Tampilkan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Log --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th>#</th>
                        <th>Admin</th>
                        <th>Aksi</th>
                        <th>Route</th>
                        <th>Method</th>
                        <th>IP</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @forelse ($logs as $i => $log)
                        <tr>
                            <td>{{ $logs->firstItem() + $i }}</td>
                            <td>{{ $log->admin->username ?? '-' }}</td>
                            <td><span class="badge bg-secondary">{{ $log->action }}</span></td>
                            <td class="text-start">{{ $log->route }}</td>
                            <td><span class="badge bg-info text-dark">{{ $log->method }}</span></td>
                            <td>{{ $log->ip }}</td>
                            <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted py-3">Belum ada data log.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
