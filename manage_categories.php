<?php
include './Includes/Functions/functions.php';
include './Includes/Functions/auth.php';


$user_id = $_SESSION['user']['id'];

// Fetch categories belonging to the logged-in user only
$sql = "SELECT * FROM categories WHERE user_id = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch categories as an associative array
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
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
                    <strong>MANAGE CATEGORIES</strong>
                </h4>
            </div>

            <!-- Display Success/Error Messages -->
            <?php if (!empty($_SESSION['SUCCESS'])): ?>
                <div style="color: green; margin-bottom: 20px;">
                    <?= $_SESSION['SUCCESS']; unset($_SESSION['SUCCESS']); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['ERROR'])): ?>
                <div style="color: red; margin-bottom: 20px;">
                    <?= $_SESSION['ERROR']; unset($_SESSION['ERROR']); ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <?php if (!empty($categories)): ?>
                    <table class="table table-bordered table-striped table-condensed">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Date Time</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?= $category['id'] ?></td>
                                    <td><?= htmlspecialchars($category['name']) ?></td>
                                    <td><?= date('Y-m-d H:i:s', strtotime($category['datetime_added'])) ?></td>
                                    <td>
                                        <a href="add_edit_category.php?category_id=<?= $category['id'] ?>" class="btn btn-sm btn-primary btn_extra_small">Edit</a>
                                    </td>
                                    <td>
                                        <a onclick="return confirm('Are you sure you want to delete this category? ALL EXPENSES UNDER THIS CATEGORY WILL BE DELETED')" href="delete_category.php?category_id=<?= $category['id'] ?>" class="btn btn-sm btn-primary btn_extra_small">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <h4>No categories added yet</h4>
                <?php endif; ?>
            </div>

            <a href="add_edit_category.php" class="btn btn-primary mt-3">Add Category</a>
        </div>
    </section>

    <?php include './bottom_scripts.php'; ?>
</body>
</html>
