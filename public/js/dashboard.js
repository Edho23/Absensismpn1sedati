// public/js/dashboard.js
(function () {
  var el = document.getElementById('chartAttendance');
  if (!el || typeof Chart === 'undefined') return;

  var data = (window.__chartData || {});
  var labels = Array.isArray(data.labels) ? data.labels : [];
  var series = Array.isArray(data.series) ? data.series : [];

  var ctx = el.getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Jumlah Kehadiran',
        data: series,
        tension: 0.3,
        fill: false
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: true },
        tooltip: { enabled: true }
      },
      scales: {
        x: { ticks: { autoSkip: false } },
        y: { beginAtZero: true, precision: 0 }
      }
    }
  });
})();
