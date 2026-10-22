<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

// Helper function to display SQL query results as a table
function displayReport($conn, $title, $sql)
{
    echo '<div class="admin-div report-div">';

    echo '<h2>' . htmlspecialchars($title) . '</h2>';

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {

        echo '<div class="table-wrapper">';

        echo '<table class="admin-table">';

        echo '<thead><tr>';

        foreach ($result->fetch_fields() as $field) {
            echo '<th>' . htmlspecialchars($field->name) . '</th>';
        }

        echo '</tr></thead><tbody>';

        while ($row = $result->fetch_assoc()) {
            echo '<tr>';

            foreach ($row as $value) {
                echo '<td>' .
                    htmlspecialchars((string) ($value ?? "")) .
                    '</td>';
            }

            echo '</tr>';
        }

        echo '</tbody></table>';

        echo '</div>';

    } else {
        echo '<p class="empty-report">No records found for this report.</p>';
    }

    echo '</div>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SQL Reports | EV Connect</title>

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
        <a href="manage_bookings.php">Bookings</a>
        <a href="manage_reviews.php">Reviews</a>
        <a href="reports.php" class="active">Reports</a>
    </nav>

</header>

<!-- Page Banner -->
<div class="page-banner">

    <p class="eyebrow">EV CONNECT ADMINISTRATION</p>

    <h1>SQL Reports</h1>

    <p>
        View summaries and statistics from the EV Facility Finder database.
    </p>

</div>

<!-- Main Content -->
<main class="admin-container">

    <!-- Page Heading -->
    <div class="admin-header">

        <h1>Database Reports</h1>

        <p>
            Review charging station statistics, booking activity,
            facility distribution and user ratings.
        </p>

    </div>

    <!-- Page Actions -->
    <div class="admin-actions">

        <a href="dashboard.php" class="btn btn-secondary">
            &larr; Back to Dashboard
        </a>

    </div>

    <?php

    // Report 1: Number of chargers at each station
    displayReport(
        $conn,
        "1. Chargers Available at Each Station",
        "SELECT
            s.station_name AS 'Station Name',
            COUNT(c.charger_id) AS 'Total Chargers'
         FROM stations s
         LEFT JOIN chargers c
            ON s.station_id = c.station_id
         GROUP BY s.station_id, s.station_name
         ORDER BY COUNT(c.charger_id) DESC"
    );

    // Report 2: Number of bookings by status
    displayReport(
        $conn,
        "2. Booking Summary by Status",
        "SELECT
            booking_status AS 'Booking Status',
            COUNT(*) AS 'Total Bookings'
         FROM bookings
         GROUP BY booking_status
         ORDER BY COUNT(*) DESC"
    );

    // Report 3: Total bookings for each charging station
    displayReport(
        $conn,
        "3. Total Bookings by Charging Station",
        "SELECT
            s.station_name AS 'Station Name',
            COUNT(b.booking_id) AS 'Total Bookings'
         FROM stations s
         LEFT JOIN chargers c
            ON s.station_id = c.station_id
         LEFT JOIN bookings b
            ON c.charger_id = b.charger_id
         GROUP BY s.station_id, s.station_name
         ORDER BY COUNT(b.booking_id) DESC"
    );

    // Report 4: Number of facilities in each districts
    displayReport(
        $conn,
        "4. Facilities by Districts",
        "SELECT
            districts AS 'Districts',
            COUNT(*) AS 'Total Facilities'
         FROM facilities
         GROUP BY districts
         ORDER BY COUNT(*) DESC"
    );

    // Report 5: Average rating for each charging station
    displayReport(
        $conn,
        "5. Average Rating by Charging Station",
        "SELECT
            s.station_name AS 'Station Name',
            COUNT(r.review_id) AS 'Total Reviews',
            ROUND(AVG(r.rating), 2) AS 'Average Rating'
         FROM stations s
         LEFT JOIN reviews r
            ON s.station_id = r.station_id
            AND r.review_status = 'Visible'
         GROUP BY s.station_id, s.station_name
         ORDER BY AVG(r.rating) DESC"
    );

    ?>

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