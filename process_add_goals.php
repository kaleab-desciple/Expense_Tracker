<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

include './Includes/Functions/functions.php';
include './Includes/Functions/auth.php';

// Ensure POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add_edit_goals.php");
    die;
}

$user_id = $_SESSION['user']['id'];

// Determine if update or insert
$update = isset($_POST['update']) && $_POST['update'] == 1;
$goal_id = $update ? (int)$_POST['goal_id'] : null;

// Get form data
$goal_data = $_POST['goals'] ?? [];
$title          = $goal_data['title'] ?? '';
$target_amount  = $goal_data['target_amount'] ?? 0;
$current_amount = $goal_data['current_amount'] ?? 0;
$deadline       = $goal_data['deadline'] ?? null;

// Validation
if (empty($title)) {
    $_SESSION['ERROR'] = "All fields are required";
    $redirect = $update ? "add_edit_goals.php?goal_id=$goal_id" : "add_edit_goals.php";
    header("Location: $redirect");
    die;
}

if ($update) {
    // Update existing goal
    $stmt = $db->prepare("UPDATE goals SET title = ?, target_amount = ?, current_amount = ?, deadline = ? WHERE id = ? AND user_id = ?");
    if (!$stmt) die("Prepare failed: " . $db->error);

    $stmt->bind_param("sddsii", $title, $target_amount, $current_amount, $deadline, $goal_id, $user_id);

    if (!$stmt->execute()) {
        $_SESSION['ERROR'] = "Error updating goal: " . $stmt->error;
        $stmt->close();
        header("Location: add_edit_goals.php?goal_id=$goal_id");
        die;
    }
    $stmt->close();
} else {
    // Insert new goal
    $stmt = $db->prepare("INSERT INTO goals (user_id, title, target_amount, current_amount, deadline) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) die("Prepare failed: " . $db->error);

    $stmt->bind_param("isdds", $user_id, $title, $target_amount, $current_amount, $deadline);

    if (!$stmt->execute()) {
        $_SESSION['ERROR'] = "Error inserting goal: " . $stmt->error;
        $stmt->close();
        header("Location: add_edit_goals.php");
        die;
    }
    $stmt->close();
}

$_SESSION['SUCCESS'] = "Saved successfully";
header("Location: goals.php");
die;
?>
