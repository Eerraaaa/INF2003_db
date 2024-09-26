<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "inc/headform.inc.php"; ?>
    <title>Register</title>
</head>
<body>
    <?php include "inc/nav.inc.php"; ?> 
    <div class="container" style="padding-top: 250px;">
        <h2>Member Registration</h2>
        <form action="process_register.php" method="post">
            <div class="form-group">
                <label for="fname">First Name:</label>
                <input type="text" class="form-control" id="fname" name="fname" required>
            </div>
            <div class="form-group">
                <label for="lname">Last Name:</label>
                <input type="text" class="form-control" id="lname" name="lname">
            </div>
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="pass">Password:</label>
                <input type="password" class="form-control" id="pass" name="pass" required>
            </div>

            <div class="form-group">
                <label for="phone_number">Phone Number (optional):</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number">
            </div>

            <div class="form-group">
                <label for="address">Address (optional):</label>
                <input type="text" class="form-control" id="address" name="address">
            </div>

            <div class="form-group">
                <label for="dob">Date of Birth (optional):</label>
                <input type="date" class="form-control" id="dob" name="dob">
            </div>

            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
    <script src="js/formscript.js"></script>
</body>
</html>
