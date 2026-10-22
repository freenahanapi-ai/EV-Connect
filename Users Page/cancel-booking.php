
<?php
require_once "config/database.php";
require_once "includes/functions.php";

requireLogin();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: booking-history.php");
    exit;
}

if (!checkToken()) {
    exit("Invalid form submission. Please return to your booking history.");
}

$bookingID = $_POST["booking_id"] ?? "";

if (!ctype_digit((string)$bookingID)) {
    header("Location: booking-history.php");
    exit;
}

// can only change eligible booking
$stmt = $pdo->prepare(
    "UPDATE bookings
     SET booking_status = 'Cancelled'
     WHERE booking_id = ?
       AND user_id = ?
       AND booking_status IN ('Pending', 'Confirmed')
       AND booking_date >= ?"
);

$stmt->execute([
    (int)$bookingID,
    $_SESSION["user_id"],
    date("Y-m-d")
]);

if ($stmt->rowCount() > 0) {
    header("Location: booking-history.php?cancelled=1");
} else {
    header("Location: booking-history.php");
}

exit;
?>
