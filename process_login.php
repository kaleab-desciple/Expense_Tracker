<?php
session_start();
include './Includes/Functions/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user = $_POST['user'] ?? [];
    $username = $user['username'] ?? '';
    $password = $user['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['ERROR'] = "Please enter username and password";
        header("Location: login.php");
        exit;
    }

    // Escape username
    $username = mysqli_real_escape_string($db, $username);

    // Fetch user
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($db, $sql);

    if (!$result || mysqli_num_rows($result) == 0) {
        $_SESSION['ERROR'] = "Invalid username or password";
        header("Location: login.php");
        exit;
    }

    $user_data = mysqli_fetch_assoc($result);

    // Verify password
    if (!password_verify($password, $user_data['password'])) {
        $_SESSION['ERROR'] = "Invalid username or password";
        header("Location: login.php");
        exit;
    }

    // Login successful
    $_SESSION['user'] = $user_data;
    $_SESSION['SUCCESS'] = "Logged in successfully";

    header("Location: index.php");
    exit;
}
?>
