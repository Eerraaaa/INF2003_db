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
    <h2>Average Property Price by Location</h2>

    <!-- Sorting Dropdown Form -->
    <form method="post">
        <label for="price_sort">Sort By:</label>
        <select name="price_sort" id="price_sort" onchange="this.form.submit()">
            <option value="DESC" <?php if (isset($_POST['price_sort']) && $_POST['price_sort'] == 'DESC') echo 'selected'; ?>>Highest Price</option>
            <option value="ASC" <?php if (isset($_POST['price_sort']) && $_POST['price_sort'] == 'ASC') echo 'selected'; ?>>Lowest Price</option>
        </select>
    </form>

    <?php
    $sortOrder = isset($_POST['price_sort']) ? $_POST['price_sort'] : 'DESC';

    $result = $conn->query("SELECT Location.town AS 'Location', AVG(Property.resalePrice) AS 'Average Price'
                            FROM Property
                            JOIN Location ON Property.locationID = Location.locationID
                            GROUP BY Location.town
                            ORDER BY AVG(Property.resalePrice) $sortOrder");

    echo '<table class="table table-bordered">';
    echo '<tr><th>Location</th><th>Average Property Price</th></tr>';
    while ($row = $result->fetch_assoc()) {
        $formatted_price = '$' . number_format($row['Average Price'], 2);
        echo "<tr><td>{$row['Location']}</td><td>{$formatted_price}</td></tr>";
    }
    echo '</table>';
    ?>
</div>
