<?php
// delete_agent.php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

include 'lib/connection.php';
include 'lib/mongodb.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agentID'])) {
    $agentID = (int)$_POST['agentID'];

    try {
        // Get MongoDB connection
        $mongodb = MongoDBConnection::getInstance();
        
        // First, get the userID from the agent document
        $filter = ['agentID' => $agentID];
        $agentData = $mongodb->findOne('agent', $filter);
        
        if ($agentData) {
            // Delete from MongoDB agent collection
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->delete(['agentID' => $agentID]);
            $mongodb->getConnection()->executeBulkWrite('realestate_db.agent', $bulk);
            
            // Delete from MongoDB agentreviews collection
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->delete(['agentID' => $agentID]);
            $mongodb->getConnection()->executeBulkWrite('realestate_db.agentreviews', $bulk);
            
            // Delete from MySQL Users table
            $deleteUserQuery = "DELETE FROM Users WHERE userID = ?";
            $stmt = $conn->prepare($deleteUserQuery);
            $stmt->bind_param("i", $agentData['userID']);
            $stmt->execute();
        }

        header("Location: admin_home.php?message=Agent deleted successfully");
        exit();
    } catch (Exception $e) {
        die("Error deleting agent: " . $e->getMessage());
    }
} else {
    header("Location: admin_home.php?message=Invalid request");
    exit();
}