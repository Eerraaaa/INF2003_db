<?php
include 'header.php';
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: unauthorized.php"); // Redirect to unauthorized access page
    exit();
}

include 'lib/connection.php';
include "../inc/headform.inc.php";

// Function to fetch distinct towns from the Location table
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

// Get towns for the dropdown
$towns = get_towns($conn);

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

<main id="main-content">
    <div class="container" style="margin-top: 220px;">

    <h1>Create New Agent Account</h1>

    <?php
      if (isset($_SESSION['form_errors'])) {
        foreach ($_SESSION['form_errors'] as $error) {
          echo "<div class='error-message'>$error</div>";
        }
        // Clear errors after displaying
        unset($_SESSION['form_errors']);
      }
      ?>

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
        <select name="areaInCharge" id="areaInCharge" required>
            <option value="">Select a town</option>
            <?php
            foreach ($towns as $town) {
                echo "<option value=\"" . htmlspecialchars($town) . "\">" . htmlspecialchars($town) . "</option>";
            }
            ?>
        </select>
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
      </main>
      <script src="../js/formscript.js"></script>
</body>
</html>
