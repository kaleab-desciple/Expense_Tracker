// Ensure this script runs after the DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    // Function to generate random colors for bars
    function getRandomColor() {
        const r = Math.floor(Math.random() * 200) + 30; // avoid very dark colors
        const g = Math.floor(Math.random() * 200) + 30;
        const b = Math.floor(Math.random() * 200) + 30;
        return `rgba(${r}, ${g}, ${b}, 0.6)`;
    }

    // Get the canvas element
    const ctx = document.getElementById('expensesChart').getContext('2d');

    // Fetch chart data from PHP endpoint
    fetch('get_chart_data.php')
        .then(response => response.json())
        .then(data => {
            // Extract labels and values
            const labels = data.map(item => item.category);
            const totals = data.map(item => parseFloat(item.total));

            // Generate colors for each bar
            const backgroundColors = labels.map(() => getRandomColor());
            const borderColors = backgroundColors.map(c => c.replace('0.6', '1'));

            // Create Chart.js bar chart
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Expenses',
                        data: totals,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
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
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: $${context.raw.toFixed(2)}`;
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(err => console.error('Error fetching chart data:', err));
});
