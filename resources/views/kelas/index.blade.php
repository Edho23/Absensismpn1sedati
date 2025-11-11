@extends('layouts.app')
@section('title', 'Manajemen Data Kelas')

@section('content')
<div class="container-fluid px-4 py-3">

  {{-- HEADER --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-primary mb-0">🏫 Manajemen Data Kelas</h3>
  </div>

  {{-- ALERT --}}
  @if(session('ok'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      {{ session('ok') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <ul class="mb-0">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  {{-- ================== FILTER KELAS ================== --}}
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label small fw-semibold text-secondary">Grade</label>
          <select name="grade" class="form-select">
            <option value="">— Semua —</option>
            @foreach($daftarGrade as $g)
              <option value="{{ $g }}" {{ (string)($grade ?? '')===(string)$g ? 'selected':'' }}>{{ $g }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small fw-semibold text-secondary">Kelas Paralel</label>
          <select name="kelas_paralel" class="form-select">
            <option value="">— Semua —</option>
            @foreach($daftarParalel as $p)
              <option value="{{ $p }}" {{ (string)($paralel ?? '')===(string)$p ? 'selected':'' }}>{{ $p }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button class="btn btn-primary"><i class="bi bi-search me-1"></i>Filter</button>
          <a href="{{ route('kelas.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-clockwise me-1"></i>Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- ================== FORM TAMBAH KELAS ================== --}}
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body pb-2">
      <h6 class="fw-semibold text-secondary mb-3">Tambah Kelas Baru</h6>

      <form action="{{ route('kelas.store') }}" method="POST" class="row g-3 align-items-end">
        @csrf
        <div class="col-md-4">
          <label class="form-label small fw-semibold text-secondary">Nama Kelas</label>
          <div class="input-group shadow-sm rounded-pill">
            <span class="input-group-text bg-white border-0 ps-3">
              <i class="bi bi-building text-primary"></i>
            </span>
            <input type="text" name="nama_kelas" class="form-control border-0 rounded-end-pill"
                   placeholder="Contoh: IX-A" required>
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label small fw-semibold text-secondary">Wali Kelas</label>
          <div class="input-group shadow-sm rounded-pill">
            <span class="input-group-text bg-white border-0 ps-3">
              <i class="bi bi-person-badge text-success"></i>
            </span>
            <input type="text" name="wali_kelas" class="form-control border-0 rounded-end-pill"
                   placeholder="Contoh: Pak Budi" required>
          </div>
        </div>

        <div class="col-md-2">
          <label class="form-label small fw-semibold text-secondary">Grade</label>
          <select name="grade" class="form-select" required>
            <option value="">— Pilih —</option>
            <option value="7">7</option>
            <option value="8">8</option>
            <option value="9">9</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label small fw-semibold text-secondary">Paralel</label>
          <select name="kelas_paralel" class="form-select" required>
            <option value="">— Pilih —</option>
            @for($i=1;$i<=11;$i++)
              <option value="{{ $i }}">{{ $i }}</option>
            @endfor
          </select>
        </div>

        <div class="col-12 text-end">
          <button type="submit" class="btn btn-primary rounded-pill px-4 mt-1">
            <i class="bi bi-plus-circle"></i> Tambah
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ================== TABEL KELAS ================== --}}
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 py-3 px-4">
      <h6 class="fw-semibold text-secondary mb-0">Daftar Kelas</h6>
    </div>

    <div class="card-body px-4 pb-3">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light text-center small">
            <tr>
              <th>No</th>
              <th>Nama Kelas</th>
              <th>Wali Kelas</th>
              <th>Grade</th>
              <th>Paralel</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody class="text-center small">
          @forelse($kelas as $k)
            <tr>
              <td>{{ $kelas->firstItem() + $loop->index }}</td>
              <td><span class="badge bg-info text-white rounded-pill px-3 py-2">{{ $k->nama_kelas }}</span></td>
              <td class="text-start">{{ $k->wali_kelas }}</td>
              <td><span class="badge bg-primary-subtle text-primary">{{ $k->grade }}</span></td>
              <td><span class="badge bg-secondary-subtle text-dark">{{ $k->kelas_paralel }}</span></td>
              <td>
                <div class="d-flex justify-content-center gap-2">
                  <a href="{{ route('kelas.edit', $k->id) }}" class="btn btn-sm btn-warning rounded-pill px-3">
                    <i class="bi bi-pencil-square"></i> Edit
                  </a>

                  {{-- tombol hapus dengan modal --}}
                  <form action="{{ route('kelas.destroy', $k->id) }}" method="POST" class="d-inline form-hapus-kelas">
                    @csrf @method('DELETE')
                    <button type="button"
                            class="btn btn-sm btn-outline-danger rounded-pill px-3 btn-confirm-delete-kelas"
                            data-nama="{{ $k->nama_kelas }}">
                      <i class="bi bi-trash"></i> Hapus
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-muted py-3">Belum ada data kelas.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>

      {{-- PAGINATION --}}
      <div class="mt-3 d-flex justify-content-center">
        {{ $kelas->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>

{{-- ========== MODAL KONFIRMASI HAPUS ========== --}}
<div class="modal fade" id="modalConfirmDeleteKelas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header">
        <h5 class="modal-title fw-bold text-danger">Konfirmasi Hapus Kelas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Apakah Anda yakin ingin <strong>menghapus</strong> kelas <strong id="deleteNamaKelas"></strong>?
        <div class="mt-2 small text-muted">
          Tindakan ini permanen. Pastikan kelas ini tidak lagi terpakai pada data siswa.
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="btnDeleteKelasGo" class="btn btn-danger">Ya, Hapus</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // ====== Modal Konfirmasi Hapus Kelas ======
  const modalDeleteKelas = new bootstrap.Modal(document.getElementById('modalConfirmDeleteKelas'));
  const spanDeleteNamaKelas = document.getElementById('deleteNamaKelas');
  let formDeleteKelas = null;

  document.querySelectorAll('.btn-confirm-delete-kelas').forEach(btn => {
    btn.addEventListener('click', function () {
      spanDeleteNamaKelas.textContent = this.dataset.nama || '';
      formDeleteKelas = this.closest('form.form-hapus-kelas');
      modalDeleteKelas.show();
    });
  });

  document.getElementById('btnDeleteKelasGo').addEventListener('click', function () {
    if (formDeleteKelas) formDeleteKelas.submit();
  });
</script>
@endpush

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
  .card { border-radius: 14px; }
  .table-hover tbody tr:hover { background-color: #f8f9fa; }
  .input-group .form-control { box-shadow: none !important; }
  .btn-primary { border: none; }
</style>
@endpush
