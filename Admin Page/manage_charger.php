<?php

session_start();

// Check admin login
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

// Initialise messages
$deleteError = "";

// Delete charger using POST
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["delete_charger"])
) {
    $chargerId = filter_input(
        INPUT_POST,
        "charger_id",
        FILTER_VALIDATE_INT
    );

    if (!$chargerId || $chargerId < 1) {
        $deleteError = "Invalid charger ID.";
    } else {
        try {
            $stmt = $conn->prepare(
                "DELETE FROM chargers WHERE charger_id = ?"
            );

            $stmt->bind_param("i", $chargerId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $stmt->close();

                header("Location: manage_charger.php?deleted=1");
                exit;
            } else {
                $deleteError = "Charger not found or already deleted.";
                $stmt->close();
            }

        } catch (mysqli_sql_exception $e) {
            $deleteError =
                "Unable to delete this charger because it may be linked to existing bookings.";
        }
    }
}

// Retrieve chargers and their station names
$sql = "
    SELECT
        chargers.charger_id,
        chargers.station_id,
        stations.station_name,
        chargers.charger_type,
        chargers.status
    FROM chargers
    INNER JOIN stations
        ON chargers.station_id = stations.station_id
    ORDER BY chargers.charger_id ASC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Chargers | EV Connect</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<!-- Navigation Bar -->
<header class="navbar">

    <a href="dashboard.php" class="logo"> 
        <span class="logo-icon">⚡</span> 
        <span> EV Connect 
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
    <h1>Manage Chargers</h1>
    <p>View and manage EV chargers registered in the system.</p>
</div>

<!-- Main Content -->
<main class="admin-container">

    <!-- Page Heading -->
    <div class="admin-header">
        <h1>Charger Management</h1>
        <p>View charger details and manage their records.</p>
    </div>

    <div class="admin-actions">

        <a href="dashboard.php" class="btn btn-secondary">
            &larr; Back to Dashboard
        </a>

        <a href="add_charger.php" class="btn">
            + Add New Charger
        </a>

    </div>

    <!-- Success Message -->
    <?php if (isset($_GET["deleted"]) && $_GET["deleted"] === "1"): ?>
        <div class="alert alert-success" role="status">
            Charger deleted successfully!
        </div>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if ($deleteError !== ""): ?>
        <div class="alert alert-error" role="alert">
            <?php echo htmlspecialchars($deleteError); ?>
        </div>
    <?php endif; ?>

    <!-- Charger Table -->
    <div class="admin-div">

        <div class="table-wrapper">

            <table class="admin-table">

                <thead>
                    <tr>
                        <th>Charger ID</th>
                        <th>Station Name</th>
                        <th>Charger Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($result && $result->num_rows > 0): ?>

                        <?php while ($charger = $result->fetch_assoc()): ?>

                            <tr>
                                <td>
                                    <?php echo (int) $charger["charger_id"]; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $charger["station_name"]
                                    ); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars(
                                        $charger["charger_type"]
                                    ); ?>
                                </td>

                                <td>
                                    <?php
                                    $status = $charger["status"];
                                    $statusClass = "";

                                    if ($status === "Available") {
                                        $statusClass = "status-available";
                                    } elseif ($status === "In Use") {
                                        $statusClass = "status-in-use";
                                    } elseif ($status === "Out of Service") {
                                        $statusClass = "status-closed";
                                    }
                                    ?>

                                    <span class="status <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </td>

                                <td>
                                    <div class="action-buttons">

                                        <!-- Edit Button -->
                                        <a
                                            href="edit_charger.php?id=<?php
                                                echo (int) $charger["charger_id"];
                                            ?>"
                                            class="btn btn-secondary btn-small"
                                        >
                                            Edit
                                        </a>

                                        <!-- Delete Button -->
                                        <form
                                            method="POST"
                                            action="manage_charger.php"
                                            onsubmit="return confirm('Are you sure you want to delete this charger? This action cannot be undone.');"
                                        >

                                            <input
                                                type="hidden"
                                                name="charger_id"
                                                value="<?php
                                                    echo (int) $charger["charger_id"];
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                name="delete_charger"
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
                            <td colspan="5" class="empty-state">
                                No chargers found.
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

<?php
$conn->close();
?>