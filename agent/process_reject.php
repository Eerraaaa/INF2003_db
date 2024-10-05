<?php
session_start();
include '../lib/connection.php';

// Ensure the user is logged in and is an agent
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
    header("Location: unauthorized.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $propertyID = isset($_POST['propertyID']) ? intval($_POST['propertyID']) : null;
    $reason = isset($_POST['reason']) ? $_POST['reason'] : null;
    $comments = isset($_POST['comments']) ? $_POST['comments'] : null;

    if (!$propertyID || !$reason || !$comments) {
        echo "Missing required information.";
        exit();    }

    // Update the property status and add unapproval details
    $sql = "UPDATE Property SET approvalStatus = 'rejected', 
            rejectReason = ?, rejectComments = ? 
            WHERE propertyID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $reason, $comments, $propertyID);
    
    if ($stmt->execute()) {
        header("Location: agent_home.php");
        exit();
    } else {
        echo "Error unapproving the listing: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Invalid request method.";
}

$conn->close();
?>