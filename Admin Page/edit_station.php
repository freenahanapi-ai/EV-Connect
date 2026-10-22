<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

$error = "";
$stationId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$stationId || $stationId < 1) {
    header("Location: manage_stations.php");
    exit;
}

// Get the existing station details
$stmt = $conn->prepare(
    "SELECT station_name, address, districts, opening_hours, station_status
     FROM stations
     WHERE station_id = ?"
);
$stmt->bind_param("i", $stationId);
$stmt->execute();

$result = $stmt->get_result();
$station = $result->fetch_assoc();

if (!$station) {
    header("Location: manage_stations.php");
    exit;
}

// Update the station when the form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stationName = trim($_POST["station_name"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $districts = trim($_POST["districts"] ?? "");
    $openingHours = trim($_POST["opening_hours"] ?? "");
    $status = $_POST["station_status"] ?? "";

    $alloweddistricts = [
        "Brunei-Muara",
        "Tutong",
        "Belait",
        "Temburong"
    ];

    $alloweddistricts = [
        "Brunei-Muara",
        "Tutong",
        "Belait",
        "Temburong"
    ];

    $allowedStatuses = [
        "Open",
        "Closed",
        "Temporarily Unavailable"
    ];

    if (
        $stationName === "" ||
        $address === "" ||
        $openingHours === "" ||
        !in_array($districts, $alloweddistricts, true) ||
        !in_array($status, $allowedStatuses, true)
    ) {
        $error = "Please complete all fields and select valid options.";
    } else {
        $updateStmt = $conn->prepare(
            "UPDATE stations
             SET station_name = ?,
                 address = ?,
                 districts = ?,
                 opening_hours = ?,
                 station_status = ?
             WHERE station_id = ?"
        );

        $updateStmt->bind_param(
            "sssssi",
            $stationName,
            $address,
            $districts,
            $openingHours,
            $status,
            $stationId
        );

        if ($updateStmt->execute()) {
            header("Location: manage_stations.php");
            exit;
        } else {
            $error = "Unable to update the station. Please try again.";
        }
    }

    // Keep the submitted values in the form if validation fails
    $station = [
        "station_name" => $stationName,
        "address" => $address,
        "districts" => $districts,
        "opening_hours" => $openingHours,
        "station_status" => $status
    ];
}

$districtsOptions = [
    "Brunei-Muara",
    "Tutong",
    "Belait",
    "Temburong"
];

$statusOptions = [
    "Open",
    "Closed",
    "Temporarily Unavailable"
];
?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Charging Station | EV Connect</title>
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
    <a href="manage_stations.php" class="active">Stations</a>
    <a href="manage_charger.php">Chargers</a>
    <a href="manage_facilities.php">Facilities</a>
    <a href="manage_bookings.php">Bookings</a>
    <a href="manage_reviews.php">Reviews</a>
    <a href="reports.php">Reports</a>
</nav>

</header>

<!-- Page Banner -->

<div class="page-banner">
    <p class="eyebrow">EV CONNECT ADMINISTRATION</p>
    <h1>Edit Charging Station</h1>
    <p>Update the charging station information and operating status.</p>
</div>

<!-- Main Content -->

<main class="admin-container">

<div class="admin-header">
    <h2>Station Information</h2>
    <p>Make changes to the station details below.</p>
</div>

<!-- Back Button -->
<div class="admin-actions">
    <a href="manage_stations.php" class="btn btn-secondary">
        &larr; Back to Manage Stations
    </a>
</div>

<!-- Error Message -->
<?php if ($error !== ""): ?>
    <div class="alert alert-error" role="alert">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<!-- Edit Form -->
<div class="form-card">
    <h2>Edit Station Details</h2>

    <p class="div-description">
        Update the fields below and save your changes.
    </p>

    <form
        method="POST"
        action="edit_station.php?id=<?php echo (int) $stationId; ?>"
        class="admin-form"
    >

        <div class="form-group">
            <label for="station_name">Station Name</label>
            <input
                type="text"
                id="station_name"
                name="station_name"
                maxlength="255"
                value="<?php echo htmlspecialchars($station["station_name"]); ?>"
                placeholder="Enter station name"
                required
            >
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <input
                type="text"
                id="address"
                name="address"
                maxlength="255"
                value="<?php echo htmlspecialchars($station["address"]); ?>"
                placeholder="Enter station address"
                required
            >
        </div>

        <div class="form-group">
            <label for="districts">District</label>
            <select id="districts" name="districts" required>
                <option value="">-- Select District --</option>

                <?php foreach ($districtsOptions as $district): ?>
                    <option
                        value="<?php echo htmlspecialchars($district); ?>"
                        <?php echo ($station["districts"] === $district) ? "selected" : ""; ?>
                    >
                        <?php echo htmlspecialchars($district); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="opening_hours">Opening Hours</label>
            <input
                type="text"
                id="opening_hours"
                name="opening_hours"
                maxlength="100"
                value="<?php echo htmlspecialchars($station["opening_hours"]); ?>"
                placeholder="e.g. 8:00 AM - 10:00 PM"
                required
            >
        </div>

        <div class="form-group">
            <label for="station_status">Station Status</label>
            <select id="station_status" name="station_status" required>
                <?php foreach ($statusOptions as $statusOption): ?>
                    <option
                        value="<?php echo htmlspecialchars($statusOption); ?>"
                        <?php echo ($station["station_status"] === $statusOption) ? "selected" : ""; ?>
                    >
                        <?php echo htmlspecialchars($statusOption); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Form Buttons -->
        <div class="form-actions">
            <a href="manage_stations.php" class="btn btn-secondary">
                Cancel
            </a>

            <button type="submit" class="btn">
                Save Changes
            </button>
        </div>

    </form>
</div>

</main>

<!-- Footer -->

<footer class="footer">
    <a href="dashboard.php" class="footer-logo">⚡ EV Connect</a>
    <p>Charge. Refuel. Go Further.</p>

<div class="footer-links">
    <a href="dashboard.php">Dashboard</a>
    <a href="manage_stations.php">Stations</a>
    <a href="manage_facilities.php">Facilities</a>
    <a href="reports.php">Reports</a>
    <a href="logout.php" class="logout-link">Logout</a>
</div>

<p class="copyright">
    &copy; <?php echo date("Y"); ?> EV Connect. All rights reserved.
</p>

</footer>

</body>
</html>
