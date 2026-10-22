<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $stationName = trim($_POST["station_name"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $districts = trim($_POST["districts"] ?? "");
    $openingHours = trim($_POST["opening_hours"] ?? "");
    $status = $_POST["station_status"] ?? "";

    $allowedStatuses = ["Open", "Closed", "Temporarily Unavailable"];

    if (
        $stationName === "" ||
        $address === "" ||
        $districts === "" ||
        $openingHours === "" ||
        !in_array($status, $allowedStatuses, true)
    ) {
        $error = "Please complete every field and select a valid status.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO stations
            (station_name, address, districts, opening_hours, station_status)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssss",
            $stationName,
            $address,
            $districts,
            $openingHours,
            $status
        );

        if ($stmt->execute()) {
            header("Location: manage_stations.php");
            exit;
        } else {
            $error = "Unable to add the station. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Charging Station</title>
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

    <h1>Add Charging Station</h1>
    <p>Add a new charging station to the EV Connect system.</p>
    
</div>

<!-- Main Content -->
<main class="admin-container">

    <!-- Page Heading -->
    <div class="admin-header">

        <h1>Station Information</h1>
        <p>Enter the details of the charging station below.</p>    

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

        <!-- Station Form -->
        <div class="form-card">
            <h2>Station Details</h2>

            <p class="div-description">
                Complete all fields to register a new charging station.
            </p>

            <form method="POST" action="add_station.php" class="admin-form">

                <!-- Station Name -->
                <div class="form-group">
                    <label for="station_name">Station Name</label>

                    <input
                        type="text"
                        id="station_name"
                        name="station_name"
                        maxlength="255"
                        value="<?php echo htmlspecialchars($_POST['station_name'] ?? ''); ?>"
                        placeholder="Enter station name"
                        required
                    >
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="address">Address</label>

                    <input
                        type="text"
                        id="address"
                        name="address"
                        maxlength="255"
                        value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>"
                        placeholder="Enter station address"
                        required
                    >
                </div>

                <!-- districts -->
                <div class="form-group">
                    <label for="districts">districts</label>

                    <select id="districts" name="districts" required>
                        <option value="">-- Select districts --</option>

                        <?php
                        $districtsOptions = [
                            "Brunei-Muara",
                            "Tutong",
                            "Belait",
                            "Temburong"
                        ];

                        foreach ($districtsOptions as $districtsOption):
                        ?>
                            <option
                                value="<?php echo htmlspecialchars($districtsOption); ?>"
                                <?php echo (($_POST['districts'] ?? '') === $districtsOption) ? 'selected' : ''; ?>
                            >
                                <?php echo htmlspecialchars($districtsOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Opening Hours -->
                <div class="form-group">
                    <label for="opening_hours">Opening Hours</label>

                    <input
                        type="text"
                        id="opening_hours"
                        name="opening_hours"
                        maxlength="100"
                        value="<?php echo htmlspecialchars($_POST['opening_hours'] ?? ''); ?>"
                        placeholder="e.g. 8:00 AM - 10:00 PM"
                        required
                    >
                </div>

                <!-- Status -->
                <div class="form-group">
                    <label for="station_status">Station Status</label>

                    <select id="station_status" name="station_status" required>
                        <option value="">-- Select Status --</option>

                        <?php
                        $statusOptions = [
                            "Open",
                            "Closed",
                            "Temporarily Unavailable"
                        ];

                        foreach ($statusOptions as $statusOption):
                        ?>
                            <option
                                value="<?php echo htmlspecialchars($statusOption); ?>"
                                <?php echo (($_POST['station_status'] ?? '') === $statusOption) ? 'selected' : ''; ?>
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
                        Add Station
                    </button>
                </div>

            </form>
        </div>

</main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; <?php echo date("Y"); ?> EV Connect. All rights reserved.</p>
    </footer>

</body>
</html>