$(document).on('click', '#sales-actions', function (e) {
  e.preventDefault();

  const messageBox = document.getElementById('message-box');

  $.ajax({
    url: 'daily_sales_data.php',
    type: 'GET',
    dataType: 'json',
    success: function (response) {
      if (response.success) {
        $('#content-area').html(`
          <h5>Daily Sales Graph</h5>
          <canvas id="salesChart" height="100"></canvas>
        `);

        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
          type: 'line',
          data: {
            labels: response.labels,
            datasets: [{
              label: 'Total Sales ($)',
              data: response.data,
              backgroundColor: 'rgba(54, 162, 235, 0.2)',
              borderColor: 'rgba(54, 162, 235, 1)',
              borderWidth: 2,
              fill: true,
              tension: 0.3
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: {
                display: true,
                position: 'top'
              },
              title: {
                display: true,
                text: 'Sales Totals by Day'
              }
            },
            scales: {
              y: {
                beginAtZero: true
              }
            }
          }
        });

        if (messageBox) {
          messageBox.innerHTML = `<div class="alert alert-success">Graph loaded successfully.</div>`;
        }
      } else {
        $('#content-area').html(`<p class="text-danger">Failed to load chart data.</p>`);
      }
    },
    error: function (xhr, status, error) {
      $('#content-area').html(`<p class="text-danger">AJAX error: ${error}</p>`);
    }
  });
});