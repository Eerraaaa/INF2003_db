<?php
include 'header.php';
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

include 'lib/connection.php';
?>

<div class="container" style="padding-top: 150px;">
    <h2>Properties by Location</h2>

       <!-- Link to go back to report.php -->
       <a href="report.php" class="btn btn-secondary mb-3">Back to Reports</a>


    <!-- Sorting Dropdown Form -->
    <form method="post">
        <label for="property_sort">Sort By:</label>
        <select name="property_sort" id="property_sort" onchange="this.form.submit()">
            <option value="DESC" <?php if (isset($_POST['property_sort']) && $_POST['property_sort'] == 'DESC') echo 'selected'; ?>>Highest Number of Properties</option>
            <option value="ASC" <?php if (isset($_POST['property_sort']) && $_POST['property_sort'] == 'ASC') echo 'selected'; ?>>Lowest Number of Properties</option>
        </select>
    </form>

    <?php
    $sortOrder = isset($_POST['property_sort']) ? $_POST['property_sort'] : 'DESC';

    $result = $conn->query("SELECT Location.town AS 'Location', COUNT(Property.propertyID) AS 'Total Properties'
                            FROM Property
                            JOIN Location ON Property.locationID = Location.locationID
                            GROUP BY Location.town
                            ORDER BY COUNT(Property.propertyID) $sortOrder");

    echo '<table class="table table-bordered">';
    echo '<tr><th>Location</th><th>Total Properties</th></tr>';
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['Location']}</td><td>{$row['Total Properties']}</td></tr>";
    }
    echo '</table>';
    ?>
</div>
