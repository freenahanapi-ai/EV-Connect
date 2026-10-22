<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

$error = "";

// Delete station using POST
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_station"])
) {
    $stationId = filter_input(
        INPUT_POST,
        "station_id",
        FILTER_VALIDATE_INT
    );

    if ($stationId && $stationId > 0) {
        try {
            $stmt = $conn->prepare(
                "DELETE FROM stations WHERE station_id = ?"
            );

            $stmt->bind_param("i", $stationId);
            $stmt->execute();
            $stmt->close();

            header("Location: manage_stations.php?deleted=1");
            exit;

        } catch (mysqli_sql_exception $e) {
            $error = "Unable to delete this station because it may have related chargers or bookings.";
        }
    } else {
        $error = "Invalid station ID.";
    }
}

// Retrieve all stations
$result = $conn->query(
    "SELECT * FROM stations ORDER BY station_id ASC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Stations | EV Connect</title>

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

    <h1>Manage Charging Stations</h1>

    <p>
        View, add, update and manage your EV charging station records.
    </p>

</div>

<!-- Main Content -->
<main class="admin-container">

    <!-- Page Heading and Actions -->
    <div class="admin-header">
        <h1>Charging Station Records</h1>
        <p>Manage station names, locations, opening hours and statuses.</p>
    </div>

    <div class="admin-actions">

        <a href="dashboard.php" class="btn btn-secondary">
            &larr; Back to Dashboard
        </a>

        <a href="add_station.php" class="btn">
            + Add New Station
        </a>

    </div>

    <!-- Success Message -->
    <?php if (isset($_GET["deleted"])): ?>
        <div class="alert alert-success" role="status">
            Station deleted successfully.
        </div>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if ($error !== ""): ?>
        <div class="alert alert-error" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Station Table -->
    <div class="admin-div">

        <h2>All Charging Stations</h2>

        <p class="div-description">
            A list of charging stations currently stored in the database.
        </p>

        <div class="table-wrapper">

            <table class="admin-table">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Station Name</th>
                        <th>Address</th>
                        <th>District</th>
                        <th>Opening Hours</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($result->num_rows > 0): ?>

                        <?php while ($station = $result->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        (string) $station["station_id"]
                                    );
                                    ?>
                                </td>

                                <td>
                                    <strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $station["station_name"]
                                        );
                                        ?>
                                    </strong>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $station["address"]
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $station["districts"]
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $station["opening_hours"]
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    $status = $station["station_status"];

                                    $statusClass = "status-pending";

                                    if (strcasecmp($status, "Available") === 0 ||
                                        strcasecmp($status, "Open") === 0) {
                                        $statusClass = "status-available";
                                    } elseif (
                                        strcasecmp($status, "Unavailable") === 0 ||
                                        strcasecmp($status, "Closed") === 0
                                    ) {
                                        $statusClass = "status-closed";
                                    }
                                    ?>

                                    <span class="status <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="action-buttons">

                                        <a
                                            href="edit_station.php?id=<?php echo (int) $station["station_id"]; ?>"
                                            class="btn btn-secondary btn-small"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="manage_stations.php"
                                            onsubmit="return confirm('Are you sure you want to delete this station? This action cannot be undone.');"
                                        >
                                            <input
                                                type="hidden"
                                                name="station_id"
                                                value="<?php echo (int) $station["station_id"]; ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="delete_station"
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
                            <td colspan="7">
                                <div class="empty-state">
                                    <h3>No Charging Stations Found</h3>

                                    <p>
                                        Add your first charging station to get started.
                                    </p>

                                    <a href="add_station.php" class="btn">
                                        + Add New Station
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
