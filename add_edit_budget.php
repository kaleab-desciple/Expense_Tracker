<?php
include './Includes/Functions/functions.php';
include './Includes/Functions/auth.php';

// Ensure user is logged in
if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Check if editing an existing budget
if (isset($_GET['budget_id'])) {
    $update = 1;
    $budget_id = (int) $_GET['budget_id'];

    // Fetch budget info from "budgets" table
    $budget = get_info("budgets", $budget_id);

    if (!$budget) {
        $_SESSION['ERROR'] = "Budget not found";
        header("Location: budget.php");
        exit;
    }
} else {
    $update = 0;
    $budget = [
        'category_id'  => '',
        'limit_amount' => '',
        'start_date'   => '',
        'end_date'     => ''
    ];
}

// Fetch categories for this user
$category_query = $db->prepare("SELECT id, name FROM categories WHERE user_id = ? ORDER BY name ASC");
$category_query->bind_param("i", $user_id);
$category_query->execute();
$result = $category_query->get_result();
$categories = $result->fetch_all(MYSQLI_ASSOC);
$category_query->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title><?= $update ? 'Edit' : 'Add' ?> Budget</title>
    <?php include './top_scripts.php'; ?>
</head>
<body>

<?php include './Includes/header.php'; ?>

<section class="container" style="min-height: 500px;">
    <h2 class="text-center mb-4"><?= $update ? 'Edit' : 'Add' ?> Budget</h2>

    <?php if (!empty($_SESSION['ERROR'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['ERROR']; unset($_SESSION['ERROR']); ?></div>
    <?php endif; ?>

    <form action="process_add_budget.php" method="POST" class="col-lg-6 mx-auto">
        <input type="hidden" name="update" value="<?= $update ?>">
        <?php if ($update): ?>
            <input type="hidden" name="budget_id" value="<?= $budget_id ?>">
        <?php endif; ?>

        <!-- CATEGORY -->
        <div class="form-group mb-3">
            <label><strong>Select Category</strong></label>
            <select name="budget[category_id]" class="form-control" required>
                <option value="">-- SELECT CATEGORY --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                        <?= ($budget['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- BUDGET AMOUNT -->
        <div class="form-group mb-3">
            <label><strong>Budget Amount</strong></label>
            <input type="number"
                   step="0.01"
                   min="0"
                   name="budget[limit_amount]"
                   class="form-control"
                   value="<?= htmlspecialchars($budget['limit_amount']) ?>"
                   required>
        </div>

        <!-- START DATE -->
        <div class="form-group mb-3">
            <label><strong>Start Date</strong></label>
            <input type="date"
                   name="budget[start_date]"
                   class="form-control"
                   value="<?= htmlspecialchars($budget['start_date']) ?>"
                   required>
        </div>

        <!-- END DATE -->
        <div class="form-group mb-4">
            <label><strong>End Date</strong></label>
            <input type="date"
                   name="budget[end_date]"
                   class="form-control"
                   value="<?= htmlspecialchars($budget['end_date']) ?>"
                   required>
        </div>

        <!-- ACTIONS -->
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="budget.php" class="btn btn-secondary">Back</a>
        </div>
    </form>
</section>

<?php include './bottom_scripts.php'; ?>
</body>
</html>
