<?php
// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include './Includes/Functions/functions.php';
include './Includes/Functions/auth.php';

// Ensure user is logged in
if (!isset($_SESSION['user']['id'])) {
    die("User not logged in");
}

$user_id = $_SESSION['user']['id'];

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add_edit_budget.php");
    exit;
}

// Detect update
$update    = isset($_POST['update']) && $_POST['update'] == 1;
$budget_id = $update ? (int)$_POST['budget_id'] : null;

// Get submitted budget
$budget = $_POST['budget'] ?? [];

// ========================
// VALIDATION
// ========================
if (
    empty($budget['category_id']) ||
    empty($budget['limit_amount']) ||
    empty($budget['start_date']) ||
    empty($budget['end_date'])
) {
    $_SESSION['ERROR'] = "All fields are required";
    header("Location: add_edit_budget.php" . ($update ? "?budget_id=$budget_id" : ""));
    exit;
}

// Validate numeric limit_amount
if (!is_numeric($budget['limit_amount'])) {
    $_SESSION['ERROR'] = "Budget amount must be numeric";
    header("Location: add_edit_budget.php" . ($update ? "?budget_id=$budget_id" : ""));
    exit;
}

// Validate date format
foreach (['start_date', 'end_date'] as $d) {
    $date_obj = DateTime::createFromFormat('Y-m-d', $budget[$d]);
    if (!$date_obj || $date_obj->format('Y-m-d') !== $budget[$d]) {
        $_SESSION['ERROR'] = ucfirst(str_replace('_',' ',$d)) . " must be in YYYY-MM-DD format";
        header("Location: add_edit_budget.php" . ($update ? "?budget_id=$budget_id" : ""));
        exit;
    }
}

// Ensure category belongs to user
$cat_check = $db->prepare("SELECT id FROM categories WHERE id=? AND user_id=?");
$cat_check->bind_param("ii", $budget['category_id'], $user_id);
$cat_check->execute();
$cat_res = $cat_check->get_result();
if ($cat_res->num_rows == 0) {
    $_SESSION['ERROR'] = "Invalid category selected";
    header("Location: add_edit_budget.php" . ($update ? "?budget_id=$budget_id" : ""));
    exit;
}

// ========================
// INSERT / UPDATE
// ========================
if ($update) {
    $stmt = $db->prepare(
        "UPDATE budgets 
         SET category_id = ?, limit_amount = ?, start_date = ?, end_date = ?
         WHERE id = ? AND user_id = ?"
    );
    if (!$stmt) die("Prepare failed: " . $db->error);

    $stmt->bind_param(
        "idssii",
        $budget['category_id'],
        $budget['limit_amount'],
        $budget['start_date'],
        $budget['end_date'],
        $budget_id,
        $user_id
    );
} else {
    $stmt = $db->prepare(
        "INSERT INTO budgets (user_id, category_id, limit_amount, start_date, end_date)
         VALUES (?, ?, ?, ?, ?)"
    );
    if (!$stmt) die("Prepare failed: " . $db->error);

    $stmt->bind_param(
        "iidss",
        $user_id,
        $budget['category_id'],
        $budget['limit_amount'],
        $budget['start_date'],
        $budget['end_date']
    );
}

// Execute query
if (!$stmt->execute()) {
    die("Execution failed: " . $stmt->error);
}

$stmt->close();

$_SESSION['SUCCESS'] = "Budget saved successfully";
header("Location: budget.php");
exit;
?>
