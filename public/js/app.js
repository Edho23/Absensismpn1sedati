// highlight link aktif (fallback selain Blade)
document.querySelectorAll('.menu a').forEach(a=>{
  if (a.href === window.location.href) a.classList.add('active');
});

// dropdown toggle + persist state
document.querySelectorAll('.menu-dropdown .menu-toggle').forEach((btn, idx) => {
  const parent = btn.closest('.menu-dropdown');
  const key = 'sidebar.dd.'+idx;
  // restore
  if (localStorage.getItem(key) === 'open') parent.classList.add('open');
  btn.addEventListener('click', () => {
    parent.classList.toggle('open');
    localStorage.setItem(key, parent.classList.contains('open') ? 'open' : 'closed');
  });
});
