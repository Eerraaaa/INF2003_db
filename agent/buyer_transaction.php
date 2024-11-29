<?php
session_start();
include '../lib/connection.php';
include '../lib/mongodb.php';
include "../inc/agentnav.inc.php";

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
    header("Location: unauthorized.php");
    exit();
}

$userID = $_SESSION['userID'];
$availableProperties = [];
$soldProperties = [];
$errorMsg = null;

try {
    $mongodb = MongoDBConnection::getInstance();
    
    // Get agentID from MongoDB agents collection
    $agentInfo = $mongodb->findOne('agent', ['userID' => (int)$userID]);
    
    if ($agentInfo) {
        $agentID = $agentInfo['agentID'];
        
        // Available properties query remains the same
        $sqlAvailable = "
            SELECT Property.propertyID, Property.flatType, Property.resalePrice, Location.town, Location.streetName, Location.block,
                COUNT(Cart.userID) AS buyerCount, Property.approvalStatus, Users.username AS sellerName
            FROM Property
            JOIN Location ON Property.locationID = Location.locationID
            JOIN Users ON Property.sellerID = Users.userID
            LEFT JOIN Cart ON Property.propertyID = Cart.propertyID
            WHERE Property.agentID = ? AND Property.availability = 'available'
            GROUP BY Property.propertyID, Property.flatType, Property.resalePrice, Location.town, Location.streetName, Location.block, Property.approvalStatus, Users.username";

        $stmtAvailable = $conn->prepare($sqlAvailable);
        $stmtAvailable->bind_param('i', $agentID);
        $stmtAvailable->execute();
        $resultAvailable = $stmtAvailable->get_result();
        while ($row = $resultAvailable->fetch_assoc()) {
            $availableProperties[] = $row;
        }
        $stmtAvailable->close();

        // For sold properties, first get all properties for this agent from MySQL
        $sqlSold = "
            SELECT p.propertyID, p.flatType, p.resalePrice, l.town, l.streetName, l.block,
                   s.username AS sellerName, p.sellerID
            FROM Property p
            JOIN Location l ON p.locationID = l.locationID
            JOIN Users s ON p.sellerID = s.userID
            WHERE p.agentID = ? AND p.availability = 'sold'";

        $stmtSold = $conn->prepare($sqlSold);
        $stmtSold->bind_param('i', $agentID);
        $stmtSold->execute();
        $resultSold = $stmtSold->get_result();

        // For each sold property
        while ($property = $resultSold->fetch_assoc()) {
            // Find matching transaction in MongoDB
            $transaction = $mongodb->findOne('transaction', [
                'propertyID' => (int)$property['propertyID']
            ]);

            if ($transaction) {
                // Get buyer name from Users table
                $sqlBuyer = "SELECT username AS buyerName FROM Users WHERE userID = ?";
                $stmtBuyer = $conn->prepare($sqlBuyer);
                $stmtBuyer->bind_param('i', $transaction['userID']);
                $stmtBuyer->execute();
                $buyerResult = $stmtBuyer->get_result();
                $buyer = $buyerResult->fetch_assoc();
                $stmtBuyer->close();

                // Add the buyer name to property info
                $soldProperties[] = array_merge($property, [
                    'buyerName' => $buyer['buyerName'],
                    'transactionDate' => $transaction['transactionDate'],
                    'totalPrice' => $transaction['totalPrice']
                ]);
            }
        }
        $stmtSold->close();
    }
} catch (Exception $e) {
    $errorMsg = "Error: " . $e->getMessage();
}

$conn->close();

// HTML part remains the same
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Buyer Transaction</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5" style="padding-top:100px;">
        <?php if ($errorMsg): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php else: ?>
            <!-- Available Properties Table -->
            <?php if (!empty($availableProperties)): ?>
                <h2>Available Properties</h2>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Flat Type</th>
                            <th>Resale Price</th>
                            <th>Town</th>
                            <th>Street Name</th>
                            <th>Block</th>
                            <th>Seller Name</th>
                            <th>Approval Status</th>
                            <th>Buyers (In Cart)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($availableProperties as $property): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($property['flatType']); ?></td>
                                <td><?php echo htmlspecialchars($property['resalePrice']); ?></td>
                                <td><?php echo htmlspecialchars($property['town']); ?></td>
                                <td><?php echo htmlspecialchars($property['streetName']); ?></td>
                                <td><?php echo htmlspecialchars($property['block']); ?></td>
                                <td><?php echo htmlspecialchars($property['sellerName']); ?></td>
                                <td><?php echo htmlspecialchars($property['approvalStatus']); ?></td>
                                <?php if ($property['approvalStatus'] == 'approved'): ?>
                                    <td><?php echo $property['buyerCount']; ?> buyers</td>
                                    <td>
                                        <a href="view_buyer.php?propertyID=<?php echo $property['propertyID']; ?>" class="btn btn-info">View Buyers</a>
                                    </td>
                                <?php else: ?>
                                    <td colspan="2">Needs Approval</td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No available properties found.</p>
            <?php endif; ?>

            <!-- Sold Properties Table -->
            <?php if (!empty($soldProperties)): ?>
                <h2>Sold Properties</h2>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Flat Type</th>
                            <th>Resale Price</th>
                            <th>Town</th>
                            <th>Street Name</th>
                            <th>Block</th>
                            <th>Seller Name</th>
                            <th>Buyer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($soldProperties as $property): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($property['flatType']); ?></td>
                                <td><?php echo htmlspecialchars($property['resalePrice']); ?></td>
                                <td><?php echo htmlspecialchars($property['town']); ?></td>
                                <td><?php echo htmlspecialchars($property['streetName']); ?></td>
                                <td><?php echo htmlspecialchars($property['block']); ?></td>
                                <td><?php echo htmlspecialchars($property['sellerName']); ?></td>
                                <td><?php echo htmlspecialchars($property['buyerName']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No sold properties found.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>