<?php
// filepath: c:\Users\TUF\Desktop\SEM 3\Database\XAMPP\htdocs\ev-facility-finder\edit_facility.php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

$error = "";
$facilityId = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$facilityId || $facilityId < 1) {
    header("Location: manage_facilities.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT facility_name, facility_type, address, districts, status
     FROM facilities
     WHERE facility_id = ?"
);
$stmt->bind_param("i", $facilityId);
$stmt->execute();

$result = $stmt->get_result();
$facility = $result->fetch_assoc();
$stmt->close();

if (!$facility) {
    header("Location: manage_facilities.php");
    exit;
}

$facilityTypeOptions = ["Car Wash", "Air Pump", "Petrol Station"];
$districtsOptions = ["Brunei-Muara", "Tutong", "Belait", "Temburong"];
$statusOptions = ["Open", "Closed", "Temporarily Unavailable"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $facilityName = trim($_POST["facility_name"] ?? "");
    $facilityType = trim($_POST["facility_type"] ?? "");
    $address = trim($_POST["address"] ?? "");
    $districst = trim($_POST["districts"] ?? "");
    $status = trim($_POST["status"] ?? "");

    if (
        $facilityName === "" ||
        $address === "" ||
        !in_array($facilityType, $facilityTypeOptions, true) ||
        !in_array($districts, $districtsOptions, true) ||
        !in_array($status, $statusOptions, true)
    ) {
        $error = "Please complete all fields and select valid options.";
    } else {
        $updateStmt = $conn->prepare(
            "UPDATE facilities
             SET facility_name = ?,
                 facility_type = ?,
                 address = ?,
                 districts = ?,
                 status = ?
             WHERE facility_id = ?"
        );

        $updateStmt->bind_param(
            "sssssi",
            $facilityName,
            $facilityType,
            $address,
            $districts,
            $status,
            $facilityId
        );

        if ($updateStmt->execute()) {
            $updateStmt->close();
            header("Location: manage_facilities.php");
            exit;
        }

        $error = "Unable to update the facility. Please try again.";
        $updateStmt->close();
    }

    // Keep submitted values in the form if validation fails.
    $facility = [
        "facility_name" => $facilityName,
        "facility_type" => $facilityType,
        "address" => $address,
        "districts" => $districts,
        "status" => $status
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Facility | EV Connect</title>
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
        <a href="manage_charger.php">Chargers</a>
        <a href="manage_facilities.php" class="active">Facilities</a>
        <a href="manage_bookings.php">Bookings</a>
        <a href="manage_reviews.php">Reviews</a>
        <a href="reports.php">Reports</a>
    </nav>
</header>

<div class="page-banner">
    <p class="eyebrow">EV CONNECT ADMINISTRATION</p>
    <h1>Edit Facility</h1>
    <p>Update the facility information and operating status.</p>
</div>

<main class="admin-container">
    <div class="admin-header">
        <h2>Facility Information</h2>
        <p>Make changes to the facility details below.</p>
    </div>

    <div class="admin-actions">
        <a href="manage_facilities.php" class="btn btn-secondary">
            &larr; Back to Manage Facilities
        </a>
    </div>

    <?php if ($error !== ""): ?>
        <div class="alert alert-error" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <h2>Edit Facility Details</h2>
        <p class="div-description">
            Update the fields below and save your changes.
        </p>

        <form
            method="POST"
            action="edit_facility.php?id=<?php echo (int) $facilityId; ?>"
            class="admin-form"
        >
            <div class="form-group">
                <label for="facility_name">Facility Name</label>
                <input
                    type="text"
                    id="facility_name"
                    name="facility_name"
                    maxlength="255"
                    value="<?php echo htmlspecialchars($facility["facility_name"]); ?>"
                    placeholder="Enter facility name"
                    required
                >
            </div>

            <div class="form-group">
                <label for="facility_type">Facility Type</label>
                <select id="facility_type" name="facility_type" required>
                    <option value="">-- Select Facility Type --</option>
                    <?php foreach ($facilityTypeOptions as $type): ?>
                        <option
                            value="<?php echo htmlspecialchars($type); ?>"
                            <?php echo ($facility["facility_type"] === $type) ? "selected" : ""; ?>
                        >
                            <?php echo htmlspecialchars($type); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <input
                    type="text"
                    id="address"
                    name="address"
                    maxlength="255"
                    value="<?php echo htmlspecialchars($facility["address"]); ?>"
                    placeholder="Enter facility address"
                    required
                >
            </div>

            <div class="form-group">
                <label for="districts">Districts</label>
                <select id="districts" name="districts" required>
                    <option value="">-- Select Districts --</option>
                    <?php foreach ($districtsOptions as $districtsOption): ?>
                        <option
                            value="<?php echo htmlspecialchars($districtsOption); ?>"
                            <?php echo ($facility["districts"] === $districtsOption) ? "selected" : ""; ?>
                        >
                            <?php echo htmlspecialchars($districtsOption); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Facility Status</label>
                <select id="status" name="status" required>
                    <option value="">-- Select Status --</option>
                    <?php foreach ($statusOptions as $statusOption): ?>
                        <option
                            value="<?php echo htmlspecialchars($statusOption); ?>"
                            <?php echo ($facility["status"] === $statusOption) ? "selected" : ""; ?>
                        >
                            <?php echo htmlspecialchars($statusOption); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <a href="manage_facilities.php" class="btn btn-secondary">
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