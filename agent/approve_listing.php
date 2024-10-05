<?php
session_start();
include '../lib/connection.php';

// Ensure the user is logged in and is an agent
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
    header("Location: unauthorized.php");
    exit();
}

// Get the propertyID from the URL
$propertyID = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$propertyID) {
    echo "Invalid property ID.";
    exit();
}

// Update the approvalStatus to 'approved'
$sql = "UPDATE Property SET approvalStatus = 'approved' WHERE propertyID = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "Error preparing the query: " . $conn->error;
    exit();
}

// Bind the propertyID and execute the query
$stmt->bind_param('i', $propertyID);

if ($stmt->execute()) {
    // Redirect back to the listings page after approval
    header("Location: agent_home.php");
    exit();
} else {
    echo "Error approving the listing: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
