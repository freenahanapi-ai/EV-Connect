<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

$error = "";

$allowedTypes = ["AC", "DC", "Type 2", "CCS", "CHAdeMO"];
$allowedStatuses = ["Available", "In Use", "Out of Service"];

$chargerId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$chargerId || $chargerId < 1) {
    die("Invalid charger ID.");
}

$stmt = $conn->prepare(
    "SELECT charger_id, station_id, charger_type, status
     FROM chargers
     WHERE charger_id = ?"
);
$stmt->bind_param("i", $chargerId);
$stmt->execute();
$result = $stmt->get_result();
$charger = $result->fetch_assoc();
$stmt->close();

if (!$charger) {
    die("Charger not found.");
}

$stationResult = $conn->query(
    "SELECT station_id, station_name
     FROM stations
     ORDER BY station_name ASC"
);

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
        $checkStation = $conn->prepare(
            "SELECT station_id FROM stations WHERE station_id = ?"
        );
        $checkStation->bind_param("i", $stationId);
        $checkStation->execute();
        $stationExists = $checkStation->get_result()->num_rows > 0;
        $checkStation->close();

        if (!$stationExists) {
            $error = "Please select an existing charging station.";
        } else {
            $update = $conn->prepare(
                "UPDATE chargers
                 SET station_id = ?, charger_type = ?, status = ?
                 WHERE charger_id = ?"
            );

            $update->bind_param(
                "issi",
                $stationId,
                $chargerType,
                $status,
                $chargerId
            );

            if ($update->execute()) {
                $update->close();
                header("Location: manage_charger.php");
                exit;
            } else {
                $error = "Unable to update charger. Please try again.";
                $update->close();
            }
        }
    }

    // Keep the submitted values in the form if validation fails.
    $charger["station_id"] = $stationId ?: $charger["station_id"];
    $charger["charger_type"] = $chargerType;
    $charger["status"] = $status;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Charger | EV Connect</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

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

<div class="page-banner">
    <p class="eyebrow">EV CONNECT ADMINISTRATION</p>
    <h1>Edit Charger</h1>
    <p>Update the charger assignment, connector type, and operating status.</p>
</div>

<main class="admin-container">
    <div class="admin-header">
        <h2>Charger Information</h2>
        <p>Make changes to the charger details below.</p>
    </div>

    <div class="admin-actions">
        <a href="manage_charger.php" class="btn btn-secondary">
            &larr; Back to Manage Chargers
        </a>
    </div>

    <?php if ($error !== ""): ?>
        <div class="alert alert-error" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <h2>Edit Charger Details</h2>
        <p class="div-description">
            Update the fields below and save your changes.
        </p>

        <form
            method="POST"
            action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . (int) $chargerId); ?>"
            class="admin-form"
        >
            <div class="form-group">
                <label for="station_id">Charging Station</label>
                <select name="station_id" id="station_id" required>
                    <option value="">-- Select a Station --</option>

                    <?php while ($station = $stationResult->fetch_assoc()): ?>
                        <option
                            value="<?php echo (int) $station["station_id"]; ?>"
                            <?php
                            if ((string) $charger["station_id"] === (string) $station["station_id"]) {
                                echo "selected";
                            }
                            ?>
                        >
                            <?php echo htmlspecialchars($station["station_name"]); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="charger_type">Charger Type</label>
                <select name="charger_type" id="charger_type" required>
                    <option value="">-- Select Charger Type --</option>

                    <?php foreach ($allowedTypes as $type): ?>
                        <option
                            value="<?php echo htmlspecialchars($type); ?>"
                            <?php
                            if ($charger["charger_type"] === $type) {
                                echo "selected";
                            }
                            ?>
                        >
                            <?php echo htmlspecialchars($type); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select name="status" id="status" required>
                    <option value="">-- Select Status --</option>

                    <?php foreach ($allowedStatuses as $chargerStatus): ?>
                        <option
                            value="<?php echo htmlspecialchars($chargerStatus); ?>"
                            <?php
                            if ($charger["status"] === $chargerStatus) {
                                echo "selected";
                            }
                            ?>
                        >
                            <?php echo htmlspecialchars($chargerStatus); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <a href="manage_charger.php" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</main>

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
