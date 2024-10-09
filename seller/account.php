<?php  
session_start();  
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {  
    header("Location: unauthorized.php"); // Redirect to unauthorized access page  
    exit();  
}
include '../lib/connection.php';  
include "../inc/sellernav.inc.php";

$sellerID = $_SESSION['userID'];

// Fetch the seller's details
$sql = "SELECT fname, lname, phone_number, username, email, password FROM Users WHERE userID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Error preparing the query: " . $conn->error;
    exit();
}
$stmt->bind_param('i', $sellerID);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $email = $_POST['email'];

    // Initialize messages for feedback
    $error_msg = "";
    $success_msg = "";

    // Check if user wants to update the password
    if (!empty($_POST['old_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_new_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_new_password = $_POST['confirm_new_password'];

        // Fetch the current password
        $sql = "SELECT password FROM Users WHERE userID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $sellerID);
        $stmt->execute();
        $stmt->bind_result($current_password);
        $stmt->fetch();
        $stmt->close();

        // Verify old password
        if (password_verify($old_password, $current_password)) {
            if ($new_password === $confirm_new_password) {
                // Hash the new password
                $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);

                // Update password
                $sql = "UPDATE Users SET password = ? WHERE userID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('si', $hashed_new_password, $sellerID);
                if ($stmt->execute()) {
                    $success_msg = "Password updated successfully.";
                } else {
                    $error_msg = "Error updating password.";
                }
                $stmt->close();
            } else {
                $error_msg = "New password and confirm password do not match.";
            }
        } else {
            $error_msg = "Old password is incorrect.";
        }
    }

    // Update other details
    if (empty($error_msg)) {
        $sql = "UPDATE Users SET fname = ?, lname = ?, phone_number = ?, username = ?, email = ? WHERE userID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssi', $fname, $lname, $phone, $username, $email, $sellerID);
        if ($stmt->execute()) {
            $success_msg = "Details updated successfully.";
        } else {
            $error_msg = "Failed to update details.";
        }
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Account</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5" style="padding-top:100px;">
    <h2 class="text-center">My Account</h2>

    <!-- Display success or error messages -->
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_msg); ?></div>
    <?php endif; ?>
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
    <?php endif; ?>

    <!-- Update form -->
    <form method="POST">
        <div class="form-group">
            <label for="fname">First Name:</label>
            <input type="text" class="form-control" id="fname" name="fname" value="<?php echo htmlspecialchars($row['fname']); ?>" required>
        </div>
        <div class="form-group">
            <label for="lname">Last Name:</label>
            <input type="text" class="form-control" id="lname" name="lname" value="<?php echo htmlspecialchars($row['lname']); ?>" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone Number:</label>
            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($row['phone_number']); ?>" required>
        </div>
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($row['username']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
        </div>

        <!-- Password change fields -->
        <h3 class="mt-4">Change Password</h3>
        <div class="form-group">
            <label for="old_password">Old Password:</label>
            <input type="password" class="form-control" id="old_password" name="old_password">
        </div>
        <div class="form-group">
            <label for="new_password">New Password:</label>
            <input type="password" class="form-control" id="new_password" name="new_password">
        </div>
        <div class="form-group">
            <label for="confirm_new_password">Confirm New Password:</label>
            <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password">
        </div>

        <!-- Submit button -->
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
</body>
</html>
