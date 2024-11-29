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
        exit();
    }

    try {
        $conn->begin_transaction(); // Start the transaction

        // Lock the property row
        $lockSql = "SELECT approvalStatus FROM Property WHERE propertyID = ? FOR UPDATE";
        $lockStmt = $conn->prepare($lockSql);
        if (!$lockStmt) {
            throw new Exception("Prepare failed for locking: " . $conn->error);
        }
        $lockStmt->bind_param("i", $propertyID);
        $lockStmt->execute();
        $lockResult = $lockStmt->get_result();
        $property = $lockResult->fetch_assoc();

        if (!$property) {
            throw new Exception("Property not found.");
        }

        if ($property['approvalStatus'] === 'rejected') {
            throw new Exception("Property is already rejected.");
        }

        // Update the property status and add rejection details
        $updateSql = "UPDATE Property SET approvalStatus = 'rejected', 
                      rejectReason = ?, rejectComments = ? 
                      WHERE propertyID = ?";
        $updateStmt = $conn->prepare($updateSql);
        if (!$updateStmt) {
            throw new Exception("Prepare failed for update: " . $conn->error);
        }
        $updateStmt->bind_param('ssi', $reason, $comments, $propertyID);
        $updateStmt->execute();

        // Commit the transaction
        $conn->commit();
        header("Location: agent_home.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback(); // Rollback the transaction on error
        echo "Error rejecting the property: " . $e->getMessage();
    }
} else {
    echo "Invalid request method.";
}

$conn->close();
?>