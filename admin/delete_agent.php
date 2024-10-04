<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: unauthorized.php"); // Redirect to unauthorized access page
    exit();
}

include 'lib/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agentID'])) {
    $agentID = $_POST['agentID'];

    // Start a transaction
    $conn->begin_transaction();

    try {
        // First, delete from the Agent table
        $deleteAgentQuery = "DELETE FROM Agent WHERE agentID = ?";
        $stmt = $conn->prepare($deleteAgentQuery);
        $stmt->bind_param("i", $agentID);
        $stmt->execute();

        // Then, delete from the Users table
        $deleteUserQuery = "DELETE FROM Users WHERE userID = (SELECT userID FROM Agent WHERE agentID = ?)";
        $stmt = $conn->prepare($deleteUserQuery);
        $stmt->bind_param("i", $agentID);
        $stmt->execute();

        // Commit the transaction
        $conn->commit();

        // Redirect back to the agent list with a success message
        header("Location: home.php?message=Agent deleted successfully");
        exit();
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();
        die("Error deleting agent: " . $e->getMessage());
    }
} else {
    // Redirect if agentID is not set
    header("Location: home.php?message=Invalid request");
    exit();
}
?>
