<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include './Includes/Functions/functions.php';
include './Includes/Functions/auth.php';

// Ensure user is logged in
if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// ----------------------
// Fetch 5 most recent expenses
// ----------------------
$sql_recent = "SELECT e.id, 
                      COALESCE(e.amount, 0) AS amount, 
                      COALESCE(e.date, '') AS date, 
                      e.description, 
                      c.name AS category_name
               FROM expenses e
               LEFT JOIN categories c ON e.category_id = c.id
               WHERE e.user_id = ?
               ORDER BY e.date DESC
               LIMIT 5";

$stmt_recent = $db->prepare($sql_recent);
$stmt_recent->bind_param("i", $user_id);
$stmt_recent->execute();
$result_recent = $stmt_recent->get_result();

$recent_expenses = [];
while ($row = $result_recent->fetch_assoc()) {
    $recent_expenses[] = $row;
}
$stmt_recent->close();

// ----------------------
// Fetch total expenses per category for chart
// ----------------------
$sql_chart = "SELECT c.name AS category_name, 
                     COALESCE(SUM(e.amount), 0) AS total_amount
              FROM categories c
              LEFT JOIN expenses e ON e.category_id = c.id AND e.user_id = ?
              GROUP BY c.id
              ORDER BY total_amount DESC";

$stmt_chart = $db->prepare($sql_chart);
$stmt_chart->bind_param("i", $user_id);
$stmt_chart->execute();
$result_chart = $stmt_chart->get_result();

$chart_labels = [];
$chart_data = [];
while ($row = $result_chart->fetch_assoc()) {
    $chart_labels[] = $row['category_name'] ?? 'Uncategorized';
    $chart_data[] = floatval($row['total_amount']);
}
$stmt_chart->close();
?>
<!DOCTYPE html>
<html>
<head>
    <?php include './top_scripts.php'; ?>
    <title>My Expenses</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include './Includes/header.php'; ?>

    <!-- Recent Expenses Section -->
    <section class="features7 cid-sENIyiRsb8" style="min-height: 500px;">
        <div class="container">
            <div class="mbr-section-head pb-5">
                <h4 class="mbr-section-title mbr-fonts-style align-center mb-0 display-2">
                    <strong>MY RECENT EXPENSES</strong>
                </h4>
            </div>

            <div class="row justify-content-center">
                <?php if (!empty($recent_expenses)): ?>
                    <table class="table table-bordered table-striped table-condensed">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_expenses as $expense): ?>
                                <tr>
                                    <td><?= $expense['id'] ?></td>
                                    <td><?= htmlspecialchars($expense['description']) ?></td>
                                    <td><?= number_format($expense['amount'], 2) ?></td>
                                    <td><?= htmlspecialchars($expense['category_name'] ?? 'N/A') ?></td>
                                    <td><?= !empty($expense['date']) ? date('Y-m-d', strtotime($expense['date'])) : 'N/A' ?></td>
                                    <td><a href="add_edit_expense.php?expense_id=<?= $expense['id'] ?>" class="btn btn-sm btn-primary">Edit</a></td>
                                    <td><a onclick="return confirm('Are you sure you want to delete this expense?')" href="delete_expense.php?expense=<?= $expense['id'] ?>" class="btn btn-sm btn-danger">Delete</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <h4>No expenses added yet</h4>
                <?php endif; ?>
            </div>

            <a href="add_edit_expense.php" class="btn btn-primary">Add Expense</a>
        </div>
    </section>

    <!-- Chart Section -->
    <section class="chart-section" style="padding: 50px; background: #f5f5f5;">
        <div class="container">
            <h3 class="text-center mb-4">Expenses by Category</h3>
            <canvas id="expensesChart" width="400" height="200"></canvas>
        </div>
    </section>

    <?php include './bottom_scripts.php'; ?>

    <script>
        const ctx = document.getElementById('expensesChart').getContext('2d');
        const expensesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [{
                    label: 'Total Expenses',
                    data: <?= json_encode($chart_data) ?>,
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
    </script>
</body>
</html>
