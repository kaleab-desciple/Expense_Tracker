<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



// Include functions and authentication
include './Includes/Functions/functions.php';
include './Includes/Functions/auth.php';

// Check if user is logged in
if (!isset($_SESSION['user']['id'])) {
    $_SESSION['ERROR'] = "You must be logged in to perform this action";
    header("Location: login.php");
    die;
}

// Ensure POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add_edit_category.php");
    die;
}

// Get logged-in user ID
$user_id = $_SESSION['user']['id'];

// Determine if this is an update
$update = isset($_POST['update']) && $_POST['update'] ? 1 : 0;
$category_id = $_POST['category_id'] ?? null;
$category = $_POST['category'] ?? [];

// Validate category name
if (empty($category['name'])) {
    $_SESSION['ERROR'] = "Category name is required";
    $redirect = $update ? "add_edit_category.php?category_id=$category_id" : "add_edit_category.php";
    header("Location: $redirect");
    die;
}

// Make sure $db is connected
if (!isset($db) || !$db instanceof mysqli) {
    die("Database connection not initialized");
}

if ($update) {
    // Check ownership to prevent editing someone else's category
    $stmt_check = $db->prepare("SELECT id FROM categories WHERE id = ? AND user_id = ?");
    $stmt_check->bind_param("ii", $category_id, $user_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows === 0) {
        $_SESSION['ERROR'] = "You cannot edit this category";
        $stmt_check->close();
        header("Location: manage_categories.php");
        die;
    }
    $stmt_check->close();

    // Update category
    $stmt = $db->prepare("UPDATE categories SET name = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $category['name'], $category_id, $user_id);

    if (!$stmt->execute()) {
        $_SESSION['ERROR'] = "Error updating category: " . $stmt->error;
        $stmt->close();
        header("Location: add_edit_category.php?category_id=$category_id");
        die;
    }
    $stmt->close();

} else {
    // Insert new category
    $datetime_added = date('Y-m-d H:i:s'); // current datetime

    $stmt = $db->prepare("INSERT INTO categories (user_id, name, datetime_added) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $category['name'], $datetime_added);

    if (!$stmt->execute()) {
        $_SESSION['ERROR'] = "Error inserting category: " . $stmt->error;
        $stmt->close();
        header("Location: add_edit_category.php");
        die;
    }
    $stmt->close();
}

// Success
$_SESSION['SUCCESS'] = "Category saved successfully";
header("Location: manage_categories.php");
die;
?>
