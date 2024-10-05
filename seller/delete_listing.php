<?php
session_start();
include '../lib/connection.php';

// Ensure the user is logged in and is a seller
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: unauthorized.php");
    exit();
}

// Get the propertyID from the URL
$propertyID = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$propertyID) {
    echo "Invalid property ID.";
    exit();
}

// Prepare the delete query
$sql = "DELETE FROM Property WHERE propertyID = ? AND sellerID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Error preparing the DELETE query: " . $conn->error;
    exit();
}

// Bind the propertyID and sellerID (to ensure the seller can only delete their own listings)
$sellerID = $_SESSION['userID']; // Assuming sellerID is stored in the session
$stmt->bind_param('ii', $propertyID, $sellerID);

// Execute the query
if ($stmt->execute()) {
    // Redirect back to the view listing page after deletion
    header("Location: seller_home.php");
    exit();
} else {
    echo "Error deleting property: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
