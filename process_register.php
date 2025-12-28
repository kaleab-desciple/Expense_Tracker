<?php
// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

include './Includes/Functions/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect POST data
    $user     = $_POST['user'] ?? [];
    $birthday = $_POST['birthday'] ?? null;
    $gender   = $_POST['gender'] ?? null;

    // Required fields validation
    if (
        empty($user['username']) ||
        empty($user['email']) ||
        empty($user['password']) ||
        empty($user['confirm_password'])
    ) {
        $_SESSION['ERROR'] = "All required fields must be filled";
        header("Location: register.php");
        exit;
    }

    // Password match validation
    if ($user['password'] !== $user['confirm_password']) {
        $_SESSION['ERROR'] = "Passwords do not match";
        header("Location: register.php");
        exit;
    }

    // Email validation
    if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['ERROR'] = "Invalid email address";
        header("Location: register.php");
        exit;
    }

    // Sanitize inputs
    $username = mysqli_real_escape_string($db, $user['username']);
    $email    = mysqli_real_escape_string($db, $user['email']);
    $birthday = !empty($birthday) ? mysqli_real_escape_string($db, $birthday) : null;
    $gender   = !empty($gender) ? mysqli_real_escape_string($db, $gender) : null;

    // Check username uniqueness
    $check_username_sql = "SELECT id FROM users WHERE username='$username'";
    $check_username_result = mysqli_query($db, $check_username_sql);

    if (mysqli_num_rows($check_username_result) > 0) {
        $_SESSION['ERROR'] = "Username already exists";
        header("Location: register.php");
        exit;
    }

    // Check email uniqueness
    $check_email_sql = "SELECT id FROM users WHERE email='$email'";
    $check_email_result = mysqli_query($db, $check_email_sql);

    if (mysqli_num_rows($check_email_result) > 0) {
        $_SESSION['ERROR'] = "Email already exists";
        header("Location: register.php");
        exit;
    }

    // Hash password
    $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);

    // Registration timestamp
    $datetime_registered = date('Y-m-d H:i:s');

    // Insert user
    $insert_sql = "
        INSERT INTO users
        (username, email, password, birthday, gender, role, datetime_registered)
        VALUES
        ('$username', '$email', '$hashed_password', '$birthday', '$gender', 'user', '$datetime_registered')
    ";

    if (!mysqli_query($db, $insert_sql)) {
        die("Database Insert Error: " . mysqli_error($db));
    }

    // Get inserted user
    $user_id = mysqli_insert_id($db);
    $user_info = get_info('users', $user_id);

    // Store session
    $_SESSION['user'] = $user_info;
    $_SESSION['SUCCESS'] = "Account created successfully";

    // Redirect
    header("Location: index.php");
    exit;
}
