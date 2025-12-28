<?php
include './Includes/Functions/functions.php';
include './Includes/Functions/auth.php';
$user_id = $_SESSION['user']['id'];

// Check if this is an update
if (isset($_GET['expense_id'])) {
    $update = 1;
    $expense_id = $_GET['expense_id'];
    $expenseInfo = get_info("expenses", $expense_id);
} else {
    $update = 0;
}

// Fetch all categories
$categories_query = $db->query("SELECT * FROM categories ORDER BY name ASC");
$categories = [];
while ($category = $categories_query->fetch_assoc()) {
    $categories[] = $category;
}

// Set default date for new expense
$selected_date = $update && !empty($expenseInfo['date']) ? $expenseInfo['date'] : date('Y-m-d');
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $update ? "Edit" : "Add" ?> Expense</title>
    <?php include './top_scripts.php'; ?>
</head>
<body>
    <?php include './Includes/header.php'; ?>

    <section class="features7 cid-sENIyiRsb8" id="features08-3" style="min-height: 500px;">
        <div class="container">
            <div class="mbr-section-head pb-5">
                <h4 class="mbr-section-title mbr-fonts-style align-center mb-0 display-2">
                    <strong><?= $update ? "Edit Expense" : "Add Expense" ?></strong>
                </h4>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-12 mx-auto mbr-form form-col md-pb">
                    <div class="form-wrap" data-form-type="formoid">
                        <form action="process_add_expense.php" method="POST" class="mbr-form form-with-styler col-lg-6 mx-auto" data-form-title="Form Name">
                            <input type="hidden" name="update" value="<?= $update ?>"/>
                            <?php if ($update): ?>
                                <input type="hidden" name="expense_id" value="<?= $expense_id ?>"/>
                            <?php endif; ?>

                            <div class="dragArea form-row">
                                <!-- Amount -->
                                <div class="col-sm-12 form-group">
                                    <input type="number" step="0.01" min="0" name="expense[amount]" placeholder="Amount" 
                                        value="<?= $update ? $expenseInfo['amount'] : "" ?>" 
                                        class="form-control display-7" required="required"/>
                                </div>

                                <div style="clear: both"></div><br/>

                                <!-- Description -->
                                <div class="col-sm-12 form-group">
                                    <textarea rows="6" name="expense[description]" placeholder="Description" 
                                        class="form-control display-7" required="required"><?= $update ? $expenseInfo['description'] : "" ?></textarea>
                                </div>

                                <div style="clear: both"></div><br/>

                                <!-- Category -->
                                <div class="col-sm-12 form-group">
                                    <select class="form-control" name="expense[category_id]" required>
                                        <option value="">--SELECT CATEGORY--</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>" 
                                                <?= $update && $expenseInfo['category_id'] == $category['id'] ? "selected" : "" ?>>
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div style="clear: both"></div><br/>

                                <!-- Date -->
                                <div class="col-sm-12 form-group">
                                    <label for="expense_date">Date</label>
                                    <input type="date" id="expense_date" name="expense[date]" class="form-control display-7" 
                                        value="<?= $selected_date ?>" required
                                        pattern="\d{4}-\d{2}-\d{2}" 
                                        title="Enter a valid date in YYYY-MM-DD format"/>
                                </div>

                                <div style="clear: both"></div><br/>

                                <div class="mbr-section-btn">
                                    <button type="submit" class="btn btn-sm btn-secondary display-7">Save</button>
                                    <a href="manage_expenses.php" class="btn btn-sm btn-secondary display-7">Back</a>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include './bottom_scripts.php'; ?>
</body>
</html>
