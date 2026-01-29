function initCategoryPie(uid) {
  fetch(`../includes/charts.php?type=category_pie&uid=${uid}`)
    .then(r => r.json())
    .then(data => {
      const ctx = document.getElementById('pieCategory');
      new Chart(ctx, {
        type: 'pie',
        data: {
          labels: data.map(d => d.name),
          datasets: [{ data: data.map(d => Number(d.total)), backgroundColor: ['#60a5fa','#34d399','#f59e0b','#f87171','#a78bfa','#22d3ee','#fb7185'] }]
        }
      });
    });
}

function initMonthlyBar(uid) {
  fetch(`../includes/charts.php?type=monthly_bar&uid=${uid}`)
    .then(r => r.json())
    .then(data => {
      const ctx = document.getElementById('barMonthly');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: data.map(d => d.ym),
          datasets: [{ label:'Expense', data: data.map(d => Number(d.total)), backgroundColor:'#2563eb' }]
        },
        options: { scales: { y: { beginAtZero: true } } }
      });
    });
}