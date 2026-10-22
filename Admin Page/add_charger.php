<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

$error = "";

$stationResult = $conn->query(
    "SELECT station_id, station_name
     FROM stations
     ORDER BY station_name ASC"
);

$allowedTypes = ["AC", "DC", "Type 2", "CCS", "CHAdeMO"];
$allowedStatuses = ["Available", "In Use", "Out of Service"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stationId = filter_input(INPUT_POST, "station_id", FILTER_VALIDATE_INT);
    $chargerType = trim($_POST["charger_type"] ?? "");
    $status = trim($_POST["status"] ?? "");

    if (!$stationId || $chargerType === "" || $status === "") {
        $error = "Please complete all fields.";
    } elseif (!in_array($chargerType, $allowedTypes, true)) {
        $error = "Please select a valid charger type.";
    } elseif (!in_array($status, $allowedStatuses, true)) {
        $error = "Please select a valid charger status.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO chargers (station_id, charger_type, status)
             VALUES (?, ?, ?)"
        );

        $stmt->bind_param("iss", $stationId, $chargerType, $status);

        if ($stmt->execute()) {
            header("Location: manage_charger.php");
            exit;
        } else {
            $error = "Unable to add charger. Please try again.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Charger | EV Connect</title>
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
        <a href="manage_charger.php" class="active">Chargers</a>
        <a href="manage_facilities.php">Facilities</a>
        <a href="manage_bookings.php">Bookings</a>
        <a href="manage_reviews.php">Reviews</a>
        <a href="reports.php">Reports</a>
    </nav>

</header>

<!-- Page Banner -->
<div class="page-banner">

    <p class="eyebrow">EV CONNECT ADMINISTRATION</p>

    <h1>Add New Charger</h1>

    <p>
        Register a new EV charger at one of your charging stations.
    </p>

</div>

<!-- Main Content -->
<main class="admin-container">

    <!-- Page Heading -->
    <div class="admin-header">

        <h1>Charger Information</h1>

        <p>
            Complete the details below to add a charger to the database.
        </p>

    </div>

    <!-- Back Button -->
    <div class="admin-actions">

        <a href="manage_charger.php" class="btn btn-secondary">
            &larr; Back to Manage Chargers
        </a>

    </div>

    <!-- Error Message -->
    <?php if ($error !== ""): ?>

        <div class="alert alert-error" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>

    <!-- Charger Form -->
    <div class="form-card">

        <h2>Charger Details</h2>

        <p class="div-description">
            Enter the station, charger type and current status.
        </p>

        <?php if ($stationResult && $stationResult->num_rows > 0): ?>

            <form method="POST" action="" class="admin-form">

                <!-- Charging Station -->
                <div class="form-group">

                    <label for="station_id">
                        Charging Station
                    </label>

                    <select name="station_id" id="station_id" required>

                        <option value="">
                            -- Select a Station --
                        </option>

                        <?php while ($station = $stationResult->fetch_assoc()): ?>

                            <option
                                value="<?php echo (int) $station["station_id"]; ?>"
                                <?php
                                if (
                                    isset($_POST["station_id"]) &&
                                    (string) $_POST["station_id"] ===
                                    (string) $station["station_id"]
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >
                                <?php
                                echo htmlspecialchars(
                                    $station["station_name"]
                                );
                                ?>
                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <!-- Charger Type -->
                <div class="form-group">

                    <label for="charger_type">
                        Charger Type
                    </label>

                    <select name="charger_type" id="charger_type" required>

                        <option value="">
                            -- Select Charger Type --
                        </option>

                        <?php foreach ($allowedTypes as $type): ?>

                            <option
                                value="<?php echo htmlspecialchars($type); ?>"
                                <?php
                                if (($_POST["charger_type"] ?? "") === $type) {
                                    echo "selected";
                                }
                                ?>
                            >
                                <?php echo htmlspecialchars($type); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- Charger Status -->
                <div class="form-group">

                    <label for="status">
                        Charger Status
                    </label>

                    <select name="status" id="status" required>

                        <option value="">
                            -- Select Status --
                        </option>

                        <?php foreach ($allowedStatuses as $chargerStatus): ?>

                            <option
                                value="<?php echo htmlspecialchars($chargerStatus); ?>"
                                <?php
                                if (
                                    ($_POST["status"] ?? "") === $chargerStatus
                                ) {
                                    echo "selected";
                                }
                                ?>
                            >
                                <?php echo htmlspecialchars($chargerStatus); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <!-- Form Buttons -->
                <div class="form-actions">

                    <a
                        href="manage_charger.php"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                    <button type="submit" class="btn">
                        + Add Charger
                    </button>

                </div>

            </form>

        <?php else: ?>

            <div class="empty-state">

                <h3>No Charging Stations Found</h3>

                <p>
                    You need to add a charging station before registering
                    a charger.
                </p>

                <a href="add_station.php" class="btn">
                    + Add New Station
                </a>

            </div>

        <?php endif; ?>

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
        <a href="manage_facilities.php">Facilities</a>
        <a href="reports.php">Reports</a>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <p class="copyright">
        &copy; 2026 EV Connect. All rights reserved.
    </p>

</footer>

</body>
</html>