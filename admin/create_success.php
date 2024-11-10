<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Account Creation Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 40px auto;
            text-align: center;
        }
        a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        if (isset($_SESSION['success_message'])) {
            echo "<h1>" . htmlspecialchars($_SESSION['success_message']) . "</h1>";
            echo "<p>Agent Account Creation was successful.";
            // Optionally clear the success message from session after displaying it
            unset($_SESSION['success_message']);
        } else {
            echo "<h1>Success!</h1>";
            echo "<p>Agent Account Creation was successful.<br>Now redirecting to home page...</p>";
        }
        ?>
        <a href="admin_home.php">Home</a>
    </div>
    <!-- Redirect to the login page after a delay -->
    <script>
        setTimeout(function() {
            window.location.href = "home.php";
        }, 5000); // Redirect after 5 seconds
    </script>
</body>
</html>
