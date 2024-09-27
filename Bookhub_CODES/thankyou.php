<!DOCTYPE html>
<html lang="en">
<head>
<?php
            include "inc/nav.inc.php";
            include "inc/headproduct.inc.php";
            include 'lib/connection.php'; // Ensure you include your database connection here

            session_start(); // Start the session if not already started
        // Assuming you have stored order number and transaction id in session or passed them via GET/POST
$orderNumber = $_SESSION['order_number'] ?? 'Not Available';
$transactionId = $_SESSION['transaction_id'] ?? 'Not Available';
        ?> 
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Your Purchase!</title>
    <link rel="stylesheet" href="path_to_your_stylesheet.css"> <!-- Optional, if you have a stylesheet -->
</head>
<body>
    <div class="thank-you-container">
        <h1>Thank You for Your Purchase!</h1>
        <p>Your transaction has been completed, and a receipt for your purchase has been emailed to you.</p>
        <!-- <p>You may log into your account at <a href="http://www.paypal.com" target="_blank">www.paypal.com</a> to view details of this transaction.</p> -->
        <div class="details">
        <p><strong>Order Number:</strong> <span id="order-number"><?php echo htmlspecialchars($orderNumber); ?></span></p>
<p><strong>Transaction ID:</strong> <span id="transaction-id"><?php echo htmlspecialchars($transactionId); ?></span></p>
        </div>
        <div class="actions">
            <a href="index.php" class="button">Return to Home</a>
        </div>
    </div>

    <script>
        // If you have some JavaScript to run
    </script>
</body>
</html>
