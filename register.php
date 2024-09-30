<!DOCTYPE html>
<html lang="en">

<head>
</head>

<body>
  <?php
  session_start();
  include 'lib/connection.php';
  include "inc/headform.inc.php";
  include "inc/nav.inc.php";
  ?>

  <main id="main-content">
    <div class="container" style="margin-top: 220px;">
      <h1>Registration</h1>

      <!-- Display error messages if any -->
      <?php
      if (isset($_SESSION['form_errors'])) {
        foreach ($_SESSION['form_errors'] as $error) {
          echo "<div class='error-message'>$error</div>";
        }
        // Clear errors after displaying
        unset($_SESSION['form_errors']);
      }
      ?>

      <form id="registrationForm" action="process_register.php" method="post">

        <!-- Basic Info Section -->
        <div class="title">Basic Info:</div>

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
          <label for="userType">Are you here as a buyer or seller?</label>
          <select id="userType" name="userType">
            <option value="buyer">Buyer</option>
            <option value="seller">Seller</option>
          </select>
        </div>

        <!-- Contact Info Section -->
        <div class="title">Contact Info:</div>

        <div class="field">
          <label for="email">Email Address <span class="required-asterisk">*</span></label>
          <input type="email" id="email" name="email" autocomplete="off" required>
        </div>

        <div class="field">
          <label for="phone_number">Phone Number <span class="required-asterisk">*</span></label>
          <input type="text" id="phone_number" name="phone_number" required pattern="\d{8}" title="Phone number should be 8 digits">
        </div>

        <!-- Password Section -->
        <div class="title">Password:</div>

        <div class="field">
          <label for="password">Password <span class="required-asterisk">*</span></label>
          <input type="password" id="password" name="pass" required>
        </div>

        <div class="field">
          <label for="confirm_password">Confirm Password <span class="required-asterisk">*</span></label>
          <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <!-- Submit Button -->
        <div class="field btns">
          <button type="submit" class="submit">Submit</button>
        </div>

      </form>
    </div>
  </main>
  <script src="js/formscript.js"></script>

</body>

</html>
