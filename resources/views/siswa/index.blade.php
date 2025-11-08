@extends('layouts.app')
@section('title','Data Siswa')

@section('content')
<div class="container-fluid px-4 py-3">

  {{-- Header --}}
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h3 class="fw-bold text-primary mb-0">
      <i class="bi bi-people-fill me-2"></i>Data Siswa
    </h3>

    {{-- Tombol: Naikkan Kelas Otomatis (pakai modal/alert box) --}}
    <button type="button" class="btn btn-warning rounded-pill" data-bs-toggle="modal" data-bs-target="#promoteModal">
      <i class="bi bi-arrow-up-right-circle me-1"></i> Naikkan Kelas Otomatis
    </button>
  </div>

  {{-- ===================== FORM TAMBAH SISWA ===================== --}}
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <h6 class="fw-bold text-secondary mb-3">Tambah Siswa</h6>

      <form action="{{ route('siswa.store') }}" method="POST" class="row g-3 align-items-end" id="form-add-siswa">
        @csrf

        {{-- Baris 1: NIS, Nama, Angkatan --}}
        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">NIS</label>
          <input type="text" name="nis" class="form-control" placeholder="NIS" required>
        </div>
        <div class="col-lg-4">
          <label class="form-label fw-semibold small text-secondary">Nama</label>
          <input type="text" name="nama" class="form-control" placeholder="Nama lengkap" required>
        </div>
        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">Angkatan</label>
          <input type="number" name="angkatan" class="form-control" min="2000" max="2100" placeholder="Tahun">
        </div>

        {{-- Baris 2: Paralel (filter kelas), Kelas, Gender, Status --}}
        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">Kelas Paralel</label>
          <select class="form-select" id="add-paralel">
            <option value="">— Semua —</option>
            @foreach($daftarParalel as $p)
              <option value="{{ $p }}">{{ $p }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">Kelas</label>
          <select name="kelas_id" class="form-select" id="add-kelas" required>
            <option value="">— Pilih Kelas —</option>
            @foreach($kelas as $k)
              <option value="{{ $k->id }}" data-paralel="{{ $k->kelas_paralel }}">
                {{ $k->nama_kelas }} - {{ $k->kelas_paralel }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="col-lg-1">
          <label class="form-label fw-semibold small text-secondary">Gender</label>
          <select name="gender" class="form-select">
            <option value="">—</option>
            <option value="L">L</option>
            <option value="P">P</option>
          </select>
        </div>

        <div class="col-lg-1">
          <label class="form-label fw-semibold small text-secondary">Status</label>
          <select name="status" class="form-select">
            <option value="A">A</option>
            <option value="N">N</option>
          </select>
        </div>

        <div class="col-12">
          <button class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Simpan
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ===================== FILTER & PENCARIAN ===================== --}}
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <form method="GET" action="{{ route('siswa.index') }}" class="row g-3 align-items-end" id="filter-form" autocomplete="off">
        {{-- Pencarian + typeahead --}}
        <div class="col-lg-4 position-relative">
          <label class="form-label fw-semibold small text-secondary">Cari NIS/Nama</label>
          <input type="text" class="form-control" name="q" id="q" value="{{ $filters['q'] ?? '' }}" placeholder="Ketik NIS atau Nama...">
          <div id="q-suggest" class="typeahead-list" style="display:none;"></div>
        </div>

        {{-- Kelas --}}
        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">Kelas</label>
          <select name="kelas_id" class="form-select" id="filter-kelas">
            <option value="">— Semua —</option>
            @foreach($kelas as $k)
              <option value="{{ $k->id }}" data-paralel="{{ $k->kelas_paralel }}"
                {{ (string)($filters['kelas_id'] ?? '') === (string)$k->id ? 'selected' : '' }}>
                {{ $k->kelas_paralel }} - {{ $k->nama_kelas }}
              </option>
            @endforeach
          </select>
        </div>

        {{-- Kelas Paralel --}}
        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">Kelas Paralel</label>
          <select name="kelas_paralel" class="form-select" id="filter-paralel">
            <option value="">— Semua —</option>
            @foreach($daftarParalel as $p)
              <option value="{{ $p }}" {{ ($filters['kelas_paralel'] ?? '') == $p ? 'selected' : '' }}>{{ $p }}</option>
            @endforeach
          </select>
        </div>

        {{-- Gender --}}
        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">Gender</label>
          <select name="gender" class="form-select">
            <option value="">— Semua —</option>
            <option value="L" {{ ($filters['gender'] ?? '')==='L' ? 'selected' : '' }}>L</option>
            <option value="P" {{ ($filters['gender'] ?? '')==='P' ? 'selected' : '' }}>P</option>
          </select>
        </div>

        {{-- Angkatan --}}
        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">Angkatan</label>
          <input type="number" name="angkatan" class="form-control" min="2000" max="2100" value="{{ $filters['angkatan'] ?? '' }}" placeholder="Tahun">
        </div>

        {{-- Status --}}
        <div class="col-lg-2">
          <label class="form-label fw-semibold small text-secondary">Status</label>
          <select name="status" class="form-select">
            <option value="">— Semua —</option>
            <option value="A" {{ ($filters['status'] ?? '')==='A' ? 'selected' : '' }}>A (Aktif)</option>
            <option value="N" {{ ($filters['status'] ?? '')==='N' ? 'selected' : '' }}>N (Nonaktif)</option>
          </select>
        </div>

        <div class="col-12 d-flex gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-search me-1"></i>Filter
          </button>
          <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-clockwise me-1"></i>Reset
          </a>
        </div>
      </form>
    </div>
  </div>

  {{-- ===================== TABEL SISWA + INLINE EDIT ===================== --}}
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle text-center">
        <thead class="table-light">
          <tr>
            <th>No</th>
            <th>NIS</th>
            <th>Nama</th>
            <th>Gender</th>
            <th>Kelas</th>
            <th>Angkatan</th>
            <th>Status</th>
            <th style="width:140px">Aksi</th>
          </tr>
        </thead>
        <tbody>
        @forelse($siswa as $i => $s)
          <tr>
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
              <button class="btn btn-sm btn-outline-primary"
                      type="button"
                      data-bs-toggle="collapse"
                      data-bs-target="#edit-{{ $s->id }}">
                <i class="bi bi-pencil-square"></i>
              </button>
              <form action="{{ route('siswa.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus siswa ini?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>

          {{-- Row edit --}}
          <tr class="collapse" id="edit-{{ $s->id }}">
            <td colspan="8" class="bg-light-subtle">
              <form action="{{ route('siswa.update', $s->id) }}" method="POST" class="row g-2 align-items-end">
                @csrf @method('PUT')
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
                  <label class="form-label small text-secondary d-block">Paralel</label>
                  <select class="form-select form-select-sm paralel-edit" data-target="#kelas-{{ $s->id }}">
                    <option value="">— Semua —</option>
                    @foreach($daftarParalel as $p)
                      <option value="{{ $p }}">{{ $p }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="col-md-3">
                  <label class="form-label small text-secondary">Kelas</label>
                  <select name="kelas_id" class="form-select form-select-sm" id="kelas-{{ $s->id }}" required>
                    @foreach($kelas as $k)
                      <option value="{{ $k->id }}" data-paralel="{{ $k->kelas_paralel }}"
                        {{ (string)$s->kelas_id === (string)$k->id ? 'selected' : '' }}>
                        {{ $k->kelas_paralel }} - {{ $k->nama_kelas }}
                      </option>
                    @endforeach
                  </select>
                </div>

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
                  <button class="btn btn-sm btn-success">
                    <i class="bi bi-save"></i>
                  </button>
                </div>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-muted py-3">Belum ada data siswa.</td>
          </tr>
        @endforelse
        </tbody>
      </table>

      {{-- Pagination --}}
      <div class="mt-3 d-flex justify-content-center">
        {{ $siswa->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>

{{-- ====== MODAL KONFIRMASI PROMOSI (Alert Box) ====== --}}
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
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <form id="promoteForm" action="{{ route('siswa.promote') }}" method="POST" class="d-inline">
          @csrf
          <button type="submit" class="btn btn-warning">
            <i class="bi bi-check2-circle me-1"></i>Ya, Jalankan
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- ===== TOAST CONTAINER (selalu aktif) ===== --}}
<div class="toast-container position-fixed top-0 end-0 p-3" id="toastArea" style="z-index: 1080;"></div>

@push('styles')
<style>
  .typeahead-list{
    position:absolute; top:64px; left:0; right:0; z-index:30;
    background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;
    box-shadow:0 8px 24px rgba(0,0,0,.06);
  }
  .typeahead-item{ padding:8px 12px; cursor:pointer; font-size:13px; display:flex; justify-content:space-between; gap:12px; }
  .typeahead-item:hover{ background:#f3f4f6 }
  .typeahead-nis{ font-weight:700 }
  .typeahead-nama{ color:#374151 }
  .typeahead-kelas{ color:#6b7280; font-size:12px }
</style>
@endpush

@push('scripts')
<script>
/** ========= Toast helper (Bootstrap 5) ========= */
const toastArea = document.getElementById('toastArea');
function makeToast(title, message, type){
  const bg = {
    success:'bg-success text-white',
    danger:'bg-danger text-white',
    warning:'bg-warning',
    info:'bg-info'
  }[type] || 'bg-dark text-white';

  const el = document.createElement('div');
  el.className = 'toast align-items-center';
  el.setAttribute('role','alert');
  el.setAttribute('aria-live','assertive');
  el.setAttribute('aria-atomic','true');
  el.innerHTML = `
    <div class="toast-header ${bg}">
      <strong class="me-auto">${title}</strong>
      <small>baru saja</small>
      <button type="button" class="btn-close ${bg.includes('text-white')?'btn-close-white':''}" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">${message}</div>
  `;
  toastArea.appendChild(el);
  new bootstrap.Toast(el, { delay: 3500, autohide: true }).show();
}

/** ========= Render session & error sebagai toast ========= */
// Server success
@if(session('ok'))
  makeToast('Berhasil', @json(session('ok')), 'success');
@endif
// Server validation errors
@if($errors->any())
  @foreach($errors->all() as $msg)
    makeToast('Error', @json($msg), 'danger');
  @endforeach
@endif

/** ========= Typeahead (tetap sama) ========= */
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
      const res = await fetch(`{{ route('siswa.search') }}?`+params.toString(), { headers: { 'Accept': 'application/json' } });
      const data = await res.json();
      if(!Array.isArray(data) || data.length===0){ hideSuggest(); return; }
      listEl.innerHTML = data.map(it => `
        <div class="typeahead-item" data-value="\${it.nis}">
          <span class="typeahead-nis">\${it.nis}</span>
          <span class="typeahead-nama">\${it.nama}</span>
          <span class="typeahead-kelas">\${it.kelas ?? '-'}</span>
        </div>
      `).join('');
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

/** ====== Filter KELAS (form tambah) by paralel ====== */
const addParalel = document.getElementById('add-paralel');
const addKelas   = document.getElementById('add-kelas');
function filterAddKelas(){
  if(!addParalel || !addKelas) return;
  const p = addParalel.value;
  [...addKelas.options].forEach(opt => {
    if (!opt.value) return;
    opt.hidden = !!p && (opt.dataset.paralel !== p);
  });
  if (addKelas.selectedOptions.length && addKelas.selectedOptions[0].hidden) addKelas.value = '';
}
addParalel?.addEventListener('change', filterAddKelas);

/** ====== Filter KELAS (filter bar) by paralel ====== */
const filterParalel = document.getElementById('filter-paralel');
const filterKelas   = document.getElementById('filter-kelas');
function filterFilterKelas(){
  if(!filterParalel || !filterKelas) return;
  const p = filterParalel.value;
  [...filterKelas.options].forEach(opt => {
    if (!opt.value) return;
    opt.hidden = !!p && (opt.dataset.paralel !== p);
  });
}
filterParalel?.addEventListener('change', filterFilterKelas);

/** ====== Filter dinamis (row edit) ====== */
document.querySelectorAll('.paralel-edit').forEach(sel => {
  sel.addEventListener('change', () => {
    const target = document.querySelector(sel.dataset.target);
    const p = sel.value;
    [...target.options].forEach(opt => {
      if (!opt.value) return;
      opt.hidden = !!p && (opt.dataset.paralel !== p);
    });
    if (target.selectedOptions.length && target.selectedOptions[0].hidden) target.value = '';
  });
});

/** Init awal **/
filterFilterKelas();
filterAddKelas();
</script>
@endpush
@endsection
