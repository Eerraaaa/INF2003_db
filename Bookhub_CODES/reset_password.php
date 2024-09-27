<?php
session_start(); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

use PHPMailer\PHPMailer\PHPMailer;
require 'vendor/autoload.php'; 

include'lib/connection.php';


// Check for token in the URL
if (!isset($_GET['token']) || empty($_GET['token'])) {
    // If token is not set or empty, redirect to the login page or show an error
    $_SESSION['error'] = "Invalid token.";
    header('Location: login.php');
    exit();
}

$token = $_GET['token'];

// If token is valid and not expired, proceed with password reset
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password']) && isset($_POST['confirm_password'])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
if ($password !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match.";
    header('Location: reset_password.php?token=' . urlencode($token));
    exit;
}

    // Check if the password and confirm password are the same
    if ($password === $confirm_password) {
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the statement to update the user's password
        $stmt = $conn->prepare("UPDATE users SET pass = ?, reset_token = NULL, token_expires_at = NULL WHERE reset_token = ?");
       

        if ($stmt === false) {
            // Handle the error properly - maybe log it and show a user-friendly message
            $_SESSION['error'] = "There was a problem resetting your password.";
            header('Location: reset_password.php?token=' . urlencode($token));
            exit;
        }

        $stmt->bind_param("ss", $hashed_password, $token);

        // Execute the statement and check if the update was successful
        if ($stmt->execute()) {
            // Password updated successfully
            $_SESSION['message'] = "Your password has been reset successfully.";
            $stmt->close();
            header('Location: newlogin.php');
            exit();
        } else {
            // Error occurred
            $_SESSION['error'] = "Failed to reset your password.";
            // Redirect back to the reset password page with an error message
    header('Location: reset_password.php?token=' . urlencode($token));
    exit;
        }
    } else {
        $_SESSION['error'] = "Passwords do not match.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<?php
    include "inc/head.inc.php";
?>

<body>
<?php
    include "inc/nav.inc.php";
    ?>

<main class="container" style="padding-top: 200px;" >
<form action="reset_password.php?token=<?= htmlspecialchars($token) ?>" method="post">
<div class="mb-3">    
<h1>Reset Password</h1>
<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success">
        <?php 
            echo $_SESSION['message']; 
            unset($_SESSION['message']);
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?php 
            echo $_SESSION['error']; 
            unset($_SESSION['error']);
        ?>
    </div>
<?php endif; ?>
<label for="password">New Password:</label>
    <input type="password" id="password" name="password" required>
    </div>
    <div class="mb-3">   
    <label for="confirm_password">Confirm New Password:</label>
    <input type="password" id="confirm_password" name="confirm_password" required>
    </div>
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
    <div class="mb-3">
    <button type="submit" value="Reset Password" class="btn btn-primary">Reset Password</button>
    </div>
</form>
</main>

<?php
    include "inc/footer.inc.php";
    ?>
</body>
</html>