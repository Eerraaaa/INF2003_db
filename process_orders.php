<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'lib/connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: newlogin.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Step 1: Insert into orders table
$insertOrderSql = "INSERT INTO orders (userid, status, created_at) VALUES (?, 'pending', NOW())";
$stmt = $conn->prepare($insertOrderSql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    exit('Error preparing statement');
}
$stmt->bind_param("i", $userId);
if (!$stmt->execute()) {
    // If execution failed, log or output error
    error_log($stmt->error);
    exit('Error in execution: ' . $stmt->error);
}

$orderId = $conn->insert_id; // Get the newly created order ID
echo "Order ID: " . $orderId . "<br>"; // Debug: Output order ID

// Step 2: Transfer items from cart to order_items
$cartItemsSql = "SELECT productid, quantity FROM cart WHERE userid = ?";
$stmt = $conn->prepare($cartItemsSql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    exit('Error preparing statement');
}
$stmt->bind_param("i", $userId);
if (!$stmt->execute()) {
    // If execution failed, log or output error
    error_log($stmt->error);
    exit('Error in execution: ' . $stmt->error);
}

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $insertItemSql = "INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)";
    $itemStmt = $conn->prepare($insertItemSql);
    if (!$itemStmt) {
        error_log("Prepare failed: " . $conn->error);
        exit('Error preparing statement');
    }
    // Assume price is fetched or calculated here if needed
    $itemStmt->bind_param("iii", $orderId, $row['productid'], $row['quantity']);
    if (!$itemStmt->execute()) {
        // If execution failed, log or output error
        error_log($itemStmt->error);
        exit('Error in execution: ' . $itemStmt->error);
    }
    
    echo "Inserted item for product ID " . $row['productid'] . " with quantity " . $row['quantity'] . "<br>"; // Debug: Output inserted items
}

// Step 3: Clear the user's cart
$clearCartSql = "DELETE FROM cart WHERE userid = ?";
$stmt = $conn->prepare($clearCartSql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    exit('Error preparing statement');
}
$stmt->bind_param("i", $userId);
if (!$stmt->execute()) {
    // If execution failed, log or output error
    error_log($stmt->error);
    exit('Error in execution: ' . $stmt->error);
}

echo "Order processed successfully";
?>
