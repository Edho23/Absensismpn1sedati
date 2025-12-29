@extends('layouts.app')

@section('title', 'Edit Data Absensi')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary mb-0">
            <i class="bi bi-clock-history me-2"></i> Edit Data Presensi
        </h3>
        <a href="{{ route('absensi.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
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
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- FILTER BAR --}}
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('absensi.edit') }}" class="row g-2">
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        @foreach (['HADIR','SAKIT','IZIN','ALPA'] as $st)
                            <option value="{{ $st }}" {{ $filter_status===$st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Kelas</label>
                    <select name="kelas" class="form-select">
                        <option value="">Semua</option>
                        @foreach($daftarKelas as $k)
                            <option value="{{ $k }}" {{ $kelas===$k ? 'selected':'' }}>{{ $k }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Paralel</label>
                    <select name="kelas_paralel" class="form-select">
                        <option value="">Semua</option>
                        @foreach($daftarParalel as $p)
                            <option value="{{ $p }}" {{ $kelasParalel==$p ? 'selected':'' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-semibold">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">-</option>
                        <option value="L" {{ $gender==='L' ? 'selected':'' }}>L</option>
                        <option value="P" {{ $gender==='P' ? 'selected':'' }}>P</option>
                    </select>
                </div>

                {{-- Cari NIS/Nama + Typeahead --}}
                <div class="col-md-3 position-relative">
                    <label class="form-label fw-semibold">Cari NIS/Nama</label>
                    <input type="text"
                           name="q"
                           id="field-q"
                           value="{{ $qterm }}"
                           class="form-control"
                           placeholder="mis. 2210 / Budi"
                           autocomplete="off">
                    <div id="q-suggest" class="typeahead-list" style="display:none;"></div>
                    <small class="text-muted d-block mt-1">
                        Ketik min. 2 huruf. Klik saran untuk mengisi NIS otomatis.
                    </small>
                </div>

                <div class="col-12 d-flex gap-2 mt-2">
                    <button class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('absensi.edit') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- CARD TABLE --}}
    <div class="card border-0 shadow-sm rounded-4">

        {{-- BULK ACTION BAR (FORM BULK TIDAK MEMBUNGKUS TABLE) --}}
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <form method="POST" action="{{ route('absensi.bulk') }}" id="bulkForm" class="d-flex align-items-center gap-2">
                @csrf
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="checkAll">
                    <label for="checkAll" class="form-check-label">Pilih Semua</label>
                </div>

                <select name="status" class="form-select form-select-sm w-auto">
                    <option value="SAKIT">Set Sakit (S)</option>
                    <option value="IZIN">Set Izin (I)</option>
                    <option value="ALPA">Set Alpa (A)</option>
                    <option value="HADIR">Set Hadir (H)</option>
                </select>

                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                    <i class="bi bi-check2-all me-1"></i> Terapkan ke terpilih
                </button>

                {{-- Tempat inject hidden input ids[] via JS --}}
                <div id="bulkHidden"></div>
            </form>

            <small class="text-muted">
                Tanggal: <strong>{{ \Carbon\Carbon::parse($tanggal)->format('d/m/Y') }}</strong>
            </small>
        </div>

        <div class="card-body px-4 pb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th></th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Jam Masuk</th>
                            <th>Jam Pulang</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($absensi as $a)
                            <tr>
                                <td>
                                    {{-- Checkbox bulk: jangan pakai name="ids[]" karena kita inject via JS --}}
                                    <input type="checkbox" value="{{ $a->id }}" class="row-check">
                                </td>

                                <td>{{ $a->siswa->nis ?? '-' }}</td>
                                <td class="text-start">{{ $a->siswa->nama ?? '-' }}</td>
                                <td>{{ ($a->siswa->kelas->nama_kelas ?? '-') . ' ' . ($a->siswa->kelas->kelas_paralel ?? '-') }}</td>
                                <td>{{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '-' }}</td>
                                <td>{{ $a->jam_pulang ? \Carbon\Carbon::parse($a->jam_pulang)->format('H:i') : '-' }}</td>

                                {{-- SATU FORM UPDATE PER BARIS (STATUS + CATATAN) --}}
                                <td>
                                    <form action="{{ route('absensi.update', $a->id) }}" method="POST" class="d-flex align-items-center justify-content-center gap-2">
                                        @csrf
                                        @method('PUT')
                                        <select name="status_harian" class="form-select form-select-sm rounded-pill" style="min-width:120px">
                                            @foreach (['HADIR','SAKIT','IZIN','ALPA'] as $st)
                                                <option value="{{ $st }}" {{ $a->status_harian===$st ? 'selected':'' }}>{{ $st }}</option>
                                            @endforeach
                                        </select>
                                </td>

                                <td>
                                        <input type="text" name="catatan" value="{{ $a->catatan }}" class="form-control form-control-sm" placeholder="Catatan...">
                                </td>

                                <td class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill">
                                            Simpan
                                        </button>
                                    </form>

                                    <form action="{{ route('absensi.destroy', $a->id) }}" method="POST" onsubmit="return confirm('Hapus baris ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger rounded-pill">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="text-muted py-3">Belum ada data presensi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $absensi->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Typeahead dropdown */
    .typeahead-list{
        position:absolute; top:74px; left:0; right:0; z-index:30;
        background:#fff; border:1px solid #e5e7eb; border-radius:10px; overflow:hidden;
        box-shadow:0 8px 24px rgba(0,0,0,.06);
    }
    .typeahead-item{
        padding:8px 12px; cursor:pointer; font-size:13px; display:flex; justify-content:space-between; gap:12px;
    }
    .typeahead-item:hover{ background:#f3f4f6 }
    .typeahead-nis{ font-weight:700 }
    .typeahead-nama{ color:#374151 }
    .typeahead-kelas{ color:#6b7280; font-size:12px }
</style>
@endpush

@push('scripts')
<script>
  // ============ BULK (kode kamu) ============
  document.getElementById('checkAll')?.addEventListener('change', function(e){
      document.querySelectorAll('.row-check').forEach(cb => cb.checked = e.target.checked);
  });

  document.getElementById('bulkForm')?.addEventListener('submit', function(e){
      const checked = Array.from(document.querySelectorAll('.row-check:checked')).map(cb => cb.value);

      if (checked.length === 0) {
          e.preventDefault();
          alert('Pilih minimal 1 siswa.');
          return;
      }

      const holder = document.getElementById('bulkHidden');
      holder.innerHTML = '';
      checked.forEach(id => {
          const inp = document.createElement('input');
          inp.type = 'hidden';
          inp.name = 'ids[]';
          inp.value = id;
          holder.appendChild(inp);
      });
  });

  // ============ TYPEAHEAD FILTER Q (BARU) ============
  (function(){
    const input = document.getElementById('field-q');
    const box   = document.getElementById('q-suggest');
    if(!input || !box) return;

    const API_URL = @json(route('siswa.search'));
    let items = [];
    let activeIndex = -1;
    let lastController = null;
    let debounceTimer = null;

    function escHtml(str){
      return String(str)
        .replaceAll("&","&amp;")
        .replaceAll("<","&lt;")
        .replaceAll(">","&gt;")
        .replaceAll('"',"&quot;")
        .replaceAll("'","&#039;");
    }

    function hide(){
      box.style.display = "none";
      box.innerHTML = "";
      items = [];
      activeIndex = -1;
    }

    function show(){ box.style.display = "block"; }

    function render(list){
      items = list || [];
      activeIndex = -1;

      if(!items.length){
        box.innerHTML = `<div class="typeahead-item" style="cursor:default;">
          <span class="typeahead-nama">Tidak ditemukan</span>
        </div>`;
        show();
        return;
      }

      box.innerHTML = items.map((it, idx) => `
        <div class="typeahead-item" data-idx="${idx}">
          <div>
            <div>
              <span class="typeahead-nis">${escHtml(it.nis)}</span>
              <span class="typeahead-nama"> — ${escHtml(it.nama)}</span>
            </div>
            <div class="typeahead-kelas">${escHtml(it.kelas || "-")}</div>
          </div>
        </div>
      `).join("");

      show();
    }

    function setActive(idx){
      activeIndex = idx;
      const rows = box.querySelectorAll(".typeahead-item");
      rows.forEach(r => r.style.background = "");
      if(rows[activeIndex]) rows[activeIndex].style.background = "#f3f4f6";
    }

    function pick(it){
      // untuk filter GET, kita isi q dengan NIS agar konsisten
      input.value = it.nis;
      hide();
    }

    async function search(term){
      // default cari siswa aktif
      const status = "A";
      const qs = new URLSearchParams({ term, status });

      if(lastController) lastController.abort();
      lastController = new AbortController();

      const res = await fetch(`${API_URL}?${qs.toString()}`, {
        signal: lastController.signal,
        headers: { "Accept":"application/json" },
      });

      if(!res.ok) return render([]);
      const data = await res.json();
      render(data);
    }

    input.addEventListener("input", () => {
      const term = input.value.trim();
      if(term.length < 2) return hide();

      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => search(term), 250);
    });

    input.addEventListener("keydown", (e) => {
      if(box.style.display === "none") return;

      const max = items.length - 1;

      if(e.key === "ArrowDown"){
        e.preventDefault();
        if(!items.length) return;
        const next = activeIndex < max ? activeIndex + 1 : 0;
        setActive(next);
      } else if(e.key === "ArrowUp"){
        e.preventDefault();
        if(!items.length) return;
        const prev = activeIndex > 0 ? activeIndex - 1 : max;
        setActive(prev);
      } else if(e.key === "Enter"){
        if(activeIndex >= 0 && items[activeIndex]){
          e.preventDefault();
          pick(items[activeIndex]);
        }
      } else if(e.key === "Escape"){
        hide();
      }
    });

    box.addEventListener("click", (e) => {
      const row = e.target.closest(".typeahead-item");
      if(!row) return;
      const idx = Number(row.dataset.idx);
      if(Number.isFinite(idx) && items[idx]) pick(items[idx]);
    });

    document.addEventListener("click", (e) => {
      if(e.target === input || box.contains(e.target)) return;
      hide();
    });

    input.addEventListener("blur", () => {
      setTimeout(() => {
        if(!box.contains(document.activeElement)) hide();
      }, 150);
    });
  })();
</script>
@endpush
