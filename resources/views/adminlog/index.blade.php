@extends('layouts.app')

@section('title', 'Log Aktivitas Admin')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary mb-0">🧾 Log Aktivitas Admin</h3>
        <button class="btn btn-outline-primary rounded-pill">
            <i class="bi bi-arrow-clockwise me-2"></i>Refresh
        </button>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body">
            <form class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tanggal</label>
                    <input type="date" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Aksi</label>
                    <select class="form-select">
                        <option value="">Semua Aksi</option>
                        <option>CREATE_USER</option>
                        <option>UPDATE_USER</option>
                        <option>DELETE_USER</option>
                    </select>
                </div>
                <div class="col-md-4">
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
                        <th>Deskripsi</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    {{-- Dummy data frontend --}}
                    <tr>
                        <td>1</td>
                        <td>Admin Utama</td>
                        <td><span class="badge bg-success">CREATE_USER</span></td>
                        <td>Menambahkan akun siswa baru (NIS: 22045)</td>
                        <td>2025-10-13 09:42:18</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Admin Utama</td>
                        <td><span class="badge bg-warning text-dark">UPDATE_USER</span></td>
                        <td>Mengedit data siswa (NIS: 22045)</td>
                        <td>2025-10-13 09:51:22</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Admin Sekolah</td>
                        <td><span class="badge bg-danger">DELETE_USER</span></td>
                        <td>Menghapus data siswa (NIS: 21412)</td>
                        <td>2025-10-12 18:05:10</td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-3 text-center">
                <nav>
                    <ul class="pagination justify-content-center mb-0">
                        <li class="page-item disabled"><a class="page-link">«</a></li>
                        <li class="page-item active"><a class="page-link">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">»</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection
