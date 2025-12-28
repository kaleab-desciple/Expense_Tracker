<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include './Includes/Functions/functions.php';

// Check if user is logged in (based on header.php pattern)
if (!ss()) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Initialize variables
$from_date = '';
$to_date = '';
$expenses = [];
$error_message = '';

// Process form submission
if (isset($_GET['from_date']) && isset($_GET['to_date'])) {
    $from_date = $_GET['from_date'];
    $to_date = $_GET['to_date'];
    
    // Validate dates
    if (empty($from_date) || empty($to_date)) {
        $error_message = "Please select both from date and to date.";
    } elseif (strtotime($from_date) > strtotime($to_date)) {
        $error_message = "From date cannot be later than to date.";
    } else {
        // Use prepared statement to prevent SQL injection
        $sql = "SELECT e.id, e.description, e.amount, e.date, c.name AS category_name 
                FROM expenses e 
                LEFT JOIN categories c ON e.category_id = c.id 
                WHERE e.user_id = ? AND e.date BETWEEN ? AND ? 
                ORDER BY e.date DESC";
        
        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iss", $user_id, $from_date, $to_date);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $expenses[] = $row;
            }
            $stmt->close();
        } else {
            $error_message = "Database error: " . $db->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php include './top_scripts.php'; ?>
    <title>Date Filter - Expenses</title>
</head>
<body>
    <?php include './Includes/header.php'; ?>

    <section class="features7 cid-sENIyiRsb8" style="min-height: 500px;">
        <div class="container">
            <div class="mbr-section-head pb-5">
                <h4 class="mbr-section-title mbr-fonts-style align-center mb-0 display-2">
                    <strong>EXPENSE DATE FILTER</strong>
                </h4>
            </div>

            <!-- Filter Form -->
            <div class="card">
                <div class="card-header">
                    <h4>FILTER EXPENSES BY DATE</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>
                    
                    <form action="" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>From Date</label>
                                    <input type="date" name="from_date" value="<?= htmlspecialchars($from_date) ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>To Date</label>
                                    <input type="date" name="to_date" value="<?= htmlspecialchars($to_date) ?>" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label><br>
                                    <button type="submit" class="btn btn-primary">Filter Expenses</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Table -->
            <?php if (isset($_GET['from_date']) && isset($_GET['to_date'])): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h4>
                            EXPENSES FROM <?= htmlspecialchars($from_date) ?> TO <?= htmlspecialchars($to_date) ?>
                            <?php if (!empty($expenses)): ?>
                                <span class="badge badge-info"><?= count($expenses) ?> records found</span>
                            <?php endif; ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($expenses)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                            <th>Category</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($expenses as $expense): ?>
                                            <tr>
                                                <td><?= $expense['id'] ?></td>
                                                <td><?= htmlspecialchars($expense['description']) ?></td>
                                                <td>$<?= number_format($expense['amount'], 2) ?></td>
                                                <td><?= htmlspecialchars($expense['category_name'] ?? 'Uncategorized') ?></td>
                                                <td><?= date('Y-m-d', strtotime($expense['date'])) ?></td>
                                                <td>
                                                    <a href="add_edit_expense.php?expense_id=<?= $expense['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                                    <a onclick="return confirm('Are you sure you want to delete this expense?')" href="delete_expense.php?expense=<?= $expense['id'] ?>" class="btn btn-sm btn-danger">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Summary -->
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="alert alert-info">
                                        <strong>Total Amount:</strong> $<?= number_format(array_sum(array_column($expenses, 'amount')), 2) ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-info">
                                        <strong>Number of Transactions:</strong> <?= count($expenses) ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <h4>No Records Found</h4>
                                <p>No expenses found for the selected date range.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php include './bottom_scripts.php'; ?>
</body>
</html>
