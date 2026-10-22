<?php
require_once "config/database.php";
require_once "includes/functions.php";

requireLogin();

$stmt = $pdo->prepare(
    "SELECT b.booking_id, b.booking_date, b.start_time,
            b.end_time, b.booking_status,
            c.charger_type, s.station_name
     FROM bookings b
     JOIN chargers c ON b.charger_id = c.charger_id
     JOIN stations s ON c.station_id = s.station_id
     WHERE b.user_id = ?
     ORDER BY b.booking_date DESC, b.start_time DESC"
);
$stmt->execute([$_SESSION["user_id"]]);
$bookings = $stmt->fetchAll();

$token = createToken();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings | EV Connect</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/user-features.css">
</head>

<body>
    <?php require "includes/header.php"; ?>

    <main class="section">
        <h1>My Booking History</h1>

        <?php if (isset($_GET["created"])): ?>
            <p class="success">Your booking request was submitted.</p>
        <?php endif; ?>

        <?php if (isset($_GET["cancelled"])): ?>
            <p class="success">Your booking was cancelled.</p>
        <?php endif; ?>

        <?php if (count($bookings) === 0): ?>
            <div class="empty-state">
                <h3>You have no bookings yet.</h3>
                <p>Your charging reservations will appear here.</p>
                <a href="booking.php" class="btn">Book a Charger</a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="booking-table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Station</th>
                            <th>Charger Type</th>
                            <th>Date</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= e($booking["booking_id"]) ?></td>
                                <td><?= e($booking["station_name"]) ?></td>
                                <td><?= e($booking["charger_type"]) ?></td>
                                <td><?= e($booking["booking_date"]) ?></td>
                                <td><?= e(substr($booking["start_time"], 0, 5)) ?></td>
                                <td><?= e(substr($booking["end_time"], 0, 5)) ?></td>
                                <td><?= e($booking["booking_status"]) ?></td>
                                <td>
                                    <?php
                                    $canCancel =
                                        in_array(
                                            $booking["booking_status"],
                                            ["Pending", "Confirmed"],
                                            true
                                        ) &&
                                        $booking["booking_date"] >= date("Y-m-d");
                                    ?>

                                    <?php if ($canCancel): ?>
                                        <form method="POST" action="cancel-booking.php" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                            <input type="hidden" name="csrf_token"
                                                value="<?= e($token) ?>">
                                            <input type="hidden" name="booking_id"
                                                value="<?= e($booking["booking_id"]) ?>">
                                            <button type="submit" class="cancel-btn">
                                                Cancel
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <?php require "includes/footer.php"; ?>