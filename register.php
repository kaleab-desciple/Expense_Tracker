<?php
include './Includes/Functions/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget management system</title>
    <link rel="stylesheet" href="style2.css">
</head>
<body>
    <div class="main">
        <div class="form">

            <?php
            if (!empty($_SESSION['ERROR'])) {
                echo "<p style='color:red'>" . $_SESSION['ERROR'] . "</p>";
                unset($_SESSION['ERROR']);
            }
            if (!empty($_SESSION['SUCCESS'])) {
                echo "<p style='color:green'>" . $_SESSION['SUCCESS'] . "</p>";
                unset($_SESSION['SUCCESS']);
            }
            ?>

            <form action="process_register.php" method="post">
                <h2>Sign up Here</h2>

                <input type="text"
                       name="user[username]"
                       placeholder="Username"
                       required>

                <input type="email"
                       name="user[email]"
                       placeholder="Email address"
                       required>

                <input type="password"
                       name="user[password]"
                       placeholder="Password"
                       required>

                <input type="password"
                       name="user[confirm_password]"
                       placeholder="Confirm Password"
                       required>

                <input type="text"
                       name="birthday"
                       onfocus="(this.type='date')"
                       placeholder="Date of birth">

                <label class="container">Male
                    <input type="radio" name="gender" value="male" checked>
                    <span class="check"></span>
                </label>

                <label class="container">Female
                    <input type="radio" name="gender" value="female">
                    <span class="check"></span>
                </label>

                <button class="btnn" type="submit" name="save1">
                    <em>Sign up</em>
                </button>
            </form>
        </div>
    </div>
</body>
</html>
