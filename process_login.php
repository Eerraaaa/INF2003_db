<?php
// Start a new session or resume the existing one
session_start();

// Include the database connection file
require 'lib/connection.php';

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
$stmt = $conn->prepare("SELECT id, f_name, email, pass, is_admin FROM project.users WHERE email = ?");
// Bind the input email to the prepared statement as a parameter
$stmt->bind_param("s", $email);
// Execute the prepared statement
$stmt->execute();
// Get the result set from the prepared statement
$result = $stmt->get_result();
// Fetch the associative array from the result set
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['pass'])) {
    // Regenerate the session ID to prevent session fixation attacks
    session_regenerate_id(true);
    // Set session variables with user information
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    // Save first name in session for dynamic nav
    $_SESSION['user_first_name'] = $user['f_name'];
    // Store whether the user is an admin
    $_SESSION['is_admin'] = $user['is_admin'];


     // Check if the user is an admin and redirect accordingly
     if ($user['is_admin']) {
        header("Location: admin/home.php");
        exit();
    } else {
        // Non-admin user redirection logic
        if (!empty($_POST['redirect']) && $_POST['redirect'] == 'product' && !empty($_POST['product_id'])) {
            header("Location: product.php?add_to_cart=" . intval($_POST['product_id']));
        } else {
            header("Location: user_dashboard.php");
        }
        exit();
    }
} else {
    // If login failed, redirect back to the login page with an error message
    $_SESSION['error_message'] = "Login failed: Email or password is incorrect.";
    header("Location: newlogin.php");
    exit();
}
