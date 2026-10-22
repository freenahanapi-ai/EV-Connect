<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

// Update booking status
$error = "";
$allowedStatuses = ["Pending", "Confirmed", "Cancelled", "Completed"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $bookingId = filter_input(INPUT_POST, "booking_id", FILTER_VALIDATE_INT);
    $newStatus = trim($_POST["booking_status"] ?? "");

    if (!$bookingId || $bookingId < 1) {
        $error = "Invalid booking ID.";
    } elseif (!in_array($newStatus, $allowedStatuses, true)) {
        $error = "Invalid booking status.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE bookings
             SET booking_status = ?
             WHERE booking_id = ?"
        );

        $stmt->bind_param("si", $newStatus, $bookingId);

        if ($stmt->execute()) {
            $stmt->close();

            header("Location: manage_bookings.php?updated=1");
            exit;
        } else {
            $error = "Unable to update booking status.";
            $stmt->close();
        }
    }
}

// Retrieve booking information
$sql = "SELECT
            b.booking_id,
            u.full_name,
            s.station_name,
            c.charger_type,
            b.booking_date,
            b.start_time,
            b.end_time,
            b.booking_status
        FROM bookings b
        INNER JOIN users u
            ON b.user_id = u.user_id
        INNER JOIN chargers c
            ON b.charger_id = c.charger_id
        INNER JOIN stations s
            ON c.station_id = s.station_id
        ORDER BY b.booking_date DESC, b.start_time DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings | EV Connect</title>
    <link rel="stylesheet" href="admin.css">
</head>

<body>

<!-- Navigation Bar -->
<header class="navbar">

    <a href="dashboard.php" class="logo">
        <span class="logo-icon">⚡</span>

        <span>
            EV Connect
            <small>Admin Management</small>
        </span>
    </a>

    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_stations.php">Stations</a>
        <a href="manage_charger.php">Chargers</a>
        <a href="manage_facilities.php">Facilities</a>
        <a href="manage_bookings.php" class="active">Bookings</a>
        <a href="manage_reviews.php">Reviews</a>
        <a href="reports.php">Reports</a>
    </nav>

</header>

<!-- Page Banner -->
<div class="page-banner">

    <p class="eyebrow">EV CONNECT ADMINISTRATION</p>

    <h1>Manage Bookings</h1>

    <p>
        View user bookings and manage their booking statuses.
    </p>

</div>

<!-- Main Content -->
<main class="admin-container">

    <!-- Page Heading -->
    <div class="admin-header">

        <h1>Booking Records</h1>

        <p>
            View booking details, charging stations, charger types
            and current booking statuses.
        </p>

    </div>

    <!-- Page Actions -->
    <div class="admin-actions">

        <a href="dashboard.php" class="btn btn-secondary">
            &larr; Back to Dashboard
        </a>

    </div>

    <!-- Error Message -->
    <?php if ($error !== ""): ?>
        <div class="alert alert-error" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Success Message -->
    <?php if (isset($_GET["updated"])): ?>
        <div class="alert alert-success" role="status">
            Booking status updated successfully!
        </div>
    <?php endif; ?>

    <!-- Booking Table -->
    <div class="admin-div">

        <h2>All Bookings</h2>

        <p class="div-description">
            A list of bookings currently registered in the database.
        </p>

        <div class="table-wrapper">

            <table class="admin-table">

                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>User</th>
                        <th>Charging Station</th>
                        <th>Charger Type</th>
                        <th>Booking Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($result && $result->num_rows > 0): ?>

                        <?php while ($booking = $result->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php echo (int) $booking["booking_id"]; ?>
                                </td>

                                <td>
                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $booking["full_name"]
                                        );
                                        ?>
                                    </strong>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $booking["station_name"]
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $booking["charger_type"]
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $booking["booking_date"]
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $booking["start_time"]
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $booking["end_time"]
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    $status = $booking["booking_status"];
                                    $statusClass = "status-pending";

                                    if (
                                        strcasecmp($status, "Confirmed") === 0 ||
                                        strcasecmp($status, "Completed") === 0
                                    ) {
                                        $statusClass = "status-available";
                                    } elseif (
                                        strcasecmp($status, "Cancelled") === 0
                                    ) {
                                        $statusClass = "status-closed";
                                    }
                                    ?>

                                    <span class="status <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </td>

                                <td>
                                    <form
                                        method="POST"
                                        action="manage_bookings.php"
                                        class="action-buttons"
                                    >

                                        <input
                                            type="hidden"
                                            name="booking_id"
                                            value="<?php
                                                echo (int) $booking["booking_id"];
                                            ?>"
                                        >

                                        <select
                                            name="booking_status"
                                            class="status-select status-<?php echo strtolower($booking["booking_status"]); ?>"
                                            required
                                        >
                                            <?php foreach ($allowedStatuses as $statusOption): ?>
                                                <option
                                                    value="<?php echo htmlspecialchars($statusOption); ?>"
                                                    <?php
                                                    if ($booking["booking_status"] === $statusOption) {
                                                        echo "selected";
                                                    }
                                                    ?>
                                                >
                                                    <?php echo htmlspecialchars($statusOption); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <button
                                            type="submit"
                                            class="btn btn-secondary btn-small"
                                        >
                                            Update
                                        </button>

                                    </form>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="9">

                                <div class="empty-state">

                                    <h3>No Bookings Found</h3>

                                    <p>
                                        There are currently no bookings
                                        registered in the database.
                                    </p>

                                    <a href="add_booking.php" class="btn">
                                        + Add New Booking
                                    </a>

                                </div>

                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</main>

<!-- Footer -->
<footer class="footer">

    <a href="dashboard.php" class="footer-logo">
        ⚡ EV Connect
    </a>

    <p>Charge. Refuel. Go Further.</p>

    <div class="footer-links">
        <a href="dashboard.php">Dashboard</a>
        <a href="manage_stations.php">Stations</a>
        <a href="manage_charger.php">Chargers</a>
        <a href="manage_facilities.php">Facilities</a>
        <a href="manage_bookings.php">Bookings</a>
        <a href="manage_reviews.php">Reviews</a>
        <a href="reports.php">Reports</a>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <p class="copyright">
        &copy; 2026 EV Connect. All rights reserved.
    </p>

</footer>

</body>
</html>
