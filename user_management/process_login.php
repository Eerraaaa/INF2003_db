<?php
// Start a new session or resume the existing one
session_start();

// Include the database connection file
require '../lib/connection.php';

// Function to sanitize user input for security
function sanitize_input($data)
{
    global $conn; // Access the global connection object
    // Use mysqli_real_escape_string to prevent SQL Injection and htmlspecialchars to prevent XSS
    return mysqli_real_escape_string($conn, htmlspecialchars(stripslashes(trim($data))));
}

// Retrieve and sanitize the email and password from POST data
$email = sanitize_input($_POST['email'] ?? '');
$password = $_POST['pwd'] ?? '';

// Prepare a SQL statement for execution, preventing SQL injection
$stmt = $conn->prepare("SELECT userID, fname, email, password, userType FROM Users WHERE email = ?");
// Bind the input email to the prepared statement as a parameter
$stmt->bind_param("s", $email);
// Execute the prepared statement
$stmt->execute();
// Get the result set from the prepared statement
$result = $stmt->get_result();
// Fetch the associative array from the result set
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    // Regenerate the session ID to prevent session fixation attacks
    session_regenerate_id(true);
    // Set session variables with user information
    $_SESSION['userID'] = $user['userID'];  // Use userID as per your database
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_first_name'] = $user['fname']; // Changed to fname as per your database
    $_SESSION['user_type'] = $user['userType']; // Adjusted to match your userType field

    // Check if the user is an admin and redirect accordingly
    if ($user['userType'] === 'admin') {  // Assuming userType is a string for admin
        header("Location: ../admin/home.php");
        exit();
    } else if ($user['userType'] === 'agent') {
        header("Location: ../agent/home.php");
        exit();
    } else if ($user['userType'] === 'buyer') {
        header("Location: ../index.php");
        exit();
    } else if ($user['userType'] === 'seller') {
        header("Location: ../seller/seller_home.php");
        exit();
    }

} else {
    // If login failed, redirect back to the login page with an error message
    $_SESSION['error_message'] = "Login failed: Email or password is incorrect.";
    header("Location: newlogin.php");
    exit();
}
?>
