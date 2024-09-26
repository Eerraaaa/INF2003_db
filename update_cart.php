<?php
session_start();
require 'lib/connection.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['action']) || !isset($_POST['product_id'])) {
    // Redirect or error handling
    exit('Invalid request');
}

if (!isset($_POST['item_type'])) {
    exit('Invalid request');
}

$itemType = $_POST['item_type'];

$userId = $_SESSION['user_id'];
$action = $_POST['action'];
$productId = $_POST['product_id'];

// Fetch current quantity in cart and stock availability
$cartStmt = $conn->prepare("SELECT quantity FROM cart WHERE userid = ? AND productid = ?");
$cartStmt->bind_param("ii", $userId, $productId);
$cartStmt->execute();
$cartResult = $cartStmt->get_result()->fetch_assoc();
$currentQuantity = $cartResult['quantity'] ?? 0;

$stockStmt = $conn->prepare("SELECT quantity FROM product WHERE id = ?");
$stockStmt->bind_param("i", $productId);
$stockStmt->execute();
$stockResult = $stockStmt->get_result()->fetch_assoc();
$availableStock = $stockResult['quantity'];

switch ($action) {
    case 'increase':
        if ($currentQuantity < $availableStock) {
            $newQuantity = $currentQuantity + 1;
            $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE userid = ? AND productid = ?");
            $updateStmt->bind_param("iii", $newQuantity, $userId, $productId);
            $updateStmt->execute();
        } else {
            // Handle case where stock is insufficient
        }
        break;
    case 'decrease':
        if ($currentQuantity > 1) {
            $newQuantity = $currentQuantity - 1;
            $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE userid = ? AND productid = ?");
            $updateStmt->bind_param("iii", $newQuantity, $userId, $productId);
            $updateStmt->execute();
        } else {
            // Consider removing the item if quantity would go below 1
        }
        break;
    case 'remove':
        if ($itemType == 'subscription') {
            // Remove subscription item from the session
            unset($_SESSION['cart']['subscription']);
        } else {
            // Existing code to remove a product item from the cart
            $deleteStmt = $conn->prepare("DELETE FROM cart WHERE userid = ? AND productid = ?");
            $deleteStmt->bind_param("ii", $userId, $productId);
            $deleteStmt->execute();
        }
        break;
}
header('Location: cart.php');
exit;
