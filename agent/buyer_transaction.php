<?php
session_start();
include '../lib/connection.php';
include "../inc/agentnav.inc.php";

// Ensure the user is logged in and is an agent
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
    header("Location: unauthorized.php");
    exit();
}

// Get the logged-in userID from the session
$userID = $_SESSION['userID']; 

// Fetch the agentID associated with this userID
$sqlAgent = "SELECT agentID FROM Agent WHERE userID = ?";
$stmtAgent = $conn->prepare($sqlAgent);
$stmtAgent->bind_param("i", $userID);

if ($stmtAgent->execute()) {
    $stmtAgent->bind_result($agentID);
    $stmtAgent->fetch();  // Fetch the agentID
    echo "Logged in Agent ID: " . $agentID . "<br>";
} else {
    echo "Error fetching agentID: " . $conn->error;
}

$stmtAgent->close();

// Initialize arrays to hold available and sold properties
$availableProperties = [];
$soldProperties = [];
$errorMsg = null;

// Fetch available properties linked to the agent
$sqlAvailable = "
    SELECT Property.propertyID, Property.flatType, Property.resalePrice, Location.town, Location.streetName, Location.block,
        COUNT(Cart.userID) AS buyerCount, Property.approvalStatus, Users.username AS sellerName
    FROM Property
    JOIN Location ON Property.locationID = Location.locationID
    JOIN Users ON Property.sellerID = Users.userID
    LEFT JOIN Cart ON Property.propertyID = Cart.propertyID
    WHERE Property.agentID = ? AND Property.availability = 'available'
    GROUP BY Property.propertyID, Property.flatType, Property.resalePrice, Location.town, Location.streetName, Location.block, Property.approvalStatus, Users.username;
";

$stmtAvailable = $conn->prepare($sqlAvailable);
if ($stmtAvailable) {
    $stmtAvailable->bind_param('i', $agentID);
    
    // Check if the query executes correctly
    if ($stmtAvailable->execute()) {
        $resultAvailable = $stmtAvailable->get_result();
        while ($row = $resultAvailable->fetch_assoc()) {
            $availableProperties[] = $row; // Store available properties
        }
    } else {
        // If execution fails, print error
        echo "Error executing available properties query: " . $stmtAvailable->error;
    }
    
    $stmtAvailable->close();
} else {
    // If preparation fails, print error
    echo "Error preparing available properties query: " . $conn->error;
}

// Fetch sold properties linked to the agent
$sqlSold = "
    SELECT Property.propertyID, Property.flatType, Property.resalePrice, Location.town, Location.streetName, Location.block,
        Transaction.userID, Buyer.username AS buyerName, Seller.username AS sellerName
    FROM Property
    JOIN Location ON Property.locationID = Location.locationID
    JOIN Transaction ON Property.propertyID = Transaction.propertyID
    JOIN Users AS Buyer ON Transaction.userID = Buyer.userID
    JOIN Users AS Seller ON Property.sellerID = Seller.userID
    WHERE Property.agentID = ? AND Property.availability = 'sold';
";

$stmtSold = $conn->prepare($sqlSold);
if ($stmtSold) {
    $stmtSold->bind_param('i', $agentID);
    
    // Check if the query executes correctly
    if ($stmtSold->execute()) {
        $resultSold = $stmtSold->get_result();
        while ($row = $resultSold->fetch_assoc()) {
            $soldProperties[] = $row; // Store sold properties
        }
    } else {
        // If execution fails, print error
        echo "Error executing sold properties query: " . $stmtSold->error;
    }
    
    $stmtSold->close();
} else {
    // If preparation fails, print error
    echo "Error preparing sold properties query: " . $conn->error;
}

$conn->close();
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

    <!-- AVAILABLE PROPERTIES TABLE -->
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

                    <!-- Check the approval status -->
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

    <!-- SOLD PROPERTIES TABLE -->
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

    </div>
</body>
</html>