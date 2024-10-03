<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Account Creation Error</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 40px auto;
        }
        h1 {
            color: #D32F2F;
        }
        p {
            color: #D32F2F;
            font-size: 16px;
        }
        a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #D32F2F;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        a:hover {
            background-color: #B71C1C;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Error</h1>
        <?php
        if (isset($_SESSION['form_errors'])) {
            foreach ($_SESSION['form_errors'] as $error) {
                echo "<p>" . htmlspecialchars($error) . "</p>";
            }
            unset($_SESSION['form_errors']); // Clear the errors after displaying them
        }
        ?>
        <a href="createAgent.php">Go Back</a>
    </div>
</body>
</html>
