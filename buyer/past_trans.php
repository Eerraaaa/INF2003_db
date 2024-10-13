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

// Fetch purchased properties along with agent details
$sql = "SELECT p.propertyID, p.flatType, p.resalePrice, t.transactionDate, l.town, 
               a.agentID, ua.fname AS agent_fname, ua.lname AS agent_lname, ua.phone_number AS agent_phone
        FROM Property p
        JOIN Location l ON p.locationID = l.locationID
        LEFT JOIN Agent a ON p.agentID = a.agentID
        LEFT JOIN Users ua ON a.userID = ua.userID  -- Join to get agent's details
        LEFT JOIN Transaction t ON p.propertyID = t.propertyID -- Join the transaction table
        LEFT JOIN Users ub ON t.userID = ub.userID  -- Join to get buyer's details
        WHERE t.userID = ? AND p.availability = 'sold'
        ORDER BY t.transactionDate DESC";




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
                        <th>Agent Name</th>
                        <th>Agent Phone</th>
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
                            <td>
                                <?php
                                if ($row['agent_fname'] && $row['agent_lname']) {
                                    echo htmlspecialchars($row['agent_fname']) . ' ' . htmlspecialchars($row['agent_lname']);
                                } else {
                                    echo "N/A";
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['agent_phone'] ? $row['agent_phone'] : 'N/A'); ?></td>
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
