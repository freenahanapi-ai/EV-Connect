<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

$error = "";

$allowedStatuses = ["Visible", "Hidden"];

// Update review visibility
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reviewId = filter_input(INPUT_POST, "review_id", FILTER_VALIDATE_INT);
    $newStatus = trim($_POST["review_status"] ?? "");

    if (!$reviewId || $reviewId < 1) {
        $error = "Invalid review ID.";
    } elseif (!in_array($newStatus, $allowedStatuses, true)) {
        $error = "Invalid review status.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE reviews
             SET review_status = ?
             WHERE review_id = ?"
        );

        $stmt->bind_param("si", $newStatus, $reviewId);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: manage_reviews.php?updated=1");
            exit;
        } else {
            $error = "Unable to update review status.";
            $stmt->close();
        }
    }
}

// Retrieve reviews and their related user/station/facility
$sql = "SELECT
            r.review_id,
            u.full_name,
            s.station_name,
            f.facility_name,
            r.rating,
            r.comment,
            r.review_date,
            r.review_status
        FROM reviews r
        INNER JOIN users u
            ON r.user_id = u.user_id
        LEFT JOIN stations s
            ON r.station_id = s.station_id
        LEFT JOIN facilities f
            ON r.facility_id = f.facility_id
        ORDER BY r.review_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews</title>
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
        <a href="manage_facilities.php">Facilities</a>
        <a href="manage_bookings.php">Bookings</a>
        <a href="manage_reviews.php" class="active">Reviews</a>
        <a href="reports.php">Reports</a>
    </nav>

</header>

<div class="page-banner">

    <p class="eyebrow">EV CONNECT ADMINISTRATION</p>

    <h1>Manage Reviews</h1>
    <p>View user reviews and manage their visibility.</p>

</div>

<!-- Main Content -->
<main class="admin-container">

    <div class="admin-header">
        <h1>Review Management</h1>
        <p>Manage user reviews and control which reviews are visible on the website.</p>
    </div>

    <!-- Page Actions -->
    <div class="admin-actions">

        <a href="dashboard.php" class="btn btn-secondary">
            &larr; Back to Dashboard
        </a>

    </div>

    <?php if ($error !== ""): ?>
        <div class="alert alert-error" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET["updated"])): ?>
        <div class="alert alert-success" role="status">
            Review status updated successfully!
        </div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table class="admin-table">
        <thead>
            <tr>
                <th>Review ID</th>
                <th>User</th>
                <th>Reviewed Item</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Date</th>
                <th>Visibility</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($review = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php echo (int) $review["review_id"]; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($review["full_name"]); ?>
                        </td>

                        <td>
                            <?php
                            if (!empty($review["station_name"])) {
                                echo htmlspecialchars($review["station_name"]);
                            } elseif (!empty($review["facility_name"])) {
                                echo htmlspecialchars($review["facility_name"]);
                            } else {
                                echo "Unknown item";
                            }
                            ?>
                        </td>

                        <td>
                            <?php echo (int) $review["rating"]; ?>/5
                        </td>

                        <td>
                            <?php echo htmlspecialchars($review["comment"] ?? ""); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($review["review_date"] ?? ""); ?>
                        </td>

                        <td>
                            <span class="status status-<?php echo strtolower(htmlspecialchars($review["review_status"])); ?>">
                                <?php echo htmlspecialchars($review["review_status"]); ?>
                            </span>
                        </td>

                        <td>
                            <form method="POST" action="manage_reviews.php" class="review-action-form">

                                <input
                                    type="hidden"
                                    name="review_id"
                                    value="<?php echo (int) $review["review_id"]; ?>"
                                >

                                <select
                                    name="review_status"
                                    class="status-select status-<?php echo strtolower(htmlspecialchars($review["review_status"])); ?>"
                                    required
                                >
                                    <?php foreach ($allowedStatuses as $status): ?>
                                        <option
                                            value="<?php echo htmlspecialchars($status); ?>"
                                            <?php
                                            if ($review["review_status"] === $status) {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            <?php echo htmlspecialchars($status); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <button type="submit" class="btn btn-small">
                                    Update
                                </button>

                            </form>
                        </td>

                    </tr>
                <?php endwhile; ?>

            <?php else: ?>
                <tr>
                    <td colspan="8">No reviews found.</td>
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
