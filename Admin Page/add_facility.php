<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $facilityName = trim($_POST["facility_name"] ?? "");
    $facilityType = $_POST["facility_type"] ?? "";
    $address = trim($_POST["address"] ?? "");
    $districts = $_POST["districts"] ?? "";
    $status = $_POST["status"] ?? "";

    $allowedTypes = ["Car Wash", "Air Pump", "Petrol Station"];
    $alloweddistricts = [
        "Brunei-Muara",
        "Tutong",
        "Belait",
        "Temburong"
    ];
    $allowedStatuses = ["Open", "Closed", "Temporarily Unavailable"];

    if (
        $facilityName === "" ||
        $address === "" ||
        !in_array($facilityType, $allowedTypes, true) ||
        !in_array($districts, $alloweddistricts, true) ||
        !in_array($status, $allowedStatuses, true)
    ) {
        $error = "Please complete all fields and select valid options.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO facilities
            (facility_name, facility_type, address, districts, status)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssss",
            $facilityName,
            $facilityType,
            $address,
            $districts,
            $status
        );

        if ($stmt->execute()) {
            header("Location: manage_facilities.php");
            exit;
        } else {
            $error = "Unable to add the facility. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Vehicle Facility</title>
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
        <a href="manage_facilities.php" class="active">Facilities</a>
        <a href="manage_bookings.php">Bookings</a>
        <a href="manage_reviews.php">Reviews</a>
        <a href="reports.php">Reports</a>
    </nav>

</header>

<!-- Page Banner -->
<div class="page-banner">

    <p class="eyebrow">EV CONNECT ADMINISTRATION</p>

    <h1>Add Vehicle Facility</h1>

    <p>Add a new vehicle-related facility to the EV Connect system.</p>

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

        <a href="manage_facilities.php" class="btn btn-secondary">
            &larr; Back to Facilities
        </a>

    </div>

        <!-- Error Message -->
        <?php if ($error !== ""): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Facility Form -->
        <div class="form-card">
            <h2>Facility Details</h2>

            <p class="div-description">
                Complete all fields to register a new vehicle facility.
            </p>

            <form method="POST" action="add_facility.php" class="admin-form">

                <!-- Facility Name -->
                <div class="form-group">
                    <label for="facility_name">Facility Name</label>

                    <input
                        type="text"
                        id="facility_name"
                        name="facility_name"
                        maxlength="255"
                        value="<?php echo htmlspecialchars($_POST['facility_name'] ?? ''); ?>"
                        placeholder="Enter facility name"
                        required
                    >
                </div>

                <!-- Facility Type -->
                <div class="form-group">
                    <label for="facility_type">Facility Type</label>

                    <select id="facility_type" name="facility_type" required>
                        <option value="">-- Select Facility Type --</option>

                        <?php
                        $facilityTypes = [
                            "Car Wash",
                            "Air Pump",
                            "Petrol Station"
                        ];

                        foreach ($facilityTypes as $type):
                        ?>
                            <option
                                value="<?php echo htmlspecialchars($type); ?>"
                                <?php echo (($_POST['facility_type'] ?? '') === $type) ? 'selected' : ''; ?>
                            >
                                <?php echo htmlspecialchars($type); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                        placeholder="Enter facility address"
                        required
                    >
                </div>

                <!-- districts -->
                <div class="form-group">
                    <label for="districts">districts</label>

                    <select id="districts" name="districts" required>
                        <option value="">-- Select districts --</option>

                        <?php
                        $districts = [
                            "Brunei-Muara",
                            "Tutong",
                            "Belait",
                            "Temburong"
                        ];

                        foreach ($districts as $districtsOption):
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

                <!-- Status -->
                <div class="form-group">
                    <label for="status">Facility Status</label>

                    <select id="status" name="status" required>
                        <option value="">-- Select Status --</option>

                        <?php
                        $statuses = [
                            "Open",
                            "Closed",
                            "Temporarily Unavailable"
                        ];

                        foreach ($statuses as $statusOption):
                        ?>
                            <option
                                value="<?php echo htmlspecialchars($statusOption); ?>"
                                <?php echo (($_POST['status'] ?? '') === $statusOption) ? 'selected' : ''; ?>
                            >
                                <?php echo htmlspecialchars($statusOption); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Form Buttons -->
                <div class="form-actions">
                    <a href="manage_facilities.php" class="btn btn-secondary">
                        Cancel
                    </a>

                    <button type="submit" class="btn">
                        Add Facility
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