<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Successful</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- Your CSS file -->
</head>
<body>
    <div class="container">
        <h1>Purchase Successful!</h1>
        <p>Thank you for your purchase!</p>
        <p>Your total amount is: $<?php echo htmlspecialchars($_GET['total_price']); ?></p>
        <a href="../index.php" class="btn btn-primary">Continue Shopping</a>
    </div>
</body>
</html>
