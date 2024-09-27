<?php
session_start();
require 'lib/connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$productId = filter_input(INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT);

if (!$productId) {
    header('Location: wishlist.php?error=invalidproductid');
    exit;
}

// Delete the item from the wishlist
$stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->bind_param("ii", $userId, $productId);
$stmt->execute();
$stmt->close();

header('Location: wishlist.php');
exit;
?>
