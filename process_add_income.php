<?php
include './Includes/Functions/functions.php';
include './Includes/Functions/auth.php';

// Enable errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ensure POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add_edit_income.php");
    die;
}

$user_id = $_SESSION['user']['id'];

// Detect update or insert
$update = isset($_POST['update']) && $_POST['update'] == 1 ? 1 : 0;
$income_id = $update ? (int)$_POST['income_id'] : null;

// Get submitted income data
$income = $_POST['income'] ?? [];
$income['user_id'] = $user_id;

// Validation
if (empty($income['category_id']) || empty($income['amount']) || empty($income['description'])) {
    $_SESSION['ERROR'] = "All fields are required";
    $redirect = $update ? "add_edit_income.php?income_id=$income_id" : "add_edit_income.php";
    header("Location: $redirect");
    die;
}

if (!is_numeric($income['amount'])) {
    $_SESSION['ERROR'] = "Amount must be a number";
    $redirect = $update ? "add_edit_income.php?income_id=$income_id" : "add_edit_income.php";
    header("Location: $redirect");
    die;
}

// Set date: user input or today
if (empty($income['date'])) {
    $income['date'] = date('Y-m-d');
} else {
    $date_obj = DateTime::createFromFormat('Y-m-d', $income['date']);
    if (!$date_obj || $date_obj->format('Y-m-d') !== $income['date']) {
        $_SESSION['ERROR'] = "Invalid date format. Use YYYY-MM-DD.";
        $redirect = $update ? "add_edit_income.php?income_id=$income_id" : "add_edit_income.php";
        header("Location: $redirect");
        die;
    }
    $income['date'] = $date_obj->format('Y-m-d');
}

// Execute query
if ($update) {
    $stmt = $db->prepare(
        "UPDATE incomes SET category_id = ?, amount = ?, description = ?, date = ? WHERE id = ? AND user_id = ?"
    );
    if (!$stmt) die("Prepare failed: " . $db->error);

    $stmt->bind_param(
        "idssii",
        $income['category_id'],
        $income['amount'],
        $income['description'],
        $income['date'],
        $income_id,
        $user_id
    );

    if (!$stmt->execute()) {
        $_SESSION['ERROR'] = "Error updating income: " . $stmt->error;
        $stmt->close();
        header("Location: add_edit_income.php?income_id=$income_id");
        die;
    }

    $stmt->close();
} else {
    $stmt = $db->prepare(
        "INSERT INTO incomes (user_id, category_id, amount, date, description) VALUES (?, ?, ?, ?, ?)"
    );
    if (!$stmt) die("Prepare failed: " . $db->error);

    $stmt->bind_param(
        "iidss",
        $user_id,
        $income['category_id'],
        $income['amount'],
        $income['date'],
        $income['description']
    );

    if (!$stmt->execute()) {
        $_SESSION['ERROR'] = "Error inserting income: " . $stmt->error;
        $stmt->close();
        header("Location: add_edit_income.php");
        die;
    }

    $stmt->close();
}

$_SESSION['SUCCESS'] = "Saved successfully";
header("Location: income.php");
exit;
