<?php
include 'header.php';
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

include 'lib/connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>Report Links</title>
</head>
<body>
    <div class="container" style="padding-top: 150px;">
        <h2>Admin Reports</h2>
        <ul class="list-group">
            <li class="list-group-item"><a href="properties_by_location.php">Properties by Location</a></li>
            <li class="list-group-item"><a href="average_property_price.php">Average Property Price by Location</a></li>
            <li class="list-group-item"><a href="median_property_prices.php">Median Property Prices by Location</a></li>
            <li class="list-group-item"><a href="top_agents_by_reviews.php">Top Agents by Reviews</a></li>
        </ul>
    </div>
</body>
</html>
