<?php
include 'header.php';
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: unauthorized.php"); // Redirect to unauthorized access page
    exit();
}

include 'lib/connection.php';

// Query for property table with search or location, sorting, and pagination
$agentQuery = "SELECT * FROM Agent";
$agentResult = $conn->query($agentQuery);

if (!$agentResult) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Agent Account</title>
    <link rel="stylesheet" href="../css/home.css">
</head>

<body>
    <div class="container homebody">
        <div class="row">
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-2"></div>
        <div class="col-lg-10 title">
            <h2>Create New Agent Account</h2>
        </div>
    </div>

    <form id="registrationForm" action="process_createAgent.php" method="post">
        <!-- Form fields -->

        <div class="field">
          <label for="fname">First Name <span class="required-asterisk">*</span></label>
          <input type="text" id="fname" name="fname" required>
        </div>

        <div class="field">
          <label for="lname">Last Name</label>
          <input type="text" id="lname" name="lname">
        </div>
        
        <div class="field">
          <label for="username">Username</label>
          <input type="text" id="username" name="username">
        </div>

        <div class="field">
          <label for="email">Email Address <span class="required-asterisk">*</span></label>
          <input type="email" id="email" name="email" autocomplete="off" required>
        </div>

        <div class="field">
          <label for="phone_number">Phone Number <span class="required-asterisk">*</span></label>
          <input type="text" id="phone_number" name="phone_number" required pattern="\d{8}" title="Phone number should be 8 digits">
        </div>

        <div class="field">
          <label for="areaInCharge">Area In Charge <span class="required-asterisk">*</span></label>
          <input type="text" id="areaInCharge" name="areaInCharge">
        </div>


        <div class="field">
          <label for="password">Password <span class="required-asterisk">*</span></label>
          <input type="password" id="password" name="pass" required>
        </div>

        <div class="field">
          <label for="confirm_password">Confirm Password <span class="required-asterisk">*</span></label>
          <input type="password" id="confirm_password" name="confirm_password" required>
        </div>


        <div class="field btns">
          <button type="submit" class="submit">Submit</button>
        </div>
      </form>

</html>
