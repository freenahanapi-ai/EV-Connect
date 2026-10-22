<?php
require_once "db.php";

$sql = "SELECT * FROM stations";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EV Charging & Vehicle Facilities Finder</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

    <h1>EV Charging & Vehicle Facilities Finder</h1>
    <h2>Charging Stations</h2>

    <?php
    if ($result && $result->num_rows > 0) {
        while ($station = $result->fetch_assoc()) {
            echo "<h3>" . htmlspecialchars($station['station_name']) . "</h3>";
            echo "<p>Address: " . htmlspecialchars($station['address']) . "</p>";
            echo "<p>District: " . htmlspecialchars($station['districts']) . "</p>";
            echo "<p>Status: " . htmlspecialchars($station['station_status']) . "</p>";
            echo "<hr>";
        }
    } else {
        echo "<p>No charging stations found.</p>";
    }
    ?>

</body>
</html>