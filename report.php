<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include './Includes/Functions/functions.php';

// Check if user is logged in
if (!ss()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Initialize variables
$budget_summary = [];
$expense_summary = [];
$category_summary = [];
$error_message = '';

try {
    // ----------------------
    // Fetch budget summary by category
    // ----------------------
    $sql_budget = "SELECT c.name AS category_name, 
                          COALESCE(SUM(b.limit_amount), 0) AS total_budget
                   FROM categories c
                   LEFT JOIN budgets b ON b.category_id = c.id AND b.user_id = ?
                   GROUP BY c.id
                   ORDER BY total_budget DESC";
    
    $stmt_budget = $db->prepare($sql_budget);
    $stmt_budget->bind_param("i", $user_id);
    $stmt_budget->execute();
    $result_budget = $stmt_budget->get_result();
    
    while ($row = $result_budget->fetch_assoc()) {
        $budget_summary[] = $row;
    }
    $stmt_budget->close();
    
    // ----------------------
    // Fetch expense summary by category
    // ----------------------
    $sql_expense = "SELECT c.name AS category_name, 
                           COALESCE(SUM(e.amount), 0) AS total_expense,
                           COUNT(e.id) AS transaction_count
                    FROM categories c
                    LEFT JOIN expenses e ON e.category_id = c.id AND e.user_id = ?
                    GROUP BY c.id
                    ORDER BY total_expense DESC";
    
    $stmt_expense = $db->prepare($sql_expense);
    $stmt_expense->bind_param("i", $user_id);
    $stmt_expense->execute();
    $result_expense = $stmt_expense->get_result();
    
    while ($row = $result_expense->fetch_assoc()) {
        $expense_summary[] = $row;
    }
    $stmt_expense->close();
    
    // ----------------------
    // Fetch recent expenses for chart
    // ----------------------
    $sql_recent = "SELECT e.description, e.amount, e.date, c.name AS category_name
                   FROM expenses e
                   LEFT JOIN categories c ON e.category_id = c.id
                   WHERE e.user_id = ?
                   ORDER BY e.date DESC
                   LIMIT 10";
    
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
    // Calculate totals
    // ----------------------
    $total_budget = array_sum(array_column($budget_summary, 'total_budget'));
    $total_expenses = array_sum(array_column($expense_summary, 'total_expense'));
    $total_transactions = array_sum(array_column($expense_summary, 'transaction_count'));
    $remaining_budget = $total_budget - $total_expenses;
    
    // ----------------------
    // Combine budget and expense data for comparison
    // ----------------------
    $comparison_data = [];
    foreach ($budget_summary as $budget_item) {
        $category_name = $budget_item['category_name'];
        
        // Find matching expense
        $matching_expense = null;
        foreach ($expense_summary as $expense_item) {
            if ($expense_item['category_name'] === $category_name) {
                $matching_expense = $expense_item;
                break;
            }
        }
        
        $comparison_data[] = [
            'category_name' => $category_name,
            'budget_amount' => $budget_item['total_budget'],
            'expense_amount' => $matching_expense ? $matching_expense['total_expense'] : 0,
            'transaction_count' => $matching_expense ? $matching_expense['transaction_count'] : 0,
            'remaining_budget' => $budget_item['total_budget'] - ($matching_expense ? $matching_expense['total_expense'] : 0)
        ];
    }
    
} catch (Exception $e) {
    $error_message = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include './top_scripts.php'; ?>
    <title>Expense Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include './Includes/header.php'; ?>

    <section class="features7 cid-sENIyiRsb8" style="min-height: 500px;">
        <div class="container">
            <div class="mbr-section-head pb-5">
                <h4 class="mbr-section-title mbr-fonts-style align-center mb-0 display-2">
                    <strong>EXPENSE REPORT</strong>
                </h4>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php else: ?>
                
                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Budget</h5>
                                <h3>$<?= number_format($total_budget, 2) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Expenses</h5>
                                <h3>$<?= number_format($total_expenses, 2) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card <?= $remaining_budget >= 0 ? 'bg-success' : 'bg-warning' ?> text-white">
                            <div class="card-body">
                                <h5 class="card-title"><?= $remaining_budget >= 0 ? 'Remaining' : 'Over Budget' ?></h5>
                                <h3>$<?= number_format(abs($remaining_budget), 2) ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Transactions</h5>
                                <h3><?= $total_transactions ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Budget vs Expenses Comparison Table -->
                <div class="card">
                    <div class="card-header">
                        <h4>Budget vs Expenses by Category</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($comparison_data)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Budget</th>
                                            <th>Expenses</th>
                                            <th>Remaining</th>
                                            <th>Transactions</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($comparison_data as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['category_name']) ?></td>
                                                <td>$<?= number_format($item['budget_amount'], 2) ?></td>
                                                <td>$<?= number_format($item['expense_amount'], 2) ?></td>
                                                <td>
                                                    <span class="<?= $item['remaining_budget'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                        $<?= number_format(abs($item['remaining_budget']), 2) ?>
                                                    </span>
                                                </td>
                                                <td><?= $item['transaction_count'] ?></td>
                                                <td>
                                                    <?php if ($item['budget_amount'] == 0): ?>
                                                        <span class="badge badge-secondary">No Budget</span>
                                                    <?php elseif ($item['remaining_budget'] >= 0): ?>
                                                        <span class="badge badge-success">Within Budget</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Over Budget</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <h4>No Data Available</h4>
                                <p>No budget or expense data found. Start by adding budgets and expenses.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Expenses Chart -->
                <?php if (!empty($recent_expenses)): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h4>Recent Expenses Distribution</h4>
                        </div>
                        <div class="card-body">
                            <div style="max-width: 400px; margin: 0 auto;">
                                <canvas id="expensesChart"></canvas>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Recent Expenses Table -->
                <?php if (!empty($recent_expenses)): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h4>Recent 10 Expenses</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>Category</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_expenses as $expense): ?>
                                            <tr>
                                                <td><?= date('Y-m-d', strtotime($expense['date'])) ?></td>
                                                <td><?= htmlspecialchars($expense['description']) ?></td>
                                                <td><?= htmlspecialchars($expense['category_name'] ?? 'Uncategorized') ?></td>
                                                <td>$<?= number_format($expense['amount'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </section>

    <?php include './bottom_scripts.php'; ?>

    <script>
        <?php if (!empty($recent_expenses)): ?>
        // Prepare chart data
        const ctx = document.getElementById('expensesChart').getContext('2d');
        const chartData = <?= json_encode(array_map(function($item) {
            return [
                'label' => $item['category_name'] ?? 'Uncategorized',
                'value' => floatval($item['amount'])
            ];
        }, $recent_expenses)) ?>;
        
        const expensesChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartData.map(item => item.label),
                datasets: [{
                    label: 'Expenses',
                    data: chartData.map(item => item.value),
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    title: {
                        display: true,
                        text: 'Expenses by Category'
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
