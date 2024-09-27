<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'lib/connection.php';

function sanitize_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Function to handle form submission
function handle_form_submission($conn) {
    $required_fields = ['fname', 'email', 'phone_number', 'pass', 'confirm_password'];
    $errors = []; // Initialize an empty array to hold error messages
    $missing_fields = false; // Flag to indicate if any field is missing

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields = true; // At least one field is missing
            break; // No need to check further, one missing field is enough
        }
    }

    // If any required field is missing, add a single error message
    if ($missing_fields) {
        $errors[] = "Required fields not filled.";
    }

    // Sanitize and validate input
    $f_name = sanitize_input($_POST['fname']);
    $email = sanitize_input($_POST['email']);
    $phone_number = sanitize_input($_POST['phone_number']);
    $password = $_POST['pass']; // Corrected from 'password' to 'pass'
    $confirm_password = $_POST['confirm_password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if (strlen($phone_number) != 8 || !ctype_digit($phone_number)) {
        $errors[] = 'Phone number not correct, type 8 digits only.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // Check for unique email
    $stmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = 'Existing email, please use another.';
    }

    // If there are any errors, redirect to error page with errors
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors; // Store the array of errors
        header("Location: reg_error.php");
        exit();
    }

    // Proceed with user registration if there are no errors
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (f_name, email, phone_number, pass) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $f_name, $email, $phone_number, $hashed_password);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Registration successful! Welcome to Bookhub, ' . $f_name . '!';
        header("Location: reg_success.php");
        exit();
    } else {
        $_SESSION['form_errors'] = ['An error occurred during registration.'];
        header("Location: reg_error.php");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    handle_form_submission($conn);
} else {
    $_SESSION['form_errors'] = ["Invalid request method."];
    header("Location: reg_error.php");
    exit();
}
?>
