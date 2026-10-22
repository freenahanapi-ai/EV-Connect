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
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $bookingDate) ||
        !checkdate(
            (int)substr($bookingDate, 5, 2),
            (int)substr($bookingDate, 8, 2),
            (int)substr($bookingDate, 0, 4)
        ) ||
        !preg_match('/^\d{2}:\d{2}$/', $startTime) ||
        !preg_match('/^\d{2}:\d{2}$/', $endTime)
    ) {
        $error = "Please enter valid booking details.";
    } elseif ($bookingDate < date("Y-m-d")) {
        $error = "Booking date cannot be in the past.";
    } elseif ($endTime <= $startTime) {
        $error = "End time must be later than start time.";
    } else {
        // Confirm that the selected charger is available
        $stmt = $pdo->prepare(
            "SELECT c.charger_id
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
                // Save the booking
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
    <title>Station Details | EV Facilities</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/user-features.css">
</head>

<body>

    <!-- Navigation Bar -->
    <nav class="navbar">
        <a href="index.html" class="logo">
            <span class="logo-icon">⚡</span>
            EV Facilities
        </a>

        <nav>
            <a href="index.html">Home</a>
            <a href="stations.html">EV Charging Stations</a>
            <a href="facilities.html">Facilities</a>
        </nav>
    </nav>

    <!-- Station Details Section -->
    <section class="section">

        <a href="stations.html" class="back-link">
            ← Back to Charging Stations
        </a>

        <div id="stationDetails" class="details-card">
            <div class="details-content">
                <p>Loading station details...</p>
            </div>
        </div>

        <!-- Booking Section -->

        <!-- PHP Booking Section -->
        <div class="booking-card" id="bookingSection">

            <div class="booking-header">
                <h2>⚡ Book a Charging Session</h2>
                <p>Choose your preferred charger, date and time.</p>
            </div>

            <?php if ($error !== ""): ?>
                <p class="error"><?= e($error) ?></p>
            <?php endif; ?>

            <?php if (count($chargers) === 0): ?>

                <p>No chargers are currently available at open stations.</p>

            <?php else: ?>

                <form method="POST" action="station-details.php<?= isset($_GET['id']) ? '?id=' . rawurlencode($_GET['id']) : '' ?>"
                    class="booking-form">

                    <input type="hidden" name="csrf_token"
                        value="<?= e($token) ?>">

                    <div class="booking-field">
                        <label for="charger_id">Charging Station and Charger</label>

                        <select id="charger_id" name="charger_id" required>
                            <option value="">Select a charger</option>

                            <?php foreach ($chargers as $charger): ?>
                                <option
                                    value="<?= e($charger["charger_id"]) ?>"
                                    <?= (string)$selectedCharger ===
                                        (string)$charger["charger_id"]
                                        ? "selected" : "" ?>>

                                    <?= e($charger["station_name"]) ?>
                                    - Charger <?= e($charger["charger_id"]) ?>
                                    (<?= e($charger["charger_type"]) ?>)

                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="booking-field">
                        <label for="booking_date">Booking Date</label>

                        <input
                            id="booking_date"
                            type="date"
                            name="booking_date"
                            min="<?= e(date("Y-m-d")) ?>"
                            value="<?= e($bookingDate) ?>"
                            required>
                    </div>

                    <div class="booking-field">
                        <label for="start_time">Start Time</label>

                        <input
                            id="start_time"
                            type="time"
                            name="start_time"
                            value="<?= e($startTime) ?>"
                            required>
                    </div>

                    <div class="booking-field">
                        <label for="end_time">End Time</label>

                        <input
                            id="end_time"
                            type="time"
                            name="end_time"
                            value="<?= e($endTime) ?>"
                            required>
                    </div>

                    <button type="submit" class="btn">
                        Submit Booking
                    </button>

                </form>

            <?php endif; ?>

        </div>


    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-logo">
            ⚡ EV Facilities
        </div>
        <p>Find EV charging stations and vehicle facilities in Brunei.</p>
        <p class="copyright">
            © 2026 EV Facilities. All rights reserved.
        </p>
    </footer>

    <!-- JavaScript -->
    <script>
        const stations = {
            1: {
                name: "EV Charging Station 01",
                location: "Royal Brunei Polo and Riding Club, Jerudong",
                type: "Type 2",
                total: 4,
                available: 2,
                hours: "24 hours",
                description: "A charging point for electric vehicle users."
            },

            2: {
                name: "EV Charging Station 02",
                location: "Tapak Perindustrian Beribi",
                type: "CCS2",
                total: 6,
                available: 0,
                hours: "24 hours",
                description: "A charging station with multiple charging points."
            },

            3: {
                name: "EV Charging Station 03",
                location: "Jalan Utama Berakas",
                type: "Type 2",
                total: 3,
                available: 1,
                hours: "24 hours",
                description: "A convenient charging point for EV drivers."
            },

            4: {
                name: "EV Charging Station 04",
                location: " Bandar Seri Begawan",
                type: "CCS2",
                total: 5,
                available: 5,
                hours: "24 hours",
                description: "A charging station with available charging points."
            }
        };

        // Get the station ID from the URL
        const params = new URLSearchParams(window.location.search);
        const stationId = params.get("id");
        const station = stations[stationId];

        const details = document.getElementById("stationDetails");


        if (station) {
            const isAvailable = station.available > 0;

            details.innerHTML = `
                <div class="details-banner">
                    <div class="station-symbol">⚡</div>
                </div>

                <div class="details-content">
                    <p class="eyebrow">EV CHARGING STATION</p>

                    <h1>${station.name}</h1>

                    <p class="details-description">
                        ${station.description}
                    </p>

                    <div class="detail-row">
                        <strong>Location</strong>
                        <span>${station.location}</span>
                    </div>

                    <div class="detail-row">
                        <strong>Connector Type</strong>
                        <span>${station.type}</span>
                    </div>

                    <div class="detail-row">
                        <strong>Total Charging Points</strong>
                        <span>${station.total}</span>
                    </div>

                    <div class="detail-row">
                        <strong>Available Charging Points</strong>
                        <span>${station.available}</span>
                    </div>

                    <div class="detail-row">
                        <strong>Opening Hours</strong>
                        <span>${station.hours}</span>
                    </div>

                    <div class="detail-row">
                        <strong>Status</strong>
                        <span class="status ${isAvailable ? "available" : "busy"}">
                            ${isAvailable ? "Available" : "Fully Occupied"}
                        </span>
                    </div>

                    <a href="stations.html" class="btn">
                        Back to Stations
                    </a>
                </div>
            `;

        } else {
            details.innerHTML = `
                <div class="empty-state">
                    <h2>Station Not Found</h2>
                    <p>Please select a valid charging station.</p>
                    <a href="stations.html" class="btn">
                        View All Stations
                    </a>
                </div>
            `;

            bookingSection.hidden = true;
        }
    </script>

</body>

</html>