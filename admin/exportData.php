<?php
session_start();
include 'lib/connection.php';

function exportReport($conn, $query, $filename, $headers) {
    // Check if there are results
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        // Set the headers to output a CSV file
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        // Open the file pointer to 'php://output' which is a write-only stream that allows you to write to the output buffer
        $output = fopen('php://output', 'w');

        // Output the column headings
        fputcsv($output, $headers);

        // Output the rows of data
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }

        // Close the file pointer
        fclose($output);
    } else {
        echo 'No records found.';
    }
    // Close the database connection
    $conn->close();
    exit;
}


// Get the report type from the URL parameter
$reportType = isset($_GET['report']) ? $_GET['report'] : '';

// Determine which report to export based on the 'report' parameter
switch ($reportType) {
        case 'users':
            $query = "SELECT id, f_name, l_name, email FROM users";
            $filename = "users_" . date('Y-m-d') . ".csv";
            $headers = ['ID', 'First Name', 'Last Name', 'Email'];
            exportReport($conn, $query, $filename, $headers);
            break;

        case 'total_sales_per_day':
            $query = "SELECT DATE(created_at) AS 'Date', SUM(totalprice) AS 'Total Sales' FROM orders GROUP BY DATE(created_at) ORDER BY DATE(created_at) DESC";
            $filename = "total_sales_per_day_" . date('Y-m-d') . ".csv";
            $headers = ['Date', 'Total Sales'];
            exportReport($conn, $query, $filename, $headers);
            break;
    
        case 'orders_count_by_status':
            $query = "SELECT status AS 'Status', COUNT(*) AS 'Order Count' FROM orders GROUP BY status";
            $filename = "orders_count_by_status_" . date('Y-m-d') . ".csv";
            $headers = ['Status', 'Order Count'];
            exportReport($conn, $query, $filename, $headers);
            break;
    
        case 'sales_products_sold_by_day':
            $query = "SELECT DATE(created_at) AS 'Date', SUM(totalprice) AS 'Daily Sales', SUM(totalproduct) AS 'Products Sold' FROM orders GROUP BY DATE(created_at) ORDER BY DATE(created_at) DESC";
            $filename = "sales_products_sold_by_day_" . date('Y-m-d') . ".csv";
            $headers = ['Date', 'Daily Sales', 'Products Sold'];
            exportReport($conn, $query, $filename, $headers);
            break;
    
        case 'average_order_value_by_day':
            $query = "SELECT DATE(created_at) AS 'Date', AVG(totalprice) AS 'Average Order Value' FROM orders GROUP BY DATE(created_at)";
            $filename = "average_order_value_by_day_" . date('Y-m-d') . ".csv";
            $headers = ['Date', 'Average Order Value'];
            exportReport($conn, $query, $filename, $headers);
            break;
        
        case 'orders':
            $query = "SELECT name, address, phone, txid, status, created_at FROM orders";
            $filename = "orders_" . date('Y-m-d') . ".csv";
            $headers = ['Name', 'Address', 'Phone', 'Transaction ID', 'Status', 'Timestamp'];
            exportReport($conn, $query, $filename, $headers);
            break;

        case 'contact':
            $query = "SELECT salutation, name, email, message, submitted_at FROM contact_submissions";
            $filename = "contact-us_" . date('Y-m-d') . ".csv";
            $headers = ['Salutation', 'Name', 'Email', 'Message','Timestamp'];
            exportReport($conn, $query, $filename, $headers);
            break;

        case 'products':
            $query = "SELECT name, catagory, description, quantity, Price FROM product";
            $filename = "products_" . date('Y-m-d') . ".csv";
            $headers = ['Name', 'Category', 'Description', 'Quantity', 'Price'];
            exportReport($conn, $query, $filename, $headers);
            break;

    default:
        echo 'Invalid report type.';
        break;
}

?>
