<?php
require_once __DIR__ . "/functions.php";
?>
<header class="navbar">
    <a href="index.html" class="logo">
        <span class="logo-icon">⚡</span>
        <span>EV Connect
            <small>Charge. Refuel. Go Further.</small>
        </span>
    </a>

    <nav>
        <a href="index.html">Home</a>
        <a href="stations.html">EV Stations</a>
        <a href="facilities.html">Facilities</a>

        <?php if (isset($_SESSION["user_id"])): ?>
            <a href="booking-history.php" class="active">My Bookings</a>
            <a href="logout.php" onclick="return confirm('Are you sure to logout?');">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>