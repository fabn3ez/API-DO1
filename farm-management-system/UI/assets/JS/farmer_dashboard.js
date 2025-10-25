// farmer_dashboard.js

document.addEventListener("DOMContentLoaded", () => {
  // ✅ Draw production trend line chart
  const ctx1 = document.getElementById('productionTrendChart');
  if (ctx1 && typeof trendData !== 'undefined') {
    const months = trendData.map(row => row.month);
    const totals = trendData.map(row => Number(row.total));

    new Chart(ctx1, {
      type: 'line',
      data: {
        labels: months,
        datasets: [{
          label: 'Production Quantity',
          data: totals,
          fill: true,
          borderWidth: 2,
          borderColor: '#2e7d32',
          backgroundColor: 'rgba(46,125,50,0.2)',
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: true }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  }

  // ✅ Draw livestock distribution pie chart
  const ctx2 = document.getElementById('livestockDistributionChart');
  if (ctx2 && typeof livestockData !== 'undefined') {
    const types = livestockData.map(row => row.type);
    const counts = livestockData.map(row => Number(row.count));

    new Chart(ctx2, {
      type: 'pie',
      data: {
        labels: types,
        datasets: [{
          label: 'Livestock',
          data: counts,
          backgroundColor: ['#66bb6a', '#81c784', '#a5d6a7', '#388e3c', '#2e7d32']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });
  }

  // ✅ Refresh button reloads the page
  const refreshBtn = document.getElementById('refreshBtn');
  if (refreshBtn) {
    refreshBtn.addEventListener('click', () => location.reload());
  }
});
