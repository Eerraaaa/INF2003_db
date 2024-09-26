<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
require 'vendor/autoload.php'; 

include'lib/connection.php';

// Start the session
session_start();

// Check for form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Generate a unique token 
        $token = bin2hex(random_bytes(16));

        // Store the token in the database with the timestamp
        $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour')); // 1 hour validity
        $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, token_expires_at = ? WHERE email = ?");
        $updateStmt->bind_param("sss", $token, $expires_at, $email);
        $updateStmt->execute();
        
        // Send email with the token
        $mail = new PHPMailer(true);
               
               try {
                 //Server settings
                 $mail->isSMTP();
                 $mail->Host       = 'smtp.gmail.com';
                 $mail->SMTPAuth   = true;
                 $mail->Username   = 'bbernicecyq@gmail.com';
                 $mail->Password   = 'oaon jitu rcew nxec';
                 $mail->SMTPSecure = 'tls';
                 $mail->Port       = 587;
                 $mail->SMTPDebug = 0;
  
                 //Recipients
                 $mail->setFrom('bbernicecyq@gmail.com', 'BookHub');
                 $mail->addAddress($email); // Add the user's email address
  
                 // Content
                 // $mail->isHTML(true);
                 $mail->Subject = 'Reset Password Request';
                 $resetLink = "http://35.212.129.117/reset_password.php?token=$token";
                 $mail->Body = "Please click on the following link to reset your password: $resetLink";
     
                 $mail->send();
                 $_SESSION['message'] = 'Reset password link has been sent to your email.';
                } catch (Exception $e) {
                    $_SESSION['error'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                } 
            }  else {
                $_SESSION['error'] = "No user found with that email address.";
            }
            $stmt->close();
            // Redirect to the same page to display the message
    header('Location: forgot_pwd.php');
    // Close connection
$conn->close();
    exit();
 }
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
    <main class="container" style="padding-top: 150px;" >
        <h1>Forgot Password?</h1>
        <section id="Forgot Password?">
        <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success" role="alert">
            <?= $_SESSION['message']; ?>
            <?php unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?= $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
        <p>
            We will send you an email with instructions on how to reset you password.<br>
        </p>
        <form action="forgot_pwd.php" method="post">

        <div class="mb-3">
                <label for="email" class="form-label">Enter your email address:</label>
                <input required maxlength="45" type="email" id="email" name="email" class="form-control"
                placeholder="name@example.com">
            </div>

            <div class="mb-3">
                <button type="submit" value="Submit" class="btn btn-primary">Email Me</button>
            </div>
</form>
        </article>
        </div>
    </main>

    <?php
    include "inc/footer.inc.php";
    ?>
</body>
</html>