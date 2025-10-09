@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0 text-primary">🏫 Manajemen Data Kelas</h3>
        <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#tambahModal">
            ➕ Tambah Kelas
        </button>
    </div>

    {{-- Alert sukses --}}
    @if(session('ok'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('ok') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ================== TABEL KELAS ================== --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <h6 class="fw-semibold text-secondary mb-0">Daftar Kelas</h6>
        </div>
        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>No</th>
                            <th>Nama Kelas</th>
                            <th>Wali Kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        {{-- Contoh data dummy (nanti diganti dari backend) --}}
                        <tr>
                            <td>1</td>
                            <td>VII-A</td>
                            <td>Bu Rina</td>
                            <td>
                                <button class="btn btn-sm btn-warning rounded-pill px-3 me-1" data-bs-toggle="modal" data-bs-target="#editModal">
                                    ✏️ Edit
                                </button>
                                <button class="btn btn-sm btn-danger rounded-pill px-3">
                                    🗑️ Hapus
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>VIII-B</td>
                            <td>Pak Dedi</td>
                            <td>
                                <button class="btn btn-sm btn-warning rounded-pill px-3 me-1" data-bs-toggle="modal" data-bs-target="#editModal">
                                    ✏️ Edit
                                </button>
                                <button class="btn btn-sm btn-danger rounded-pill px-3">
                                    🗑️ Hapus
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination dummy --}}
            <nav class="mt-4">
                <ul class="pagination justify-content-center flex-wrap gap-1 mb-0">
                    <li class="page-item disabled"><span class="page-link rounded-pill px-3">«</span></li>
                    <li class="page-item active"><span class="page-link rounded-pill px-3">1</span></li>
                    <li class="page-item"><a class="page-link rounded-pill px-3" href="#">2</a></li>
                    <li class="page-item"><a class="page-link rounded-pill px-3" href="#">3</a></li>
                    <li class="page-item"><a class="page-link rounded-pill px-3" href="#">4</a></li>
                    <li class="page-item"><a class="page-link rounded-pill px-3" href="#">5</a></li>
                    <li class="page-item"><a class="page-link rounded-pill px-3" href="#">»</a></li>
                </ul>
            </nav>
        </div>
    </div>
</div>

{{-- ================== MODAL TAMBAH KELAS ================== --}}
<div class="modal fade" id="tambahModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title fw-bold text-primary">Tambah Kelas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nama Kelas</label>
                            <input type="text" class="form-control form-control-lg" placeholder="Contoh: IX-A">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Wali Kelas</label>
                            <input type="text" class="form-control form-control-lg" placeholder="Contoh: Pak Budi">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================== MODAL EDIT KELAS ================== --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-light border-0">
                <h5 class="modal-title fw-bold text-warning">Edit Data Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Nama Kelas</label>
                            <input type="text" class="form-control form-control-lg" value="VIII-B">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Wali Kelas</label>
                            <input type="text" class="form-control form-control-lg" value="Pak Dedi">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .pagination {
        margin-top: 1rem;
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
