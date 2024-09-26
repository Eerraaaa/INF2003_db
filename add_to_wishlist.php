<?php
session_start();
require 'lib/connection.php';

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$productId = filter_input(INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT);

// Validate product ID
if (!$productId) {
    header('Location: product.php?error=invalidproductid');
    exit;
}

// Check if the product already exists in the user's wishlist
$stmt = $conn->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

// If the count is greater than 0, the item already exists in the wishlist
if ($count > 0) {
    header('Location: product.php?error=alreadyinwishlist');
    exit;
}

// Insert the product into the wishlist table
$stmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();
$stmt->close();

// Redirect to wishlist page
header('Location: wishlist.php');
exit;
?>
