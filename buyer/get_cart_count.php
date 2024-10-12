<?php
session_start();
include '../lib/connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'buyer') {
    echo json_encode(['count' => 0]);
    exit();
}

$userID = $_SESSION['userID'];
$count = 0;

try {
    $sql = "SELECT COUNT(*) as count FROM Cart WHERE userID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $count = $row['count'];
} catch (Exception $e) {
    error_log("Error in get_cart_count.php: " . $e->getMessage());
}

echo json_encode(['count' => $count]);
?>