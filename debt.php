<?php
include './Includes/Functions/functions.php';
include './Includes/Functions/auth.php';

$user_id = $_SESSION['user']['id'];

// Fetch budgets with total expenses per category
$sql = "SELECT b.id AS budget_id,
               b.limit_amount AS budget,
               COALESCE(SUM(e.amount), 0) AS expense,
               (b.limit_amount - COALESCE(SUM(e.amount), 0)) AS debt,
               c.name AS description
        FROM budgets b
        LEFT JOIN categories c ON b.category_id = c.id
        LEFT JOIN expenses e ON e.category_id = b.category_id AND e.user_id = b.user_id
        WHERE b.user_id = ?
        GROUP BY b.id, b.limit_amount, c.name";

$stmt = $db->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$debt = [];
while ($row = $result->fetch_assoc()) {
    $debt[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <?php include './top_scripts.php'; ?>
    <title>Manage Debt</title>
</head>
<body>
<?php include './Includes/header.php'; ?>

<section class="features7 cid-sENIyiRsb8" id="features08-3" style="min-height: 500px;">
    <div class="container">
        <div class="mbr-section-head pb-5">
            <h4 class="mbr-section-title mbr-fonts-style align-center mb-0 display-2">
                <strong>MANAGE DEBT</strong>
            </h4>
        </div>
        <div class="row">
            <?php if (!empty($debt)): ?>
                <table class="table table-bordered table-striped table-condensed">
                    <thead>
                        <tr>
                            <th>DESCRIPTION</th>
                            <th>BUDGET</th>
                            <th>EXPENSE</th>
                            <th>DEBT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($debt as $deb): ?>
                            <tr>
                                <td><?= htmlspecialchars($deb['description'] ?? 'Uncategorized') ?></td>
                                <td><?= number_format($deb['budget'], 2) ?></td>
                                <td><?= number_format($deb['expense'], 2) ?></td>
                                <td><?= number_format($deb['debt'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <h4>No debt data yet</h4>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include './bottom_scripts.php'; ?>
</body>
</html>
