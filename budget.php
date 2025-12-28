<?php
include './Includes/Functions/functions.php';
include './Includes/Functions/auth.php';
$user_id = $_SESSION['user']['id'];

// Fetch budgets with category names
$sql = "SELECT b.*, c.name AS category_name
        FROM budgets b
        LEFT JOIN categories c ON b.category_id = c.id
        WHERE b.user_id = ?
        ORDER BY b.start_date DESC";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$budgets = [];
while ($row = $result->fetch_assoc()) {
    $budgets[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <?php include './top_scripts.php'; ?>
</head>
<body>
<?php include './Includes/header.php'; ?>

<section class="features7 cid-sENIyiRsb8" id="features08-3" style="min-height: 500px;">
    <div class="container">
        <div class="mbr-section-head pb-5">
            <h4 class="mbr-section-title mbr-fonts-style align-center mb-0 display-2">
                <strong>MANAGE BUDGET</strong>
            </h4>
        </div>

        <div class="row">
            <?php if (!empty($budgets)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-condensed">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($budgets as $b): ?>
                                <tr>
                                    <td><?= $b['id'] ?></td>
                                    <td><?= htmlspecialchars($b['category_name'] ?? 'N/A') ?></td>
                                    <td><?= number_format($b['limit_amount'], 2) ?></td>
                                    <td><?= !empty($b['start_date']) ? date('Y-m-d', strtotime($b['start_date'])) : 'N/A' ?></td>
                                    <td><?= !empty($b['end_date']) ? date('Y-m-d', strtotime($b['end_date'])) : 'N/A' ?></td>
                                    <td>
                                        <a href="add_edit_budget.php?budget_id=<?= $b['id'] ?>" class="btn btn-sm btn-primary btn-block">Edit</a>
                                    </td>
                                    <td>
                                        <a onclick="return confirm('Are you sure you want to delete this budget?')" href="delete_budget.php?budget_id=<?= $b['id'] ?>" class="btn btn-sm btn-danger btn-block">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <h4>No budgets added yet</h4>
            <?php endif; ?>
        </div>

        <a href="add_edit_budget.php" class="btn btn-primary mt-3">Add Budget</a>
    </div>
</section>

<?php include './bottom_scripts.php'; ?>
</body>
</html>
