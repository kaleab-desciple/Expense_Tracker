<?php
include './Includes/Functions/functions.php';
include './Includes/Functions/auth.php';

$user_id = $_SESSION['user']['id'];

// Check if editing an existing income
if (isset($_GET['income_id'])) {
    $update = 1;
    $income_id = (int)$_GET['income_id'];
    $incomeInfo = get_info("incomes", $income_id);

    if (!$incomeInfo) {
        $_SESSION['ERROR'] = "Income record not found";
        header("Location: income.php");
        exit;
    }
} else {
    $update = 0;
    $incomeInfo = [
        'category_id' => '',
        'amount' => '',
        'date' => date('Y-m-d'),
        'description' => ''
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
    <?php include './top_scripts.php'; ?>
</head>
<body>

<?php include './Includes/header.php'; ?>

<section class="features7 cid-sENIyiRsb8" id="features08-3" style="min-height: 500px;">
    <div class="container">
        <div class="mbr-section-head pb-5">
            <h4 class="mbr-section-title mbr-fonts-style align-center mb-0 display-2">
                <strong><?= $update ? 'Edit' : 'Add' ?> Income</strong>
            </h4>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-12 mx-auto mbr-form form-col md-pb">
                <div class="form-wrap" data-form-type="formoid">
                    <form action="process_add_income.php" method="POST" class="mbr-form form-with-styler col-lg-6 mx-auto">
                        <input type="hidden" name="update" value="<?= $update ?>"/>
                        <?php if ($update): ?>
                            <input type="hidden" name="income_id" value="<?= $income_id ?>"/>
                        <?php endif; ?>

                        <div class="dragArea form-row">

                            <!-- CATEGORY DROPDOWN -->
                            <div class="col-sm-12 form-group">
                                <label><strong>Category</strong></label>
                                <select name="income[category_id]" class="form-control" required>
                                    <option value="">-- Select Category --</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" 
                                            <?= ($incomeInfo['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div style="clear: both"></div><br/>

                            <!-- AMOUNT -->
                            <div class="col-sm-12 form-group">
                                <label><strong>Amount</strong></label>
                                <input type="number" step="0.01" min="0" name="income[amount]" 
                                       class="form-control display-7" 
                                       value="<?= htmlspecialchars($incomeInfo['amount']) ?>" required/>
                            </div>

                            <div style="clear: both"></div><br/>

                            <!-- DATE -->
                            <div class="col-sm-12 form-group">
                                <label><strong>Date</strong></label>
                                <input type="date" name="income[date]" class="form-control display-7"
                                       value="<?= htmlspecialchars($incomeInfo['date']) ?>" required/>
                            </div>

                            <div style="clear: both"></div><br/>

                            <!-- DESCRIPTION -->
                            <div class="col-sm-12 form-group">
                                <label><strong>Description</strong></label>
                                <textarea name="income[description]" rows="4" class="form-control display-7" required><?= htmlspecialchars($incomeInfo['description']) ?></textarea>
                            </div>

                            <div style="clear: both"></div><br/>

                            <!-- ACTIONS -->
                            <div class="mbr-section-btn">
                                <button type="submit" class="btn btn-sm btn-secondary display-7">Save</button>
                                <a href="income.php" class="btn btn-sm btn-secondary display-7">Back</a>
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
