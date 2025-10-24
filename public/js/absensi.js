// public/js/absensi.js
(function () {
  var input = document.getElementById('field-nis');
  var list  = document.getElementById('nis-suggest');
  if (!input || !list) return;

  var timer = null;
  var lastTerm = '';

  function hideList(){
    list.style.display = 'none';
    list.innerHTML = '';
  }

  function showLoading(){
    list.innerHTML =
      '<div class="typeahead-item" style="justify-content:center;color:#6b7280">Mencari…</div>';
    list.style.display = 'block';
  }

  function render(items){
    if (!items || !items.length){
      list.innerHTML =
        '<div class="typeahead-item" style="justify-content:center;color:#6b7280">Tidak ada hasil</div>';
      list.style.display = 'block';
      return;
    }
    list.innerHTML = items.map(function(x){
      return '<div class="typeahead-item" data-nis="'+x.nis+'">' +
               '<div>' +
                 '<div class="typeahead-nis">'+x.nis+'</div>' +
                 '<div class="typeahead-nama">'+x.nama+'</div>' +
               '</div>' +
               '<div class="typeahead-kelas">'+x.kelas+'</div>' +
             '</div>';
    }).join('');
    list.style.display = 'block';

    Array.prototype.forEach.call(list.querySelectorAll('.typeahead-item'), function(el){
      el.addEventListener('click', function(){
        var nis = this.getAttribute('data-nis');
        if (!nis) return;
        input.value = nis;
        hideList();
        input.focus();
      });
    });
  }

  function search(term){
    var url = '/siswa/search?term=' + encodeURIComponent(term);
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
      .then(function(res){
        if (!res.ok){
          console.error('Search failed', res.status, res.statusText);
          hideList();
          return [];
        }
        return res.json();
      })
      .then(render)
      .catch(function(err){
        console.error('Search error', err);
        hideList();
      });
  }

  input.addEventListener('input', function(){
    var term = (this.value||'').trim();
    if (term === lastTerm) return;
    lastTerm = term;
    clearTimeout(timer);

    if (term.length < 2){ hideList(); return; }

    showLoading();
    timer = setTimeout(function(){ search(term); }, 200);
  });

  document.addEventListener('click', function(e){
    if (!list.contains(e.target) && e.target !== input){ hideList(); }
  });
  input.addEventListener('keydown', function(e){
    if (e.key === 'Escape'){ hideList(); }
  });
})();
