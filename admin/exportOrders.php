<?php
function exportOrders($conn, $filename, $headers) {
    // Set the headers to output a CSV file
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    // Open the file pointer to 'php://output'
    $output = fopen('php://output', 'w');

    // Output the column headings
    fputcsv($output, $headers);

    // Build the query based on whether filter parameters are set
    $query = "SELECT name, address, phone, txid, status, created_at FROM orders";
    
    // Check if session variables for the filter are set
    if (isset($_SESSION['filter']['starttime']) && isset($_SESSION['filter']['endtime'])) {
        $starttime = $_SESSION['filter']['starttime'];
        $endtime = $_SESSION['filter']['endtime'];
        $query .= " WHERE created_at >= '$starttime' AND created_at <= '$endtime'";
    }
    
    // Execute the query
    $result = $conn->query($query);
    
    // Check if there are any results
    if ($result && $result->num_rows > 0) {
        // Output each row of data
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
    } else {
        // Handle case where no records are found
        fputcsv($output, ['No records found.']);
    }
    
    // Close the file pointer
    fclose($output);

    // Clear the filter session variables after the export is done
unset($_SESSION['filter']);
    exit;
// Use this filename for the export
$filename = "orders_" . date('Y-m-d') . ".csv";
$headers = ['Name', 'Address', 'Phone', 'Transaction ID', 'Status', 'Created At'];

// Call the function to export the orders
exportOrders($conn, $filename, $headers);
}
?> 