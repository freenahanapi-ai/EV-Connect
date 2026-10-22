<?php
session_start();
require_once "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare(
        "SELECT admin_id, username, password_hash
         FROM admins
         WHERE username = ?"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    $stmt->close();

    if ($admin && password_verify($password, $admin["password_hash"])) {
        session_regenerate_id(true);

        $_SESSION["admin_id"] = $admin["admin_id"];
        $_SESSION["admin_username"] = $admin["username"];

        header("Location: dashboard.php");
        exit;
    }

    $error = "Invalid username or password.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | EV Connect</title>

    <link rel="stylesheet" href="admin.css">
</head>

<body class="login-page">

    <main class="login-container">

        <header class="login-header">
            <h1>EV Connect</h1>
            <p>Admin Portal</p>
        </header>

        <div class="login-card">

            <div class="admin-header">
                <div>
                    <h2>Admin Login</h2>
                    <p class="div-description">
                        Sign in to manage the EV Connect system.
                    </p>
                </div>
            </div>

            <?php if ($error !== ""): ?>
                <div class="alert alert-error login-error" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="login-form">

                <label for="username">Username</label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    autocomplete="username"
                    required
                >

                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                >

                <button type="submit" class="login-button">
                    Login
                </button>

            </form>

        </div>

        <footer class="login-footer">
            <p>&copy; <?php echo date("Y"); ?> EV Connect. All rights reserved.</p>
        </footer>

    </main>

</body>
</html>