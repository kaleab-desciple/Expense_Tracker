<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Fetch the chart data from PHP
    fetch('get_chart_data.php')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('expensesChart').getContext('2d');

            const expensesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,   // categories
                    datasets: [{
                        label: 'Total Expenses',
                        data: data.totals,  // sums
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        })
        .catch(err => console.error('Error fetching chart data:', err));
</script>
