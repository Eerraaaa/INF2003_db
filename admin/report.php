<?php
include 'header.php';
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("location:newlogin.php");
    exit; // Don't forget to exit after sending a header redirect.
}

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
        <div class="export-buttons">
            <!-- <a href="exportData.php?report=total_sales_per_day" class="btn btn-outline-success export-btn">
                <i class="fas fa-file-export"></i> Export Total Sales Per Day
            </a> -->
            <a href="exportData.php?report=orders_count_by_status" class="btn btn-outline-success export-btn">
                <i class="fas fa-file-export"></i> Export Orders Count by Status
            </a>
            <!-- <a href="exportData.php?report=sales_products_sold_by_day" class="btn btn-outline-success export-btn">
                <i class="fas fa-file-export"></i> Export Sales and Products Sold by Day
            </a>
            <a href="exportData.php?report=average_order_value_by_day" class="btn btn-outline-success export-btn">
                <i class="fas fa-file-export"></i> Export Average Order Value by Day
            </a> -->
        </div>

        <?php

        // Display Total Sales Per Day
        // $result = $conn->query("SELECT DATE(created_at) AS order_date, SUM(totalprice) AS total_sales FROM orders GROUP BY order_date ORDER BY order_date DESC");
        // echo '<br><h4>Total Sales Per Day</h4>';
        // echo '<table>';
        // echo '<tr><th>Date</th><th>Total Sales</th></tr>';
        // while ($row = $result->fetch_assoc()) {
        //     echo "<tr><td>{$row['order_date']}</td><td>{$row['total_sales']}</td></tr>";
        // }
        // echo '</table>';

        // Orders Count by Status
        $result = $conn->query("SELECT status, COUNT(*) AS order_count FROM orders GROUP BY status");
        echo '<br><h4>Orders Count by Status</h4>';
        echo '<table>';
        echo '<tr><th>Status</th><th>Order Count</th></tr>';
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['status']}</td><td>{$row['order_count']}</td></tr>";
        }
        echo '</table>';

        // Sales and Products Sold by Day
        // $result = $conn->query("SELECT DATE(created_at) AS order_date, SUM(totalprice) AS daily_sales, SUM(totalproduct) AS products_sold FROM orders GROUP BY order_date ORDER BY order_date DESC");
        // echo '<br><h4>Sales and Products Sold by Day</h4>';
        // echo '<table>';
        // echo '<tr><th>Date</th><th>Daily Sales</th><th>Products Sold</th></tr>';
        // while ($row = $result->fetch_assoc()) {
        //     echo "<tr><td>{$row['order_date']}</td><td>{$row['daily_sales']}</td><td>{$row['products_sold']}</td></tr>";
        // }
        // echo '</table>';

        // Average Order Value by Day
        // $result = $conn->query("SELECT DATE(created_at) AS order_date, AVG(totalprice) AS average_order_value FROM orders GROUP BY DATE(created_at)");
        // echo '<br><h4>Average Order Value by Day</h4>';
        // echo '<table>';
        // echo '<tr><th>Date</th><th>Average Order Value</th></tr>';
        // while ($row = $result->fetch_assoc()) {
        //     echo "<tr><td>{$row['order_date']}</td><td>{$row['average_order_value']}</td></tr>";
        // }
        // echo '</table>';

        ?>

</div>
</body>

</html>
</div>