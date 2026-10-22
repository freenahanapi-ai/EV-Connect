<?php
require_once "config/database.php";
require_once "includes/functions.php";

$error = "";
$fullName = "";
$username = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST["full_name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if (!checkToken()) {
        $error = "Invalid form submission. Please try again.";
    } elseif ($fullName === "" || $username === "" || $email === "" || $password === "") {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $error = "Password must contain at least 8 characters.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare(
            "SELECT user_id FROM users
             WHERE username = ? OR email = ?"
        );
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $error = "Username or email is already registered.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users (full_name, username, email, password_hash)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([
                $fullName,
                $username,
                $email,
                $passwordHash
            ]);

            header("Location: login.php?registered=1");
            exit;
        }
    }
}

$token = createToken();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | EV Connect</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/user-features.css">
</head>

<body>
    <?php require "includes/header.php"; ?>

    <main class="form-container">
        <h1>Create an Account</h1>
        <p>Register to book charging sessions and submit reviews.</p>

        <?php if ($error !== ""): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <input type="hidden" name="csrf_token" value="<?= e($token) ?>">

            <label for="full_name">Full Name</label>
            <input id="full_name" type="text" name="full_name"
                maxlength="100" required value="<?= e($fullName) ?>">

            <label for="username">Username</label>
            <input id="username" type="text" name="username"
                maxlength="50" required value="<?= e($username) ?>">

            <label for="email">Email</label>
            <input id="email" type="email" name="email"
                maxlength="100" required value="<?= e($email) ?>">

            <label for="password">Password (at least 8 characters)</label>
            <input id="password" type="password" name="password"
                minlength="8" required>

            <label for="confirm_password">Confirm Password</label>
            <input id="confirm_password" type="password"
                name="confirm_password" minlength="8" required>

            <button type="submit" class="btn">Register</button>
        </form>

        <p>Already registered? <a href="login.php">Login here</a>.</p>
    </main>

    <?php require "includes/footer.php"; ?>