<?php
require_once "db.php";

$username = "admin";
$email = "admin@evfinder.com";
$password = "Admin123!";

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO admins (username, email, password_hash)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)"
);

$stmt->bind_param("sss", $username, $email, $passwordHash);

if ($stmt->execute()) {
    echo "Admin account created successfully!<br>";
    echo "Username: admin<br>";
    echo "Password: Admin123!";
} else {
    echo "Error creating admin account.";
}
?>

