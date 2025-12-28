<?php
include './Includes/Functions/functions.php';
include './Includes/Functions/auth.php';

// Ensure user is logged in
if (!isset($_SESSION['user']['id'])) {
    die("Error: User not logged in.");
}

$user_id = $_SESSION['user']['id'];

// Ensure POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add_edit_expense.php");
    die;
}

// Determine if update or insert
$update = isset($_POST['update']) && $_POST['update'];
$expense_id = $update ? $_POST['expense_id'] : null;

// Get expense data
$expense = $_POST['expense'] ?? [];
$expense['user_id'] = $user_id;

// Validate required fields
if (empty($expense['amount']) || empty($expense['description']) || empty($expense['category_id'])) {
    $_SESSION['ERROR'] = "All fields are required";
    $redirect = $update ? "add_edit_expense.php?expense_id=$expense_id" : "add_edit_expense.php";
    header("Location: $redirect");
    die;
}

// Ensure numeric amount
if (!is_numeric($expense['amount'])) {
    $_SESSION['ERROR'] = "Amount must be a number";
    $redirect = $update ? "add_edit_expense.php?expense_id=$expense_id" : "add_edit_expense.php";
    header("Location: $redirect");
    die;
}

// Validate and format date strictly as YYYY-MM-DD
if (empty($expense['date'])) {
    $expense['date'] = date('Y-m-d'); // default today
} else {
    $date_obj = DateTime::createFromFormat('Y-m-d', $expense['date']);
    if (!$date_obj || $date_obj->format('Y-m-d') !== $expense['date']) {
        $_SESSION['ERROR'] = "Invalid date format. Use YYYY-MM-DD.";
        $redirect = $update ? "add_edit_expense.php?expense_id=$expense_id" : "add_edit_expense.php";
        header("Location: $redirect");
        die;
    }
    $expense['date'] = $date_obj->format('Y-m-d'); // ensure correct format
}

// --- Execute query ---
if ($update) {
    $stmt = $db->prepare("UPDATE expenses SET amount = ?, description = ?, category_id = ?, date = ? WHERE id = ? AND user_id = ?");
    if (!$stmt) die("Prepare failed: " . $db->error);

    $stmt->bind_param(
        "dsisii",
        $expense['amount'],
        $expense['description'],
        $expense['category_id'],
        $expense['date'],
        $expense_id,
        $user_id
    );

    if (!$stmt->execute()) {
        $_SESSION['ERROR'] = "Error updating expense: " . $stmt->error;
        $stmt->close();
        header("Location: add_edit_expense.php?expense_id=$expense_id");
        die;
    }

    $stmt->close();
} else {
    $stmt = $db->prepare("INSERT INTO expenses (amount, description, category_id, user_id, date) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) die("Prepare failed: " . $db->error);

    $stmt->bind_param(
        "dsiis",
        $expense['amount'],
        $expense['description'],
        $expense['category_id'],
        $user_id,
        $expense['date']
    );

    if (!$stmt->execute()) {
        $_SESSION['ERROR'] = "Error inserting expense: " . $stmt->error;
        $stmt->close();
        header("Location: add_edit_expense.php");
        die;
    }

    $stmt->close();
}

// Success
$_SESSION['SUCCESS'] = "Saved successfully";
header("Location: manage_expenses.php");
exit;
?>
