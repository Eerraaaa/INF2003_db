<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../lib/connection.php';

// Function to sanitize input
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to fetch distinct towns from the Location table (for validation purposes)
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

// Function to handle form submission
function handle_form_submission($conn) {
    $required_fields = ['username', 'email', 'pass', 'confirm_password', 'fname', 'phone_number', 'areaInCharge'];
    $errors = [];

    // Check if any required field is missing
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Please fill in all required fields.";
            break;
        }
    }

    // Sanitize and validate input
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $fname = sanitize_input($_POST['fname']);
    $lname = sanitize_input($_POST['lname']);
    $phone_number = sanitize_input($_POST['phone_number']);
    $areaInCharge = sanitize_input($_POST['areaInCharge']);
    $password = $_POST['pass'];
    $confirm_password = $_POST['confirm_password'];

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    // Phone number validation
    if (strlen($phone_number) != 8 || !ctype_digit($phone_number)) {
        $errors[] = 'Phone number must be exactly 8 digits.';
    }

    // Password match check
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // Ensure areaInCharge is a valid town from the Location table
    $valid_towns = get_towns($conn);
    if (!in_array($areaInCharge, $valid_towns)) {
        $errors[] = 'Invalid area selected.';
    }

    // Check for unique email
    $stmt = $conn->prepare("SELECT email FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = 'Email already exists. Please use another.';
    }

    // If there are errors, redirect to the error page
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        header("Location: reg_error.php");
        exit();
    }

    // Proceed with registration if no errors
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // 1. Insert into Users table
    $stmt = $conn->prepare("INSERT INTO Users (username, email, password, userType, fname, lname, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $userType = 'agent';
    $stmt->bind_param("sssssss", $username, $email, $hashed_password, $userType, $fname, $lname, $phone_number);

    if (!$stmt->execute()) {
        $_SESSION['form_errors'] = ['An error occurred during user registration.'];
        header("Location: create_error.php");
        exit();
    }

    // Get the last inserted userID
    $userID = $conn->insert_id;

    // 2. Insert into Agent table
    $stmt = $conn->prepare("INSERT INTO Agent (areaInCharge, userID) VALUES (?, ?)");
    $stmt->bind_param("si", $areaInCharge, $userID);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Registration successful! Welcome, ' . $username . '!';
        header("Location: create_success.php");
        exit();
    } else {
        $_SESSION['form_errors'] = ['An error occurred during agent registration.'];
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

