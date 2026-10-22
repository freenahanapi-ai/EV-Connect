<?php
session_start();

// Check admin login
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Generate a CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

require_once "db.php";

$error = "";
$success = "";

// Delete a facility
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_facility"])
) {
    $facilityId = filter_input(
        INPUT_POST,
        "facility_id",
        FILTER_VALIDATE_INT
    );

    $submittedToken = $_POST["csrf_token"] ?? "";

    // Validate CSRF token
    if (
        !is_string($submittedToken) ||
        !hash_equals($_SESSION["csrf_token"], $submittedToken)
    ) {
        die("Invalid security token. Please refresh the page and try again.");
    }

    // Validate facility ID
    if (!$facilityId || $facilityId < 1) {
        $error = "Invalid facility ID.";
    } else {
        try {
            $stmt = $conn->prepare(
                "DELETE FROM facilities WHERE facility_id = ?"
            );

            $stmt->bind_param("i", $facilityId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $stmt->close();

                header("Location: manage_facilities.php?deleted=1");
                exit;
            } else {
                $stmt->close();
                $error = "Facility not found or already deleted.";
            }
        } catch (mysqli_sql_exception $e) {
            $error = "Unable to delete this facility because related records may depend on it.";
        }
    }
}

// Show success message after deletion
if (isset($_GET["deleted"]) && $_GET["deleted"] === "1") {
    $success = "Facility deleted successfully.";
}

// Retrieve all facilities
$result = $conn->query(
    "SELECT * FROM facilities ORDER BY facility_id ASC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Facilities | EV Facility Finder</title>
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

    <h1>Manage Vehicle Facilities</h1>
    <p>View, update, and manage vehicle facility records.</p>

</div>

<!-- Main Content -->
<main class="admin-container">

    <div class="admin-header">
        <h1>Facility Records</h1>
        <p>Manage the facilities available to vehicle owners.</p>
    </div>

    <!-- Page Actions -->
    <div class="admin-actions">

        <a href="dashboard.php" class="btn btn-secondary">
            &larr; Back to Dashboard
        </a>

        <a href="add_facility.php" class="btn">
            + Add New Facilities
        </a>

    </div>

    <!-- Success Message -->
    <?php if ($success !== ""): ?>
        <div class="alert alert-success" role="status">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if ($error !== ""): ?>
        <div class="alert alert-error" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Facilities Table -->
    <div class="table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Facility Name</th>
                    <th>Type</th>
                    <th>Address</th>
                    <th>District</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>

                    <?php while ($facility = $result->fetch_assoc()): ?>

                        <?php
                        $facilityStatus = $facility["status"];
                        $statusClass = "status-pending";

                        if (
                            in_array(
                                strtolower($facilityStatus),
                                ["available", "open", "active"]
                            )
                        ) {
                            $statusClass = "status-available";
                        } elseif (
                            in_array(
                                strtolower($facilityStatus),
                                ["unavailable", "closed", "inactive"]
                            )
                        ) {
                            $statusClass = "status-closed";
                        }
                        ?>

                        <tr>
                            <td>
                                <?php echo (int) $facility["facility_id"]; ?>
                            </td>

                            <td>
                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $facility["facility_name"]
                                    );
                                    ?>
                                </strong>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $facility["facility_type"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $facility["address"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $facility["districts"]
                                );
                                ?>
                            </td>

                            <td>
                                <span class="status <?php echo $statusClass; ?>">
                                    <?php
                                    echo htmlspecialchars($facilityStatus);
                                    ?>
                                </span>
                            </td>

                            <td>
                                <div class="action-buttons">

                                    <a
                                        href="edit_facility.php?id=<?php echo (int) $facility["facility_id"]; ?>"
                                        class="btn btn-secondary btn-small"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        method="POST"
                                        action="manage_facilities.php"
                                        onsubmit="return confirm('Are you sure you want to delete this facility? This action cannot be undone.');"
                                    >
                                        <input
                                            type="hidden"
                                            name="facility_id"
                                            value="<?php echo (int) $facility["facility_id"]; ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="delete_facility"
                                            value="1"
                                            class="btn btn-danger btn-small"
                                        >
                                            Delete
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="7" class="empty-state">
                            <h3>No Facilities Found</h3>
                            <p>There are currently no facility records in the database.</p>

                            <a href="add_facility.php" class="btn">
                                + Add Your First Facility
                            </a>
                        </td>
                    </tr>

                <?php endif; ?>
            </tbody>
        </table>
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