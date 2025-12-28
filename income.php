<?php
include './Includes/Functions/functions.php';
include './Includes/Functions/auth.php';
$user_id = $_SESSION['user']['id'];

// Query incomes for the current user with category names
$sql = "SELECT i.id, i.amount, i.date, i.description, c.name AS category_name
        FROM incomes i
        LEFT JOIN categories c ON i.category_id = c.id
        WHERE i.user_id = $user_id
        ORDER BY i.date DESC";
$result = mysqli_query($db, $sql);

// Fetch result as associative array
$incomes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $incomes[] = $row;
}
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
                <strong>MANAGE INCOME</strong>
            </h4>
        </div>
        <div class="row">

            <?php if (!empty($incomes)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-condensed">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($incomes as $inc): ?>
                            <tr>
                                <td><?= $inc['id'] ?></td>
                                <td><?= htmlspecialchars($inc['category_name']) ?></td>
                                <td><?= number_format($inc['amount'], 2) ?></td>
                                <td><?= date('Y-m-d', strtotime($inc['date'])) ?></td>
                                <td><?= htmlspecialchars($inc['description']) ?></td>
                                <td><a href="add_edit_income.php?income_id=<?= $inc['id'] ?>" class="btn btn-sm btn-primary btn_extra_small">Edit</a></td>
                                <td><a onclick="return confirm('Are you sure you want to delete this income?')" href="delete_income.php?income_id=<?= $inc['id'] ?>" class="btn btn-sm btn-danger btn_extra_small">Delete</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <h4>No income added yet</h4>
            <?php endif; ?>

        </div>
        <a href="add_edit_income.php" class="btn btn-primary mt-3">Add INCOME</a>
    </div>
</section>

<?php include './bottom_scripts.php'; ?>
</body>
</html>
