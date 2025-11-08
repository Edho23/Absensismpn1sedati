@extends('layouts.app')
@section('title', 'Kartu RFID')

@section('content')
<div class="container-fluid px-4 py-3">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="fw-bold text-primary mb-0">
      <i class="bi bi-credit-card-2-front me-2"></i>Kartu RFID
    </h3>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-secondary" id="btn-test-api">
        <i class="bi bi-wifi"></i> Tes API
      </button>
    </div>
  </div>

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

  {{-- ====== Tambah Kartu (Register UID) ====== --}}
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
      <h6 class="fw-bold text-secondary mb-3">Tambah Kartu RFID</h6>

      <form action="{{ route('kartu.store') }}" method="POST" id="form-kartu" autocomplete="off">
        @csrf

        <div class="row g-3">
          <div class="col-md-6">
            <div class="position-relative">
              <label class="form-label fw-semibold">UID Kartu</label>
              <div class="input-group">
                <input type="text" id="uid" name="uid" class="form-control" placeholder="Tap kartu di alat..." readonly required>
                <button type="button" class="btn btn-outline-primary" id="btn-scan">
                  <i class="bi bi-upc-scan me-1"></i> Scan
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btn-stop" disabled>Stop</button>
              </div>
              <small class="text-muted d-block mt-1">
                Status: <span id="poll-status" class="text-secondary">Idle</span> • Device: <code id="device-label">REG-1</code>
              </small>
            </div>
          </div>

          <div class="col-md-6">
            <div class="position-relative">
              <label class="form-label fw-semibold">NIS</label>
              <input
                type="text"
                id="nis"
                name="nis"
                class="form-control"
                placeholder="Ketik NIS atau nama siswa..."
                required
                autocomplete="off"
                autocapitalize="off"
                spellcheck="false"
              >
              <div id="nis-suggest" class="typeahead-list" style="display:none;"></div>
            </div>
          </div>
        </div>

        <div class="mt-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ====== Tabel Daftar Kartu ====== --}}
  <div class="card border-0 shadow-sm rounded-4">
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle text-center">
        <thead class="table-light">
          <tr>
            <th>No</th>
            <th>UID</th>
            <th>NIS</th>
            <th>Nama</th>
            <th>Kelas</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        @forelse($kartu as $i => $k)
          <tr>
            <td>{{ $kartu->firstItem() + $i }}</td>
            <td>{{ $k->uid }}</td>
            <td>{{ $k->nis }}</td>
            <td class="text-start">{{ $k->siswa->nama ?? '-' }}</td>
            <td>{{ $k->siswa->kelas->nama_kelas ?? '-' }}</td>
            <td>
              <span class="badge {{ ($k->status ?? 'A') === 'A' ? 'bg-success' : 'bg-secondary' }}">
                {{ ($k->status ?? 'A') === 'A' ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td>
              <form method="POST" action="{{ route('kartu.destroy', $k->id) }}" onsubmit="return confirm('Hapus kartu ini?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-muted py-3">Belum ada kartu.</td></tr>
        @endforelse
        </tbody>
      </table>

      <div class="mt-3 d-flex justify-content-center">
        {{ $kartu->links('pagination::bootstrap-5') }}
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
  .typeahead-list{
    position:absolute; top:68px; left:0; right:0; z-index:2000;
    background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;
    box-shadow:0 8px 24px rgba(0,0,0,.08);
    max-height:260px; overflow:auto;
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
(function(){
  const BASE = window.location.origin;

  // ===== Tes API =====
  const btnTest = document.getElementById('btn-test-api');
  btnTest?.addEventListener('click', async () => {
    btnTest.disabled = true;
    try {
      const r = await fetch(`${BASE}/api/ping`, {cache:'no-store'});
      if (r.ok) {
        btnTest.classList.remove('btn-outline-secondary','btn-danger');
        btnTest.classList.add('btn-success');
        btnTest.innerHTML = '<i class="bi bi-check2-circle"></i> API OK';
      } else { throw new Error(); }
    } catch {
      btnTest.classList.remove('btn-outline-secondary','btn-success');
      btnTest.classList.add('btn-danger');
      btnTest.innerHTML = '<i class="bi bi-x-circle"></i> API GAGAL';
    } finally {
      setTimeout(()=>{ btnTest.disabled = false; }, 900);
    }
  });

  // ===== Scan dari alat (polling UID) =====
  let polling = null;
  const device = 'REG-1';
  const uidField = document.getElementById('uid');
  const btnScan = document.getElementById('btn-scan');
  const btnStop = document.getElementById('btn-stop');
  const pollStatus = document.getElementById('poll-status');
  document.getElementById('device-label').textContent = device;

  btnScan.addEventListener('click', () => {
    stopPolling('Idle');
    uidField.value = '';
    btnScan.disabled = true;
    btnStop.disabled = false;
    pollStatus.textContent = 'Menunggu UID...';
    startPolling();
  });

  btnStop.addEventListener('click', () => {
    stopPolling('Dihentikan');
  });

  function stopPolling(statusText){
    if (polling) { clearInterval(polling); polling = null; }
    btnScan.disabled = false;
    btnStop.disabled = true;
    pollStatus.textContent = statusText || 'Idle';
  }

  function normalizeNoColon(uid){
    if (!uid) return '';
    const hex = uid.replace(/[^0-9a-fA-F]/g,'').toUpperCase();
    return hex;
  }

  function startPolling(){
    polling = setInterval(async () => {
      try {
        const r = await fetch(`${BASE}/api/rfid/register-last`, {cache:'no-store', headers:{'Accept':'application/json'}});
        if (r.ok) {
          const data = await r.json();
          if (data && data.uid) {
            uidField.value = normalizeNoColon(data.uid);
            stopPolling('UID diterima');
          }
        }
      } catch (e) {}
    }, 800);
  }

  // ===== Typeahead NIS/Nama (pakai WEB route) =====
  const nisInput   = document.getElementById('nis');
  const suggestBox = document.getElementById('nis-suggest');
  let typingTimer = null;

  nisInput.addEventListener('input', () => {
    clearTimeout(typingTimer);
    const term = nisInput.value.trim();
    if (term.length < 1) { hideSuggest(); return; }
    typingTimer = setTimeout(()=> searchSiswa(term), 250);
  });

  // pakai mousedown + delegation supaya tidak kalah oleh blur
  suggestBox.addEventListener('mousedown', (e) => {
    const row = e.target.closest('.typeahead-item');
    if (!row) return;
    e.preventDefault(); // cegah blur dulu
    nisInput.value = row.dataset.nis || '';
    hideSuggest();
  });

  // hide kalau klik di luar
  document.addEventListener('mousedown', (e)=>{
    if (!suggestBox.contains(e.target) && e.target !== nisInput) hideSuggest();
  });

  function hideSuggest(){
    suggestBox.style.display='none'; suggestBox.innerHTML='';
  }

  async function searchSiswa(term){
    try{
      const url = `{{ route('siswa.search') }}?` + new URLSearchParams({
        term: term,
        status: 'A'
      }).toString();

      const r = await fetch(url, {headers:{'Accept':'application/json'}, cache:'no-store'});
      if (!r.ok) throw new Error('search fail');
      const list = await r.json(); // [{nis,nama,kelas,label}]
      if (!Array.isArray(list) || list.length === 0) { hideSuggest(); return; }

      suggestBox.innerHTML = list.slice(0,10).map(it => `
        <div class="typeahead-item" data-nis="${it.nis}">
          <span><span class="typeahead-nis">${it.nis}</span> — <span class="typeahead-nama">${it.nama??'-'}</span></span>
          <span class="typeahead-kelas">${it.kelas??'-'}</span>
        </div>
      `).join('');
      suggestBox.style.display = 'block';
    }catch(e){
      hideSuggest();
    }
  }
})();
</script>
@endpush
@endsection
