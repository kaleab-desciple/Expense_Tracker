<?php
// Enable error reporting for debugging
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

// Fetch all expenses with category names
$sql = "SELECT e.*, 
               COALESCE(e.amount, 0) AS amount,
               COALESCE(e.date, '') AS date,
               c.name AS category_name
        FROM expenses e
        LEFT JOIN categories c ON e.category_id = c.id
        WHERE e.user_id = ?
        ORDER BY e.date DESC";

$stmt = $db->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$expenses = [];
while ($row = $result->fetch_assoc()) {
    $expenses[] = $row;
}

$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <?php include './top_scripts.php'; ?>
    <title>Manage Expenses</title>
</head>
<body>
    <?php include './Includes/header.php'; ?>

    <section class="features7 cid-sENIyiRsb8" id="features08-3" style="min-height: 500px;">
        <div class="container">
            <div class="mbr-section-head pb-5">
                <h4 class="mbr-section-title mbr-fonts-style align-center mb-0 display-2">
                    <strong>ALL EXPENSES</strong>
                </h4>
            </div>

            <div class="row justify-content-center">
                <?php if (!empty($expenses)): ?>
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
                            <?php foreach ($expenses as $expense): ?>
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

    <?php include './bottom_scripts.php'; ?>
</body>
</html>
