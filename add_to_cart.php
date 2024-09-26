<?php
session_start();
require 'lib/connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: newlogin.php');
    exit;
}

$userId = $_SESSION['user_id'];
$productId = filter_input(INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT);

if (!$productId) {
    header('Location: product.php?error=invalidproductid');
    exit;
}

// Check available stock first
$stockCheckStmt = $conn->prepare("SELECT quantity FROM product WHERE id = ?");
$stockCheckStmt->bind_param("i", $productId);
$stockCheckStmt->execute();
$stockResult = $stockCheckStmt->get_result()->fetch_assoc();
$availableStock = $stockResult['quantity'];

if ($availableStock <= 0) {
    // Handle case where no stock is available
    header('Location: product.php?error=outofstock');
    exit;
}

// Proceed with adding to cart or updating quantity
$stmt = $conn->prepare("SELECT quantity FROM cart WHERE userid = ? AND productid = ?");
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $cartItem = $result->fetch_assoc();
    $newQuantity = $cartItem['quantity'] + 1;

    // Ensure new quantity does not exceed available stock
    if ($newQuantity > $availableStock) {
        // Handle case where adding another item would exceed available stock
        header('Location: product.php?error=stocklimitreached');
        exit;
    }

    $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE userid = ? AND productid = ?");
    $updateStmt->bind_param("iii", $newQuantity, $userId, $productId);
    $updateStmt->execute();
    $updateStmt->close();
} else {
    // Insert new item with quantity 1 into the cart
    $insertStmt = $conn->prepare("INSERT INTO cart (userid, productid, quantity) VALUES (?, ?, 1)");
    $insertStmt->bind_param("ii", $userId, $productId);
    $insertStmt->execute();
    $insertStmt->close();
}

header('Location: cart.php');
exit;
?>
