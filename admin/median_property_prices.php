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
    <h2>Median Property Prices by Location</h2>

    <!-- Sorting Dropdown Form -->
    <form method="post">
        <label for="median_sort">Sort By:</label>
        <select name="median_sort" id="median_sort" onchange="this.form.submit()">
            <option value="DESC" <?php if (isset($_POST['median_sort']) && $_POST['median_sort'] == 'DESC') echo 'selected'; ?>>Highest Price</option>
            <option value="ASC" <?php if (isset($_POST['median_sort']) && $_POST['median_sort'] == 'ASC') echo 'selected'; ?>>Lowest Price</option>
        </select>
    </form>

    <?php
    $sortOrder = isset($_POST['median_sort']) ? $_POST['median_sort'] : 'DESC';

    $result = $conn->query("SELECT Location.town AS 'Location', AVG(resalePrice) AS 'Median Price'
                            FROM Property
                            JOIN Location ON Property.locationID = Location.locationID
                            GROUP BY Location.town
                            HAVING COUNT(resalePrice) % 2 = 0
                            ORDER BY AVG(resalePrice) $sortOrder");

    echo '<table class="table table-bordered">';
    echo '<tr><th>Location</th><th>Median Price</th></tr>';
    while ($row = $result->fetch_assoc()) {
        $formatted_price = '$' . number_format($row['Median Price'], 2);
        echo "<tr><td>{$row['Location']}</td><td>{$formatted_price}</td></tr>";
    }
    echo '</table>';
    ?>
</div>
