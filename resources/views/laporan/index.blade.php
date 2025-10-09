@extends('layouts.app')

@section('title', 'Laporan Absensi')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0 text-primary">📄 Laporan Absensi Siswa</h3>
    </div>

    {{-- ================== FORM FILTER ================== --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tanggal</label>
                    <input type="date" class="form-control form-control-lg" name="tanggal">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kelas</label>
                    <select class="form-select form-select-lg" name="kelas">
                        <option value="">-- Semua Kelas --</option>
                        <option>VII-A</option>
                        <option>VIII-B</option>
                        <option>IX-C</option>
                    </select>
                </div>
                <div class="col-md-4 text-md-end text-center">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2">
                        🔍 Tampilkan
                    </button>
                    <button type="button" class="btn btn-success rounded-pill px-4 py-2 ms-2">
                        ⬇️ Unduh PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================== TABEL LAPORAN ================== --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="fw-semibold text-secondary mb-0">Hasil Laporan Absensi</h6>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        {{-- Data dummy sementara --}}
                        <tr>
                            <td>1</td>
                            <td>12345</td>
                            <td>Yogi Aditya</td>
                            <td>IX-A</td>
                            <td>2025-10-09</td>
                            <td><span class="badge bg-success rounded-pill px-3 py-2">Hadir</span></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>67890</td>
                            <td>Dimas Arif</td>
                            <td>VIII-B</td>
                            <td>2025-10-09</td>
                            <td><span class="badge bg-danger rounded-pill px-3 py-2">Alpa</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <nav class="mt-4">
                <ul class="pagination justify-content-center flex-wrap gap-1 mb-0">
                    <li class="page-item disabled"><span class="page-link rounded-pill px-3">«</span></li>
                    <li class="page-item active"><span class="page-link rounded-pill px-3">1</span></li>
                    <li class="page-item"><a class="page-link rounded-pill px-3" href="#">2</a></li>
                    <li class="page-item"><a class="page-link rounded-pill px-3" href="#">3</a></li>
                    <li class="page-item"><a class="page-link rounded-pill px-3" href="#">»</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .modal-content {
        border-radius: 15px !important;
    }

    .form-label {
        color: #444;
        font-size: 15px;
    }

    input.form-control-lg, select.form-select-lg {
        border-radius: 10px;
        padding: 10px 14px;
        border: 1px solid #ced4da;
        transition: all 0.2s ease-in-out;
    }

    input.form-control-lg:focus, select.form-select-lg:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.15rem rgba(13,110,253,.25);
    }

    .page-link {
        border: none !important;
        color: #0d6efd;
        font-weight: 500;
        transition: all 0.2s ease-in-out;
    }

    .page-link:hover {
        background-color: #e8f0fe;
        color: #0b5ed7;
    }

    .page-item.active .page-link {
        background-color: #0d6efd;
        color: white !important;
        font-weight: 600;
        box-shadow: 0 0 8px rgba(13, 110, 253, 0.3);
    }

    .pagination .page-link.rounded-pill {
        border-radius: 50rem !important;
    }
</style>
@endpush
