<?php
session_start();
include '../lib/connection.php';
include '../lib/mongodb.php';
include '../inc/head.inc.php';

// Check if user is logged in and is a buyer
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'buyer') {
    header("Location: ../unauthorized.php");
    exit();
}

$userID = (int)$_SESSION['userID'];

try {
    $mongodb = MongoDBConnection::getInstance();
    
    // First get transactions from MongoDB
    $query = new MongoDB\Driver\Query(
        ['userID' => $userID],
        ['sort' => ['transactionDate' => -1]]
    );
    
    $cursor = $mongodb->getConnection()->executeQuery("realestate_db.transaction", $query);
    $transactions = [];
    
    foreach ($cursor as $transaction) {
        $transData = json_decode(json_encode($transaction), true);
        
        // Get property details from MySQL
        $propertyStmt = $conn->prepare("
            SELECT p.propertyID, p.flatType, p.agentID, l.town 
            FROM Property p
            JOIN Location l ON p.locationID = l.locationID
            WHERE p.propertyID = ?
        ");
        $propertyID = (int)$transData['propertyID'];
        $propertyStmt->bind_param("i", $propertyID);
        $propertyStmt->execute();
        $propertyResult = $propertyStmt->get_result();
        $propertyData = $propertyResult->fetch_assoc();
        
        if ($propertyData) {
            // Get agent details from MongoDB and MySQL
            $agentQuery = new MongoDB\Driver\Query(
                ['agentID' => (int)$propertyData['agentID']]
            );
            $agentCursor = $mongodb->getConnection()->executeQuery("realestate_db.agent", $agentQuery);
            $agentData = current($agentCursor->toArray());
            
            if ($agentData) {
                $agentInfo = json_decode(json_encode($agentData), true);
                
                // Get agent's user details from MySQL
                $agentUserStmt = $conn->prepare("
                    SELECT fname, lname, phone_number 
                    FROM Users 
                    WHERE userID = ?
                ");
                $agentUserID = (int)$agentInfo['userID'];
                $agentUserStmt->bind_param("i", $agentUserID);
                $agentUserStmt->execute();
                $agentUserResult = $agentUserStmt->get_result();
                $agentUserData = $agentUserResult->fetch_assoc();
            }
            
            // Combine all data
            $transactions[] = [
                'propertyID' => $propertyData['propertyID'],
                'flatType' => $propertyData['flatType'],
                'town' => $propertyData['town'],
                'resalePrice' => $transData['totalPrice'],
                'transactionDate' => $transData['transactionDate'],
                'agent_fname' => $agentUserData['fname'] ?? 'N/A',
                'agent_lname' => $agentUserData['lname'] ?? '',
                'agent_phone' => $agentUserData['phone_number'] ?? 'N/A'
            ];
        }
    }
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
        <?php if (!empty($transactions)): ?>
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
                    <?php foreach ($transactions as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['propertyID']); ?></td>
                            <td><?php echo htmlspecialchars($row['flatType']); ?></td>
                            <td><?php echo htmlspecialchars($row['town']); ?></td>
                            <td>$<?php echo number_format($row['resalePrice'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['transactionDate']); ?></td>
                            <td>
                                <?php
                                if ($row['agent_fname'] !== 'N/A') {
                                    echo htmlspecialchars($row['agent_fname']) . ' ' . htmlspecialchars($row['agent_lname']);
                                } else {
                                    echo "N/A";
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['agent_phone']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have no transaction history.</p>
        <?php endif; ?>
        <a href="../index.php" class="btn btn-secondary">Back to Home</a>
    </div>
</body>
</html>

<?php
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>