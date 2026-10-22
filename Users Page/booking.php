<?php
require_once "config/database.php";
require_once "includes/functions.php";

requireLogin();

$error = "";
$selectedCharger = "";
$bookingDate = "";
$startTime = "";
$endTime = "";

// Get available chargers at open stations
$stmt = $pdo->query(
    "SELECT c.charger_id, c.charger_type,
            s.station_name, s.address
     FROM chargers c
     JOIN stations s ON c.station_id = s.station_id
     WHERE c.status = 'Available'
       AND s.station_status = 'Open'
     ORDER BY s.station_name, c.charger_id"
);
$chargers = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $selectedCharger = $_POST["charger_id"] ?? "";
    $bookingDate = $_POST["booking_date"] ?? "";
    $startTime = $_POST["start_time"] ?? "";
    $endTime = $_POST["end_time"] ?? "";

    if (!checkToken()) {
        $error = "Invalid form submission. Please try again.";
    } elseif (
        $selectedCharger === "" ||
        $bookingDate === "" ||
        $startTime === "" ||
        $endTime === ""
    ) {
        $error = "Please complete all booking fields.";
    } elseif (
        !ctype_digit((string)$selectedCharger) ||
        !checkdate(
            (int)substr($bookingDate, 5, 2),
            (int)substr($bookingDate, 8, 2),
            (int)substr($bookingDate, 0, 4)
        ) ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $bookingDate) ||
        !preg_match('/^\d{2}:\d{2}$/', $startTime) ||
        !preg_match('/^\d{2}:\d{2}$/', $endTime)
    ) {
        $error = "Please enter valid booking details.";
    } elseif ($bookingDate < date("Y-m-d")) {
        $error = "Booking date cannot be in the past.";
    } elseif ($endTime <= $startTime) {
        $error = "End time must be later than start time.";
    } else {
        // Confirm that the selected charger can be booked
        $stmt = $pdo->prepare(
            "SELECT charger_id
             FROM chargers c
             JOIN stations s ON c.station_id = s.station_id
             WHERE c.charger_id = ?
               AND c.status = 'Available'
               AND s.station_status = 'Open'"
        );
        $stmt->execute([(int)$selectedCharger]);

        if (!$stmt->fetch()) {
            $error = "This charger is not available for booking.";
        } else {
            // Check for overlapping bookings
            $stmt = $pdo->prepare(
                "SELECT booking_id
                 FROM bookings
                 WHERE charger_id = ?
                   AND booking_date = ?
                   AND booking_status IN ('Pending', 'Confirmed')
                   AND start_time < ?
                   AND end_time > ?"
            );
            $stmt->execute([
                (int)$selectedCharger,
                $bookingDate,
                $endTime,
                $startTime
            ]);

            if ($stmt->fetch()) {
                $error = "This charger is already booked during that time.";
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO bookings
                     (user_id, charger_id, booking_date,
                      start_time, end_time, booking_status)
                     VALUES (?, ?, ?, ?, ?, 'Pending')"
                );

                $stmt->execute([
                    $_SESSION["user_id"],
                    (int)$selectedCharger,
                    $bookingDate,
                    $startTime,
                    $endTime
                ]);

                header("Location: booking-history.php?created=1");
                exit;
            }
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
    <title>Book a Charger | EV Connect</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/user-features.css">
</head>

<body>
    <?php require "includes/header.php"; ?>

    <main class="form-container">
        <h1>Charging Reservation</h1>
        <p>Submit a request to reserve an available charger.</p>

        <?php if ($error !== ""): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endif; ?>

        <?php if (count($chargers) === 0): ?>
            <p>No chargers are currently listed as available at open stations.</p>
        <?php else: ?>
            <form method="POST" action="booking.php">
                <input type="hidden" name="csrf_token" value="<?= e($token) ?>">

                <label for="charger_id">Charging Station and Charger</label>
                <select id="charger_id" name="charger_id" required>
                    <option value="">Select a charger</option>

                    <?php foreach ($chargers as $charger): ?>
                        <option value="<?= e($charger["charger_id"]) ?>"
                            <?= (string)$selectedCharger === (string)$charger["charger_id"]
                                ? "selected" : "" ?>>
                            <?= e($charger["station_name"]) ?>
                            - Charger <?= e($charger["charger_id"]) ?>
                            (<?= e($charger["charger_type"]) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="booking_date">Booking Date</label>
                <input id="booking_date" type="date" name="booking_date"
                    min="<?= e(date("Y-m-d")) ?>"
                    value="<?= e($bookingDate) ?>" required>

                <label for="start_time">Start Time</label>
                <input id="start_time" type="time" name="start_time"
                    value="<?= e($startTime) ?>" required>

                <label for="end_time">End Time</label>
                <input id="end_time" type="time" name="end_time"
                    value="<?= e($endTime) ?>" required>

                <button type="submit" class="btn">Submit Booking</button>
            </form>
        <?php endif; ?>
    </main>

    <?php require "includes/footer.php"; ?>