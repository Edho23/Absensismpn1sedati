(function(){
  const ctx = document.getElementById('chartAttendance');
  if (!ctx || !window.__chartData) return;
  const {labels, series} = window.__chartData;

  // Chart.js line
  new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{ label: 'Kehadiran', data: series, tension: 0.3 }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero:true, ticks: { precision:0 } } }
    }
  });
})();
