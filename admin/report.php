<?php
include 'header.php';
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: unauthorized.php"); // Redirect to unauthorized access page
    exit();
}

// include "../inc/headproduct.inc.php";
include 'lib/connection.php';

?>
<div class="container">
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/report.css">
        <title>Report</title>
        <!-- Bootstrap library -->
        <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
        <!-- Stylesheet file -->
        <link rel="stylesheet" href="assets/css/style.css">
        <!-- Font Awesome for icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    </head>

    <body>
        <!-- Export buttons -->
        <div style="padding-top: 150px;">


        <?php

        // Properties by Location
        $result = $conn->query("SELECT Location.town AS 'Location', COUNT(Property.propertyID) AS 'Total Properties'
                  FROM Property
                  JOIN Location ON Property.locationID = Location.locationID
                  GROUP BY Location.town
                  ORDER BY Location.town ASC");
        echo '<br><h4>Properties by Location</h4>';
        echo '<table>';
        echo '<tr><th>Location</th><th>Total Properties</th></tr>';
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['Location']}</td><td>{$row['Total Properties']}</td></tr>";
        }
        echo '</table>';


        // Average Property Price by Location
        $result = $conn->query("SELECT Location.town AS 'Location', AVG(Property.resalePrice) AS 'Average Price'
                FROM Property
                JOIN Location ON Property.locationID = Location.locationID
                GROUP BY Location.town
                ORDER BY AVG(Property.resalePrice) DESC");

        echo '<br><h4>Average Property Price by Location</h4>';
        echo '<table>';
        echo '<tr><th>Location</th><th>Average Property Price</th></tr>'; // Fixed the header order
        while ($row = $result->fetch_assoc()) {
            // Format the average property price with commas and two decimal places
            $formatted_price = '$' . number_format($row['Average Price'], 2);

            // Display the location and the formatted price
            echo "<tr><td>{$row['Location']}</td><td>{$formatted_price}</td></tr>";
        }
        echo '</table>';

    
        // Median Property Prices by Location
        $result = $conn->query("SELECT Location.town AS 'Location', AVG(resalePrice) AS 'Median Price'
        FROM Property
        JOIN Location ON Property.locationID = Location.locationID
        GROUP BY Location.town
        HAVING COUNT(resalePrice) % 2 = 0;");

        echo '<br><h4>Median Property Prices by Location</h4>';
        echo '<table>';
        echo '<tr><th>Location</th><th>Median Price</th></tr>';
        while ($row = $result->fetch_assoc()) {
            $formatted_price = '$' . number_format($row['Median Price'], 2);
            echo "<tr><td>{$row['Location']}</td><td>{$formatted_price}</td></tr>";
        }
        echo '</table>';


        // Top Agents by Reviews
        $result = $conn->query("SELECT CONCAT(Users.fname, ' ', Users.lname) AS 'Agent Name', 
                ROUND(AVG(agentReview.rating), 2) AS 'Average Rating'
                FROM agentReview
                JOIN Agent ON agentReview.agentID = Agent.agentID
                JOIN Users ON Agent.userID = Users.userID
                GROUP BY agentReview.agentID
                ORDER BY AVG(agentReview.rating) DESC");

        echo '<br><h4>Top Agents by Reviews</h4>';
        echo '<table>';
        echo '<tr><th>Agent Name</th><th>Average Rating</th></tr>';
        while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['Agent Name']}</td><td>{$row['Average Rating']}</td></tr>";
        }
        echo '</table>';   
        
        // property sales 
        // case 'property_sales_by_day':
        //     $query = "SELECT DATE(Property.transactionDate) AS 'Date', COUNT(Property.propertyID) AS 'Total Sales'
        //               FROM Property
        //               GROUP BY DATE(Property.transactionDate)
        //               ORDER BY DATE(Property.transactionDate) DESC";
        //     $filename = "property_sales_by_day_" . date('Y-m-d') . ".csv";
        //     $headers = ['Date', 'Total Sales'];
        //     exportReport($conn, $query, $filename, $headers);
        //     break;

        ?>

</div>
</body>

</html>
</div>