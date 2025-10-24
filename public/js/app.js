// public/js/app.js
(function () {
  // Aktifkan highlight submenu berdasar URL saat ini
  var current = window.location.pathname;
  document.querySelectorAll('.submenu-link').forEach(function (a) {
    try {
      if (a.pathname === current) a.classList.add('active');
    } catch (e) {}
  });

  // Toggle untuk menu custom (non-bootstrap)
  document.querySelectorAll('.menu-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var li = this.closest('.menu-dropdown');
      if (!li) return;
      li.classList.toggle('open');
    });
  });

  // Smooth scroll untuk anchor jika diperlukan
  document.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var id = this.getAttribute('href');
      if (!id || id === '#') return;
      var target = document.querySelector(id);
      if (!target) return;
      e.preventDefault();
      target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
})();
