(function(){
  const input = document.getElementById('field-nis');
  const box   = document.getElementById('nis-suggest');
  if(!input || !box) return;

  const API_URL = window.__ABSENSI__?.siswaSearchUrl;
  if(!API_URL){
    console.error("siswaSearchUrl belum di-inject dari blade.");
    return;
  }

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
    input.value = it.nis; // INPUT MANUAL: isi field nis dengan NIS
    hide();
  }

  async function search(term){
    // kalau controller search kamu butuh status, pakai seperti edit:
    // const status = "A";
    // const qs = new URLSearchParams({ term, status });

    const qs = new URLSearchParams({ term });

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
      setActive(activeIndex < max ? activeIndex + 1 : 0);
    } else if(e.key === "ArrowUp"){
      e.preventDefault();
      if(!items.length) return;
      setActive(activeIndex > 0 ? activeIndex - 1 : max);
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
