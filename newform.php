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
      <h1>Member Registration</h1>
      <div class="progress-bar">
        <div class="step">
          <p>Name</p>
          <div class="bullet">
            <span>1</span>
          </div>
          <div class="check fas fa-check"></div>
        </div>
        <div class="step">
          <p>Contact</p>
          <div class="bullet">
            <span>2</span>
          </div>
          <div class="check fas fa-check"></div>
        </div>
        <div class="step">
          <p>Address</p>
          <div class="bullet">
            <span>3</span>
          </div>
          <div class="check fas fa-check"></div>
        </div>
        <div class="step">
          <p>Submit</p>
          <div class="bullet">
            <span>4</span>
          </div>
          <div class="check fas fa-check"></div>
        </div>
      </div>

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

      <!-- storing into db -->
      <div class="form-outer">

        <form id="registrationForm" action="process_register.php" method="post">

          <div class="page slide-page">
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
              <label for="dob">Date of Birth</label>
              <input type="date" id="dob" name="dob">
            </div>

            <div class="field">
              <button class="firstNext next">Next</button>
            </div>

          </div>
          <!-- end 1st page -->

          <div class="page">
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


            <div class="field btns">
              <button class="prev-1 prev">Previous</button>
              <button class="next-1 next">Next</button>
            </div>

          </div>
          <!-- end 2nd page -->

          <div class="page">
            <!-- Address Section -->
            <div class="title">Address:</div>

            <div class="field">
              <div class="label">Postal Code</div>
              <input maxlength="45" type="text" id="postal_code" name="postal_code" placeholder="e.g., 123456" autocomplete="off">
            </div>

            <div class="field">
              <div class="label">Street Address</div>
              <input maxlength="45" type="text" id="address" name="address" placeholder="Building, Street, etc..." autocomplete="off">
            </div>

            <div class="field">
              <div class="label">Unit Number (optional)</div>
              <input maxlength="45" type="text" id="unit_no" name="unit_no" placeholder="e.g., #12-345">
            </div>

            <div class="field btns">
              <button class="prev-2 prev">Previous</button>
              <button class="next-2 next">Next</button>
            </div>
          </div>
          <!-- end 3rd page -->


          <div class="page">
            <!-- Password Section -->
            <div class="title">Password:</div>

            <!-- Password Field -->
            <div class="field">
              <label for="password">Password <span class="required-asterisk">*</span></label>

              <input type="password" id="password" name="pass" required>
            </div>
            <!-- Confirm Password Field -->
            <div class="field">

              <label for="confirm_password">Confirm Password <span class="required-asterisk">*</span></label>
              <input type="password" id="confirm_password" name="confirm_password" required>

            </div>

            <div class="field btns">
              <button class="prev-3 prev">Previous</button>
              <button class="submit">Submit</button>
            </div>
          </div>
          <!-- end 4th page -->

        </form>
        <!-- end of form submission-->

      </div>
    </div>
  </main>
  <script src="js/formscript.js"></script>

</body>

</html>


