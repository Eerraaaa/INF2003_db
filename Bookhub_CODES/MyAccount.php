<!DOCTYPE HTML>
<!--
Done By: Bryan -->
<html lang="en">

<head>
<?php
include "inc/headacc.inc.php";
?> 
</head>

<body>
<?php
include "inc/nav.inc.php";
?> 
    <main class="container" style="margin-top: 220px;">
        <div class="row">
            <div class="col">
            <hr> <!-- Divider -->
                <h1>Member Registration</h1>
                <p>
                    For existing members, please go to the
                    <a href="newlogin.php">Sign In page</a>.
                </p>
                <form action="process_register.php" method="post">

                    <div class="mb-3">
                        <label for="fname" class="form-label">First Name:</label>
                        <input type="fname" id="fname" name="fname" class="form-control" placeholder="Enter first name">
                    </div>

                    <div class="mb-3">
                        <label for="lname" class="form-label">Last Name:</label>
                        <input required maxlength="45" type="lname" id="lname" name="lname" class="form-control" placeholder="Enter last name">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email:</label>
                        <input required maxlength="45" type="email" id="email" name="email" class="form-control" placeholder="Enter email">
                    </div>

                    <div class="mb-3">
                        <label for="pwd" class="form-label">Password:</label>
                        <input required type="password" id="pwd" name="pwd" class="form-control" placeholder="Enter password">
                    </div>

                    <div class="mb-3">
                        <label for="pwd_confirm" class="form-label">Confirm Password:</label>
                        <input required type="password" id="pwd_confirm" name="pwd_confirm" class="form-control" placeholder="Confirm password">
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" id="newsletter" name="newsletter" class="form-check-input">
                        <label for="newsletter" class="form-check-label">
                            Subscribe to our newsletter
                            <small class="text-muted italic">
                                Stay up-to-date with our latest book releases, exclusive offers, and literary news!
                            </small>
                        </label>
                    </div>

                    <div class="mb-3 form-check">
                        <input required type="checkbox" name="agree" class="form-check-input">
                        <label class="form-check-label">
                            Agree to terms and conditions.
                        </label>
                    </div>
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </main>



<!-- Footer -->
<?php
include "inc/footer.inc.php";
?> 
</html> 
</body>