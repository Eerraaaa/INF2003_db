<?php
session_start();
include '../lib/connection.php';
include '../inc/head.inc.php';

// Check if user is logged in and is a buyer
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'buyer') {
    header("Location: ../unauthorized.php");
    exit();
}

$userID = $_SESSION['userID'];

// Fetch purchased properties
$sql = "SELECT p.propertyID, p.flatType, p.resalePrice, p.transactionDate, l.town 
        FROM Property p 
        JOIN Location l ON p.locationID = l.locationID 
        WHERE p.sellerID = ? AND p.availability = 'sold'
        ORDER BY p.transactionDate DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("An error occurred. Please try again later.");
}

$stmt->bind_param("i", $userID);
if (!$stmt->execute()) {
    die("An error occurred while fetching your purchase history.");
}

$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase History</title>
    
</head>
<body>
    <div class="container">
        <h1>Your Transaction History</h1>
        <?php if ($result->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Property ID</th>
                        <th>Flat Type</th>
                        <th>Location</th>
                        <th>Transaction Price</th>
                        <th>Transaction Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['propertyID']); ?></td>
                            <td><?php echo htmlspecialchars($row['flatType']); ?></td>
                            <td><?php echo htmlspecialchars($row['town']); ?></td>
                            <td>$<?php echo number_format($row['resalePrice'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['transactionDate']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have no transaction history.</p>
        <?php endif; ?>
        <a href="../index.php" class="btn btn-secondary">Back to Home</a>
    </div>
</body>
</html>