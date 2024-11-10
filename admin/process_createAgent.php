<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../lib/connection.php';
require '../lib/mongodb.php';

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function get_towns($conn) {
    $stmt = $conn->prepare("SELECT DISTINCT town FROM Location");
    $stmt->execute();
    $result = $stmt->get_result();
    $towns = [];
    while ($row = $result->fetch_assoc()) {
        $towns[] = $row['town'];
    }
    return $towns;
}

function handle_form_submission($conn) {
    $required_fields = ['username', 'email', 'pass', 'confirm_password', 'fname', 'phone_number', 'areaInCharge'];
    $errors = [];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Please fill in all required fields.";
            break;
        }
    }

    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $fname = sanitize_input($_POST['fname']);
    $lname = sanitize_input($_POST['lname']);
    $phone_number = sanitize_input($_POST['phone_number']);
    $areaInCharge = sanitize_input($_POST['areaInCharge']);
    $password = $_POST['pass'];
    $confirm_password = $_POST['confirm_password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if (strlen($phone_number) != 8 || !ctype_digit($phone_number)) {
        $errors[] = 'Phone number must be exactly 8 digits.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    $valid_towns = get_towns($conn);
    if (!in_array($areaInCharge, $valid_towns)) {
        $errors[] = 'Invalid area selected.';
    }

    $stmt = $conn->prepare("SELECT email FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = 'Email already exists. Please use another.';
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        header("Location: create_error.php");
        exit();
    }

    try {
        // Insert into Users table (MySQL)
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO Users (username, email, password, userType, fname, lname, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $userType = 'agent';
        $stmt->bind_param("sssssss", $username, $email, $hashed_password, $userType, $fname, $lname, $phone_number);
        
        if (!$stmt->execute()) {
            throw new Exception("Error inserting user data");
        }

        $userID = $conn->insert_id;

        // Get MongoDB connection
        $mongodb = MongoDBConnection::getInstance();
        
        // Get the next agentID
        $query = new MongoDB\Driver\Query(
            [],
            ['sort' => ['agentID' => -1], 'limit' => 1]
        );
        $cursor = $mongodb->getConnection()->executeQuery("realestate_db.agent", $query);
        $lastAgent = current($cursor->toArray());
        $nextAgentID = $lastAgent ? $lastAgent->agentID + 1 : 1;

        // Insert into MongoDB agent collection
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert([
            'agentID' => $nextAgentID,
            'userID' => $userID,
            'areaInCharge' => $areaInCharge,
            'rating' => 0,
            'totalReviews' => 0
        ]);
        
        $result = $mongodb->getConnection()->executeBulkWrite('realestate_db.agent', $bulk);

        if ($result->getInsertedCount() > 0) {
            $_SESSION['success_message'] = 'Registration successful! Welcome, ' . $username . '!';
            header("Location: create_success.php");
            exit();
        } else {
            throw new Exception("Error inserting agent data");
        }
    } catch (Exception $e) {
        $_SESSION['form_errors'] = ['An error occurred during registration: ' . $e->getMessage()];
        header("Location: create_error.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    handle_form_submission($conn);
} else {
    $_SESSION['form_errors'] = ["Invalid request method."];
    header("Location: create_error.php");
    exit();
}

