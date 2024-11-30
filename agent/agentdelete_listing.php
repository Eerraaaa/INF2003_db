<?php
session_start();
include '../lib/connection.php';
include '../lib/mongodb.php';  // Add MongoDB connection

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

try {
    // Get MongoDB connection and agent info
    $mongodb = MongoDBConnection::getInstance();
    $agentInfo = $mongodb->findOne('agent', ['userID' => (int)$_SESSION['userID']]);
    
    if (!$agentInfo) {
        throw new Exception("Agent information not found.");
    }

    $agentID = $agentInfo['agentID'];

    // Start transaction
    $conn->begin_transaction();

    // First verify this is an approved listing that belongs to this agent
    $checkSql = "SELECT approvalStatus FROM Property 
                 WHERE propertyID = ? AND agentID = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('ii', $propertyID, $agentID);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Property not found or you don't have permission to delete it.");
    }
    
    $property = $result->fetch_assoc();
    if ($property['approvalStatus'] !== 'approved') {
        throw new Exception("Only approved listings can be deleted.");
    }

    // Delete the property
    $deleteSql = "DELETE FROM Property WHERE propertyID = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param('i', $propertyID);
    
    if (!$deleteStmt->execute()) {
        throw new Exception("Error deleting the property: " . $conn->error);
    }

    // Commit the transaction
    $conn->commit();
    
    // Set success message and redirect
    $_SESSION['success_message'] = "Listing has been successfully deleted.";
    header("Location: agent_home.php");
    exit();

} catch (Exception $e) {
    // Rollback the transaction in case of error
    if (isset($conn)) {
        $conn->rollback();
    }
    
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: agent_home.php");
    exit();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>