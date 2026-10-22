<?php
require_once "config/database.php";
require_once "includes/functions.php";

$error = "";
$loginName = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $loginName = trim($_POST["login_name"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!checkToken()) {
        $error = "Invalid form submission. Please try again.";
    } elseif ($loginName === "" || $password === "") {
        $error = "Please enter your username/email and password.";
    } else {
        $stmt = $pdo->prepare(
            "SELECT user_id, full_name, username, password_hash
             FROM users
             WHERE username = ? OR email = ?
             LIMIT 1"
        );
        $stmt->execute([$loginName, $loginName]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password_hash"])) {
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["username"] = $user["username"];

            header("Location: index.html");
            exit;
        } else {
            $error = "Incorrect username/email or password.";
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
    <title>Login | EV Connect</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/user-features.css">
</head>

<body>

    <main class="form-container">
        <h1>Login</h1>
        <p>Login to manage your charging bookings.</p>

        <?php if (isset($_GET["registered"])): ?>
            <p class="success">Registration successful. You can now log in.</p>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= e($token) ?>">

            <label for="login_name">Username or Email</label>
            <input id="login_name" type="text" name="login_name"
                required value="<?= e($loginName) ?>">

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <button type="submit" class="btn">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </main>

    <?php require "includes/footer.php"; ?>