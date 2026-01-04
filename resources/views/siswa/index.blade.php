@extends('layouts.app')
@section('title','Data Siswa')

@section('content')
<div class="container-fluid px-4 py-3 siswa-page">

  {{-- ========== HEADER ========== --}}
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
      <h3 class="fw-bold text-primary mb-0">
        <i class="bi bi-people-fill me-2"></i>Data Siswa
      </h3>
      <small class="text-muted">
        Kelola data siswa, filter berdasarkan kelas, angkatan, dan status keaktifan.
      </small>
    </div>

    <div class="d-flex gap-2 flex-wrap">
      {{-- Import CSV --}}
      <button type="button" class="btn btn-outline-primary rounded-pill btn-sm d-flex align-items-center gap-1"
              data-bs-toggle="modal" data-bs-target="#importCsvModal">
        <i class="bi bi-upload"></i>
        <span>Import CSV</span>
      </button>

      {{-- Tombol: Naikkan Kelas Otomatis --}}
      <button type="button" class="btn btn-warning rounded-pill btn-sm d-flex align-items-center gap-1"
              data-bs-toggle="modal" data-bs-target="#promoteModal">
        <i class="bi bi-arrow-up-right-circle"></i>
        <span>Naikkan Kelas Otomatis</span>
      </button>
    </div>
  </div>

  {{-- ===================== FORM TAMBAH SISWA ===================== --}}
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div>
          <h6 class="fw-bold text-secondary mb-1">
            <i class="bi bi-person-plus me-2"></i>Tambah Siswa Baru
          </h6>
          <small class="text-muted">
            Lengkapi NIS, nama, angkatan, kelas, gender, dan status sebelum menyimpan.
          </small>
        </div>
      </div>

      <form action="{{ route('siswa.store') }}" method="POST" class="row g-3 align-items-end" id="form-add-siswa">
        @csrf

        {{-- Baris 1 --}}
        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">NIS</label>
          <input type="text" name="nis" class="form-control form-control-sm" placeholder="NIS" required>
        </div>

        <div class="col-lg-4">
          <label class="form-label fw-semibold small text-secondary">Nama</label>
          <input type="text" name="nama" class="form-control form-control-sm" placeholder="Nama lengkap" required>
        </div>

        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">Angkatan</label>
          <input type="number" name="angkatan" class="form-control form-control-sm" min="2000" max="2100" placeholder="Tahun">
        </div>

        {{-- Baris 2: Kelas hanya VII/VIII/IX + Paralel --}}
        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">Kelas</label>
          <select class="form-select form-select-sm" id="add-grade" required>
            <option value="">— Pilih Kelas —</option>
            @foreach($grades as $g)
              <option value="{{ $g }}">{{ $g }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">Kelas Paralel</label>
          <select class="form-select form-select-sm" id="add-paralel" required>
            <option value="">— Pilih Paralel —</option>
          </select>
        </div>

        {{-- yang benar dikirim ke backend --}}
        <input type="hidden" name="kelas_id" id="add-kelas-id" value="">

        <div class="col-lg-1">
          <label class="form-label fw-semibold small text-secondary">Gender</label>
          <select name="gender" class="form-select form-select-sm">
            <option value="">—</option>
            <option value="L">L</option>
            <option value="P">P</option>
          </select>
        </div>

        <div class="col-lg-1">
          <label class="form-label fw-semibold small text-secondary">Status</label>
          <select name="status" class="form-select form-select-sm">
            <option value="A">A</option>
            <option value="N">N</option>
          </select>
        </div>

        <div class="col-12 text-end">
          <button class="btn btn-primary btn-sm rounded-pill px-4" type="submit">
            <i class="bi bi-save me-1"></i>Simpan
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ===================== FILTER & PENCARIAN ===================== --}}
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div>
          <h6 class="fw-bold text-secondary mb-1">
            <i class="bi bi-funnel me-2"></i>Filter & Pencarian
          </h6>
          <small class="text-muted">
            Cari siswa berdasarkan NIS/nama, kelas (VII/VIII/IX), paralel, gender, angkatan, dan status.
          </small>
        </div>
      </div>

      <form method="GET" action="{{ route('siswa.index') }}" class="row g-3 align-items-end" id="filter-form" autocomplete="off">
        <div class="col-lg-4 position-relative">
          <label class="form-label fw-semibold small text-secondary">Cari NIS/Nama</label>
          <input type="text"
                class="form-control form-control-sm"
                name="q"
                id="q"
                value="{{ $filters['q'] ?? '' }}"
                placeholder="Ketik NIS atau Nama...">
          <div id="q-suggest" class="typeahead-list" style="display:none;"></div>
        </div>

        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">Kelas</label>
          <select name="nama_kelas" class="form-select form-select-sm" id="filter-grade">
            <option value="">— Semua —</option>
            @foreach($grades as $g)
              <option value="{{ $g }}" {{ ($filters['nama_kelas'] ?? '') == $g ? 'selected' : '' }}>
                {{ $g }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">Kelas Paralel</label>
          <select name="kelas_paralel" class="form-select form-select-sm" id="filter-paralel">
            <option value="">— Semua —</option>
          </select>
        </div>

        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">Gender</label>
          <select name="gender" class="form-select form-select-sm">
            <option value="">— Semua —</option>
            <option value="L" {{ ($filters['gender'] ?? '')==='L' ? 'selected' : '' }}>L</option>
            <option value="P" {{ ($filters['gender'] ?? '')==='P' ? 'selected' : '' }}>P</option>
          </select>
        </div>

        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">Angkatan</label>
          <input type="number"
                name="angkatan"
                class="form-control form-control-sm"
                min="2000" max="2100"
                value="{{ $filters['angkatan'] ?? '' }}"
                placeholder="Tahun">
        </div>

        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">Status</label>
          <select name="status" class="form-select form-select-sm">
            <option value="">— Semua —</option>
            <option value="A" {{ ($filters['status'] ?? '')==='A' ? 'selected' : '' }}>A (Aktif)</option>
            <option value="N" {{ ($filters['status'] ?? '')==='N' ? 'selected' : '' }}>N (Nonaktif)</option>
          </select>
        </div>

        <div class="col-12 d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-search"></i><span>Filter</span>
          </button>
          <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-clockwise me-1"></i>Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- ===================== TABEL SISWA + INLINE EDIT + BULK DELETE ===================== --}}
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-0 px-4 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
      <div>
        <h6 class="fw-semibold text-secondary mb-0">
          <i class="bi bi-list-ul me-2"></i>Daftar Siswa
        </h6>
        <small class="text-muted">
          Menampilkan data siswa sesuai filter yang dipilih.
        </small>
      </div>
      <span class="badge bg-primary-subtle text-primary">
        Total: {{ $siswa->total() }} siswa
      </span>
    </div>

    <div class="card-body table-responsive px-4 pb-3">

      {{-- FORM BULK DELETE (tidak membungkus table/edit form, biar tidak nested) --}}
      <form id="bulkDeleteForm" action="{{ route('siswa.bulk-destroy') }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
        <div id="bulkHiddenIds"></div>
      </form>

      <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div class="small text-muted">
          Menampilkan {{ $siswa->firstItem() }}–{{ $siswa->lastItem() }} dari {{ $siswa->total() }} siswa
        </div>
        <button type="button" id="btnBulkDelete" class="btn btn-sm btn-outline-danger rounded-pill d-flex align-items-center gap-1">
          <i class="bi bi-trash"></i>
          <span>Hapus Terpilih</span>
        </button>
      </div>

      <table class="table table-hover align-middle text-center mb-0">
        <thead class="table-light small">
          <tr>
            <th><input type="checkbox" id="check-all"></th>
            <th>No</th>
            <th>NIS</th>
            <th>Nama</th>
            <th>Gender</th>
            <th>Kelas</th>
            <th>Angkatan</th>
            <th>Status</th>
            <th style="width:110px">Aksi</th>
          </tr>
        </thead>
        <tbody class="small">
        @forelse($siswa as $i => $s)
          <tr>
            <td><input type="checkbox" class="check-item" value="{{ $s->id }}"></td>
            <td>{{ $siswa->firstItem() + $i }}</td>
            <td>{{ $s->nis }}</td>
            <td class="text-start">{{ $s->nama }}</td>
            <td>{{ $s->gender ?: '-' }}</td>
            <td>{{ $s->kelas->nama_kelas ?? '-' }} - {{ $s->kelas->kelas_paralel ?? '-' }}</td>
            <td>{{ $s->angkatan ?? '-' }}</td>
            <td>
              <span class="badge {{ ($s->status ?? 'A') === 'A' ? 'bg-success' : 'bg-secondary' }}">
                {{ ($s->status ?? 'A') === 'A' ? 'A (Aktif)' : 'N (Nonaktif)' }}
              </span>
            </td>
            <td class="d-flex gap-2 justify-content-center">
              <button class="btn btn-sm btn-outline-primary" type="button"
                      data-bs-toggle="collapse" data-bs-target="#edit-{{ $s->id }}">
                <i class="bi bi-pencil-square"></i>
              </button>
            </td>
          </tr>

          @php
            $curGrade   = $s->kelas->nama_kelas ?? '';
            $curParalel = (string)($s->kelas->kelas_paralel ?? '');
          @endphp

          <tr class="collapse" id="edit-{{ $s->id }}">
            <td colspan="9" class="bg-light-subtle">
              <form action="{{ route('siswa.update', $s->id) }}" method="POST"
                    class="row g-2 align-items-end form-edit"
                    data-nama="{{ $s->nama }}"
                    data-initial-grade="{{ $curGrade }}"
                    data-initial-paralel="{{ $curParalel }}"
                    data-row="{{ $s->id }}">
                @csrf
                @method('PUT')

                <div class="col-md-2">
                  <label class="form-label small text-secondary">NIS</label>
                  <input type="text" name="nis" class="form-control form-control-sm" value="{{ $s->nis }}" required>
                </div>

                <div class="col-md-4">
                  <label class="form-label small text-secondary">Nama</label>
                  <input type="text" name="nama" class="form-control form-control-sm" value="{{ $s->nama }}" required>
                </div>

                <div class="col-md-2">
                  <label class="form-label small text-secondary">Angkatan</label>
                  <input type="number" name="angkatan" class="form-control form-control-sm" min="2000" max="2100" value="{{ $s->angkatan }}">
                </div>

                <div class="col-md-2">
                  <label class="form-label small text-secondary d-block">Kelas</label>
                  <select class="form-select form-select-sm grade-edit" data-row="{{ $s->id }}" required>
                    <option value="">— Pilih Kelas —</option>
                    @foreach($grades as $g)
                      <option value="{{ $g }}" {{ $curGrade === $g ? 'selected' : '' }}>{{ $g }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-2">
                  <label class="form-label small text-secondary d-block">Paralel</label>
                  <select class="form-select form-select-sm paralel-edit" data-row="{{ $s->id }}" required>
                    <option value="">— Pilih Paralel —</option>
                  </select>
                </div>

                <input type="hidden" name="kelas_id" id="kelas-id-{{ $s->id }}" value="{{ $s->kelas_id }}">

                <div class="col-md-1">
                  <label class="form-label small text-secondary">Gender</label>
                  <select name="gender" class="form-select form-select-sm">
                    <option value="">—</option>
                    <option value="L" {{ ($s->gender ?? '')==='L' ? 'selected' : '' }}>L</option>
                    <option value="P" {{ ($s->gender ?? '')==='P' ? 'selected' : '' }}>P</option>
                  </select>
                </div>

                <div class="col-md-1">
                  <label class="form-label small text-secondary">Status</label>
                  <select name="status" class="form-select form-select-sm">
                    <option value="A" {{ ($s->status ?? 'A')==='A' ? 'selected' : '' }}>A</option>
                    <option value="N" {{ ($s->status ?? 'N')==='N' ? 'selected' : '' }}>N</option>
                  </select>
                </div>

                <div class="col-md-1 d-grid">
                  <button type="button" class="btn btn-sm btn-success btn-confirm-edit">
                    <i class="bi bi-save"></i>
                  </button>
                </div>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-muted py-3">Belum ada data siswa.</td>
          </tr>
        @endforelse
        </tbody>
      </table>

      <div class="mt-3 d-flex justify-content-center">
        {{ $siswa->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>

{{-- ====== MODAL IMPORT CSV ====== --}}
<div class="modal fade" id="importCsvModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-upload me-2"></i>Import Siswa dari CSV
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form action="{{ route('siswa.import-csv') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="small text-muted mb-2">
            Format kolom CSV: <code>nis,nama,angkatan,gender,status,nama_kelas,kelas_paralel</code><br>
            Sistem akan mencari <b>kelas_id</b> otomatis dari tabel <b>kelas</b> berdasarkan <b>nama_kelas + kelas_paralel</b>.
          </div>

          <div class="mb-2">
            <a href="{{ route('siswa.template-csv') }}" class="small">Unduh template CSV</a>
          </div>

          <div class="mb-3">
            <label class="form-label small text-secondary fw-semibold">File CSV</label>
            <input type="file" name="file" class="form-control form-control-sm" accept=".csv,text/csv" required>
          </div>

          <div class="mb-3">
            <label class="form-label small text-secondary fw-semibold">Mode Import</label>
            <select name="mode" class="form-select form-select-sm">
              <option value="upsert" selected>Update jika NIS sudah ada (Upsert)</option>
              <option value="insert_only">Hanya insert (kalau NIS sudah ada → skip)</option>
            </select>
          </div>

          <div class="alert alert-warning py-2 mb-0 small">
            Pastikan data kelas (VII/VIII/IX + paralel) sudah lengkap di tabel <b>kelas</b>, kalau tidak baris CSV akan ditolak.
          </div>
        </div>

        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-cloud-arrow-up me-1"></i>Import
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ====== MODAL KONFIRMASI PROMOSI ====== --}}
<div class="modal fade" id="promoteModal" tabindex="-1" aria-labelledby="promoteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4">
      <div class="modal-header">
        <h5 class="modal-title" id="promoteModalLabel">
          <i class="bi bi-exclamation-triangle text-warning me-2"></i>Konfirmasi Promosi
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Tindakan ini akan:
        <ul class="mb-0">
          <li>Naikkan kelas semua siswa berstatus <span class="badge bg-success">A</span> (Aktif) ke grade berikutnya</li>
          <li>Meluluskan siswa grade 9 menjadi <span class="badge bg-secondary">N</span> (Nonaktif)</li>
        </ul>
        Lanjutkan?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <form id="promoteForm" action="{{ route('siswa.promote') }}" method="POST" class="d-inline">
          @csrf
          <button type="submit" class="btn btn-warning btn-sm">
            <i class="bi bi-check2-circle me-1"></i>Ya, Jalankan
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- ====== MODAL KONFIRMASI EDIT (SAVE) ====== --}}
<div class="modal fade" id="modalConfirmEdit" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">Konfirmasi Simpan Perubahan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Simpan perubahan untuk siswa <strong id="editNama"></strong>?
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="btnEditGo" class="btn btn-primary btn-sm">Ya, Simpan</button>
      </div>
    </div>
  </div>
</div>

{{-- ====== MODAL KONFIRMASI HAPUS (BULK) ====== --}}
<div class="modal fade" id="modalConfirmDelete" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header">
        <h5 class="modal-title fw-bold text-danger">
          <i class="bi bi-exclamation-triangle me-2"></i>Konfirmasi Hapus
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Anda akan <strong>menghapus</strong> <strong id="deleteCount">0</strong> data siswa terpilih
        dan tindakan ini tidak dapat dibatalkan. Lanjutkan?
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="btnDeleteGo" class="btn btn-danger btn-sm">Ya, Hapus</button>
      </div>
    </div>
  </div>
</div>

<div class="toast-container position-fixed top-0 end-0 p-3" id="toastArea" style="z-index: 1080;"></div>

@push('styles')
<style>
  .typeahead-list{
    position:absolute; top:64px; left:0; right:0; z-index:30;
    background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;
    box-shadow:0 8px 24px rgba(0,0,0,.06);
  }
  .typeahead-item{ padding:10px 14px; cursor:pointer; font-size:14px; display:flex; justify-content:space-between; gap:12px; }
  .typeahead-item:hover{ background:#f3f4f6 }
  .typeahead-nis{ font-weight:700 }
  .typeahead-nama{ color:#374151 }
  .typeahead-kelas{ color:#6b7280; font-size:12.5px }

  .siswa-page { font-size: 15px; }
  .siswa-page .card .card-body,
  .siswa-page .card .card-header { font-size: 15px; }

  .siswa-page .form-control-sm,
  .siswa-page .form-select-sm {
    font-size: 0.95rem;
    padding-top: .5rem; padding-bottom: .5rem;
    border-radius: 10px;
  }

  .siswa-page .btn-sm { font-size: 0.95rem; }
  .siswa-page .table { font-size: 14.5px; }
  .siswa-page .table th, .siswa-page .table td {
    vertical-align: middle !important;
    padding-top: 10px !important;
    padding-bottom: 10px !important;
  }
  .siswa-page .badge { font-size: 0.8rem; }
  .siswa-page .pagination .page-link { font-size: 0.95rem; border-radius: 8px; }
</style>
@endpush

@push('scripts')
<script>
/** ========= Toast helper ========= */
const toastArea = document.getElementById('toastArea');
function makeToast(title, message, type){
  const bg = {success:'bg-success text-white',danger:'bg-danger text-white',warning:'bg-warning',info:'bg-info'}[type] || 'bg-dark text-white';
  const el = document.createElement('div');
  el.className = 'toast align-items-center';
  el.setAttribute('role','alert'); el.setAttribute('aria-live','assertive'); el.setAttribute('aria-atomic','true');
  el.innerHTML = `
    <div class="toast-header ${bg}">
      <strong class="me-auto">${title}</strong>
      <small>baru saja</small>
      <button type="button" class="btn-close ${bg.includes('text-white')?'btn-close-white':''}" data-bs-dismiss="toast"></button>
    </div>
    <div class="toast-body">${(message ?? '').toString().replace(/\n/g,'<br>')}</div>`;
  toastArea.appendChild(el); new bootstrap.Toast(el, { delay: 4500, autohide: true }).show();
}

/** ========= Render session & error sebagai toast ========= */
@if(session('ok'))  makeToast('Berhasil', @json(session('ok')), 'success'); @endif
@if(session('error'))  makeToast('Perhatian', @json(session('error')), 'warning'); @endif
@if($errors->any())
  @foreach($errors->all() as $msg) makeToast('Error', @json($msg), 'danger'); @endforeach
@endif

/** ========= Typeahead ========= */
const qInput = document.getElementById('q');
const listEl = document.getElementById('q-suggest');
let qTimer = null;
function hideSuggest(){ listEl.style.display = 'none'; listEl.innerHTML = ''; }
function showSuggest(){ if(listEl.innerHTML.trim()!==''){ listEl.style.display = 'block'; } }
qInput?.addEventListener('input', () => {
  clearTimeout(qTimer);
  const term = qInput.value.trim();
  if(term.length < 2){ hideSuggest(); return; }
  qTimer = setTimeout(async () => {
    try{
      const params = new URLSearchParams({ term, status: 'A' });
      const res = await fetch(`{{ route('siswa.search') }}?`+params.toString(), { headers: { 'Accept': 'application/json' }});
      const data = await res.json();
      if(!Array.isArray(data) || data.length===0){ hideSuggest(); return; }
      listEl.innerHTML = data.map(it => `
        <div class="typeahead-item" data-value="${it.nis}">
          <span class="typeahead-nis">${it.nis}</span>
          <span class="typeahead-nama">${it.nama}</span>
          <span class="typeahead-kelas">${it.kelas ?? '-'}</span>
        </div>`).join('');
      showSuggest();
    }catch(e){ hideSuggest(); }
  }, 250);
});
listEl?.addEventListener('click', (ev) => {
  const row = ev.target.closest('.typeahead-item');
  if(!row) return;
  qInput.value = row.getAttribute('data-value');
  hideSuggest();
  document.getElementById('filter-form').submit();
});
document.addEventListener('click', (ev) => {
  if(!listEl.contains(ev.target) && ev.target !== qInput){ hideSuggest(); }
});

/** ====== MAPS dari controller ====== */
const paralelMap = @json($paralelMap);
const kelasIdMap = @json($kelasIdMap);

/** ====== Helper build paralel options ====== */
function buildParalelOptions(selectEl, grade, preselected=''){
  selectEl.innerHTML = '';
  const base = document.createElement('option');
  base.value = '';
  base.textContent = '— Pilih Paralel —';
  selectEl.appendChild(base);

  if(!grade || !paralelMap[grade]) return;

  paralelMap[grade].forEach(p => {
    const opt = document.createElement('option');
    opt.value = String(p);
    opt.textContent = String(p);
    if(preselected !== '' && String(preselected) === String(p)) opt.selected = true;
    selectEl.appendChild(opt);
  });
}

/** ====== Helper set kelas_id ====== */
function setKelasId(hiddenEl, grade, paralel){
  if(!hiddenEl) return;
  const gid = (kelasIdMap?.[grade]?.[String(paralel)]) ?? '';
  hiddenEl.value = gid;
}

/** ====== Tambah Siswa: grade + paralel -> kelas_id ====== */
const addGrade   = document.getElementById('add-grade');
const addParalel = document.getElementById('add-paralel');
const addKelasId = document.getElementById('add-kelas-id');

addGrade?.addEventListener('change', () => {
  buildParalelOptions(addParalel, addGrade.value, '');
  setKelasId(addKelasId, addGrade.value, addParalel.value);
});
addParalel?.addEventListener('change', () => {
  setKelasId(addKelasId, addGrade.value, addParalel.value);
});
document.getElementById('form-add-siswa')?.addEventListener('submit', (e) => {
  if(!addKelasId.value){
    e.preventDefault();
    makeToast('Perhatian', 'Pilih Kelas dan Paralel terlebih dahulu.', 'warning');
  }
});

/** ====== Filter section: grade -> paralel dinamis ====== */
const filterGrade    = document.getElementById('filter-grade');
const filterParalel  = document.getElementById('filter-paralel');
const initialGrade   = @json($filters['nama_kelas'] ?? '');
const initialParalel = @json($filters['kelas_paralel'] ?? '');

function rebuildFilterParalel(preselected){
  if (!filterParalel) return;
  const g = filterGrade?.value || '';
  filterParalel.innerHTML = '';
  const baseOpt = document.createElement('option');
  baseOpt.value = '';
  baseOpt.textContent = '— Semua —';
  filterParalel.appendChild(baseOpt);

  if (!g || !paralelMap[g]) return;

  paralelMap[g].forEach(p => {
    const opt = document.createElement('option');
    opt.value = String(p);
    opt.textContent = String(p);
    if (preselected && String(preselected) === String(p)) opt.selected = true;
    filterParalel.appendChild(opt);
  });
}
if (filterGrade) {
  if (initialGrade) filterGrade.value = initialGrade;
  rebuildFilterParalel(initialParalel);
  filterGrade.addEventListener('change', () => rebuildFilterParalel(''));
}

/** ====== Edit row: init paralel sesuai data awal + sync kelas_id ====== */
function initEditRows(){
  document.querySelectorAll('form.form-edit').forEach(form => {
    const rowId = form.dataset.row;
    const gradeSel = form.querySelector(`.grade-edit[data-row="${rowId}"]`);
    const paralelSel = form.querySelector(`.paralel-edit[data-row="${rowId}"]`);
    const hiddenId = document.getElementById(`kelas-id-${rowId}`);

    const initGrade = form.dataset.initialGrade || '';
    const initParalel = form.dataset.initialParalel || '';

    if(gradeSel && initGrade) gradeSel.value = initGrade;
    if(paralelSel) buildParalelOptions(paralelSel, gradeSel?.value || initGrade, initParalel);
    setKelasId(hiddenId, gradeSel?.value || initGrade, paralelSel?.value || initParalel);

    gradeSel?.addEventListener('change', () => {
      buildParalelOptions(paralelSel, gradeSel.value, '');
      setKelasId(hiddenId, gradeSel.value, paralelSel.value);
    });

    paralelSel?.addEventListener('change', () => {
      setKelasId(hiddenId, gradeSel.value, paralelSel.value);
    });

    form.addEventListener('submit', (e) => {
      if(!hiddenId.value){
        e.preventDefault();
        makeToast('Perhatian', 'Pilih Kelas dan Paralel yang valid.', 'warning');
      }
    });
  });
}
initEditRows();

/** ====== Konfirmasi EDIT ====== */
const modalEdit = new bootstrap.Modal(document.getElementById('modalConfirmEdit'));
const spanEditNama = document.getElementById('editNama');
let formToEdit = null;
document.querySelectorAll('.form-edit .btn-confirm-edit').forEach(btn => {
  btn.addEventListener('click', function () {
    formToEdit = this.closest('form.form-edit');
    spanEditNama.textContent = formToEdit?.dataset?.nama || '';
    modalEdit.show();
  });
});
document.getElementById('btnEditGo')?.addEventListener('click', function () {
  if (formToEdit) formToEdit.submit();
});

/** ====== BULK DELETE (tanpa nested form) ====== */
const bulkForm        = document.getElementById('bulkDeleteForm');
const bulkHidden      = document.getElementById('bulkHiddenIds');
const btnBulkDelete   = document.getElementById('btnBulkDelete');
const checkAll        = document.getElementById('check-all');
const modalDelete     = new bootstrap.Modal(document.getElementById('modalConfirmDelete'));
const spanDeleteCount = document.getElementById('deleteCount');

function getCheckedIds(){
  return [...document.querySelectorAll('.check-item:checked')].map(cb => cb.value);
}
function updateCheckAllState(){
  const items = document.querySelectorAll('.check-item');
  const total = items.length;
  const checked = document.querySelectorAll('.check-item:checked').length;
  if (!total) { checkAll.checked = false; checkAll.indeterminate = false; return; }
  checkAll.checked = (checked === total);
  checkAll.indeterminate = (checked > 0 && checked < total);
}
checkAll?.addEventListener('change', function(){
  document.querySelectorAll('.check-item').forEach(cb => { cb.checked = checkAll.checked; });
  updateCheckAllState();
});
document.querySelectorAll('.check-item').forEach(cb => cb.addEventListener('change', updateCheckAllState));

btnBulkDelete?.addEventListener('click', function(){
  const ids = getCheckedIds();
  if (!ids.length) {
    makeToast('Perhatian', 'Pilih minimal satu siswa terlebih dahulu.', 'warning');
    return;
  }
  spanDeleteCount.textContent = ids.length;
  modalDelete.show();
});

document.getElementById('btnDeleteGo')?.addEventListener('click', function(){
  const ids = getCheckedIds();
  if (!ids.length) {
    makeToast('Perhatian', 'Tidak ada siswa yang dipilih untuk dihapus.', 'warning');
    return;
  }

  // inject hidden input ids[]
  bulkHidden.innerHTML = '';
  ids.forEach(id => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'ids[]';
    input.value = id;
    bulkHidden.appendChild(input);
  });

  bulkForm.submit();
});

updateCheckAllState();
</script>
@endpush
@endsection
