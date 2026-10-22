<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

// Count charging stations
$stationsResult = $conn->query(
    "SELECT COUNT(*) AS total FROM stations"
);
$totalStations = $stationsResult->fetch_assoc()["total"];

// Count chargers
$chargersResult = $conn->query(
    "SELECT COUNT(*) AS total FROM chargers"
);
$totalChargers = $chargersResult->fetch_assoc()["total"];

// Count vehicle facilities
$facilitiesResult = $conn->query(
    "SELECT COUNT(*) AS total FROM facilities"
);
$totalFacilities = $facilitiesResult->fetch_assoc()["total"];

// Count bookings
$bookingsResult = $conn->query(
    "SELECT COUNT(*) AS total FROM bookings"
);
$totalBookings = $bookingsResult->fetch_assoc()["total"];

// Count pending bookings
$pendingResult = $conn->query(
    "SELECT COUNT(*) AS total
     FROM bookings
     WHERE booking_status = 'Pending'"
);
$pendingBookings = $pendingResult->fetch_assoc()["total"];

// Count visible reviews
$reviewsResult = $conn->query(
    "SELECT COUNT(*) AS total
     FROM reviews
     WHERE review_status = 'Visible'"
);
$visibleReviews = $reviewsResult->fetch_assoc()["total"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | EV Connect</title>

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
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="manage_stations.php">Stations</a>
        <a href="manage_charger.php">Chargers</a>
        <a href="manage_facilities.php">Facilities</a>
        <a href="manage_bookings.php">Bookings</a>
        <a href="manage_reviews.php">Reviews</a>
        <a href="reports.php">Reports</a>
    </nav>

</header>


<!-- Dashboard Banner -->
<div class="page-banner">

    <p class="eyebrow">EV CONNECT ADMINISTRATION</p>

    <h1>Admin Dashboard</h1>

    <p>
        Manage charging stations, vehicle facilities,
        bookings and customer reviews in one place.
    </p>

</div>


<!-- Dashboard Content -->
<main class="admin-container">

    <!-- Welcome Div -->
    <div class="admin-header">

        <p class="eyebrow">YOUR ADMIN WORKSPACE</p>

        <h1>
            Welcome, <?php
                echo htmlspecialchars(
                    $_SESSION["admin_username"] ?? "Admin"
                );
            ?>!
        </h1>

        <p>
            Here's an overview of your EV Connect system.
            Select a div below to manage your records.
        </p>

    </div>


    <!-- System Overview -->
    <div class="admin-div">

        <div class="admin-header">
            <h1>System Overview</h1>

            <p>
                A quick summary of the current records
                stored in your database.
            </p>
        </div>

        <div class="dashboard-cards">

            <!-- Charging Stations -->
            <a href="manage_stations.php" class="dashboard-card">

                <div class="card-icon">⚡</div>

                <h3>Total Charging Stations</h3>

                <p class="card-number">
                    <?php echo $totalStations; ?>
                </p>

                <span class="card-link">
                    Manage Stations &rarr;
                </span>

            </a>


            <!-- Chargers -->
            <a href="manage_charger.php" class="dashboard-card">

                <div class="card-icon">🔌</div>

                <h3>Total Chargers</h3>

                <p class="card-number">
                    <?php echo $totalChargers; ?>
                </p>

                <span class="card-link">
                    Manage Chargers &rarr;
                </span>

            </a>


            <!-- Facilities -->
            <a href="manage_facilities.php" class="dashboard-card">

                <div class="card-icon">🚗</div>

                <h3>Total Vehicle Facilities</h3>

                <p class="card-number">
                    <?php echo $totalFacilities; ?>
                </p>

                <span class="card-link">
                    Manage Facilities &rarr;
                </span>

            </a>


            <!-- Bookings -->
            <a href="manage_bookings.php" class="dashboard-card">

                <div class="card-icon">📋</div>

                <h3>Total Bookings</h3>

                <p class="card-number">
                    <?php echo $totalBookings; ?>
                </p>

                <span class="card-link">
                    View Bookings &rarr;
                </span>

            </a>


            <!-- Pending Bookings -->
            <a href="manage_bookings.php" class="dashboard-card">

                <div class="card-icon">⏳</div>

                <h3>Pending Bookings</h3>

                <p class="card-number">
                    <?php echo $pendingBookings; ?>
                </p>

                <span class="card-link">
                    Review Bookings &rarr;
                </span>

            </a>


            <!-- Visible Reviews -->
            <a href="manage_reviews.php" class="dashboard-card">

                <div class="card-icon">⭐</div>

                <h3>Visible Reviews</h3>

                <p class="card-number">
                    <?php echo $visibleReviews; ?>
                </p>

                <span class="card-link">
                    Manage Reviews &rarr;
                </span>

            </a>

        </div>

    </div>


    <!-- Database Management -->
    <div class="admin-div">

        <div class="div-heading">
            <p class="eyebrow">SYSTEM MANAGEMENT</p>

            <h2>Database Management</h2>

            <p>
                Access the different divs to maintain
                and manage your system records.
            </p>
        </div>

        <div class="report-grid">

            <div class="report-card">

                <h3>📍 Charging Stations</h3>

                <p>
                    Add, edit and manage EV charging station
                    information and operating status.
                </p>

                <br>

                <a href="manage_stations.php" class="btn">
                    Manage Stations &rarr;
                </a>

            </div>


            <div class="report-card">

                <h3>🔌 Chargers</h3>

                <p>
                    Maintain charger information, types
                    and availability status.
                </p>

                <br>

                <a href="manage_charger.php" class="btn">
                    Manage Chargers &rarr;
                </a>

            </div>


            <div class="report-card">

                <h3>🚗 Vehicle Facilities</h3>

                <p>
                    Manage petrol stations, car washes
                    and air pump locations.
                </p>

                <br>

                <a href="manage_facilities.php" class="btn">
                    Manage Facilities &rarr;
                </a>

            </div>


            <div class="report-card">

                <h3>📋 Bookings</h3>

                <p>
                    Review bookings and update their
                    current status.
                </p>

                <br>

                <a href="manage_bookings.php" class="btn">
                    Manage Bookings &rarr;
                </a>

            </div>


            <div class="report-card">

                <h3>⭐ Reviews</h3>

                <p>
                    Review submitted feedback and
                    manage review visibility.
                </p>

                <br>

                <a href="manage_reviews.php" class="btn">
                    Manage Reviews &rarr;
                </a>

            </div>


            <div class="report-card">

                <h3>📊 SQL Reports</h3>

                <p>
                    View database summaries and reports
                    generated from SQL queries.
                </p>

                <br>

                <a href="reports.php" class="btn">
                    View Reports &rarr;
                </a>

            </div>

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
