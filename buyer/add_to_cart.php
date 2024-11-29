<?php
session_start();
include '../lib/connection.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Log function
function logError($message)
{
    error_log(date('[Y-m-d H:i:s] ') . "ADD TO CART ERROR: " . $message . "\n", 3, "../error.log");
}

// Check if user is logged in and is a buyer
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'buyer') {
    logError("Unauthorized access attempt");
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$userID = $_SESSION['userID'];
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['propertyID'])) {
    $propertyID = $_POST['propertyID'];

    try {
        // Start MySQL transaction
        $conn->begin_transaction();

        // Lock the property row to check availability
        $checkAvailability = "SELECT propertyID, availability, flatType, resalePrice FROM Property WHERE propertyID = ? FOR UPDATE";
        $stmt = $conn->prepare($checkAvailability);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $propertyID);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
        $property = $result->fetch_assoc();

        // Log the fetched property details
        error_log("Property Details: " . json_encode($property));

        if ($property && strtolower(trim($property['availability'])) == 'available') {
            // Insert the property into the cart
            $insertCartQuery = "INSERT INTO Cart (userID, propertyID) VALUES (?, ?)";
            $insertStmt = $conn->prepare($insertCartQuery);
            if (!$insertStmt) {
                throw new Exception("Prepare failed for cart insert: " . $conn->error);
            }
            $insertStmt->bind_param("ii", $userID, $propertyID);
            if (!$insertStmt->execute()) {
                throw new Exception("Execute failed for cart insert: " . $insertStmt->error);
            }

            // Commit the transaction
            $conn->commit();

            $response['success'] = true;
            $response['message'] = 'Property added to cart successfully!';
        } else {
            $response['message'] = 'Property is not available.';
            $conn->rollback(); // Roll back the transaction if property is not available
        }
    } catch (Exception $e) {
        $conn->rollback(); // Roll back the transaction in case of an error
        logError("Error adding property to cart: " . $e->getMessage());
        $response['message'] = 'An error occurred while processing your request: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request.';
}

echo json_encode($response);