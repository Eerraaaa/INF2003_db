<?php
session_start();
include '../lib/connection.php';
include '../lib/mongodb.php';  // Add MongoDB connection
include "../inc/sellernav.inc.php";

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in and is a seller
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: unauthorized.php");
    exit();
}

try {
    $mongodb = MongoDBConnection::getInstance();
} catch (Exception $e) {
    die("Could not connect to MongoDB");
}

// Display success message if set
if (isset($_SESSION['success_message'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
    unset($_SESSION['success_message']);
}

$sellerID = (int)$_SESSION['userID'];

// Fetch property listings from MySQL
$sql = "SELECT 
            Property.propertyID,
            Property.flatType, 
            Property.resalePrice, 
            Property.approvalStatus, 
            Property.rejectReason,
            Property.rejectComments,
            Property.agentID,
            Location.town, 
            Location.streetName, 
            Location.block
        FROM Property
        JOIN Location ON Property.locationID = Location.locationID
        WHERE Property.sellerID = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing the query: " . $conn->error);
}

$stmt->bind_param('i', $sellerID);
$stmt->execute();
$result = $stmt->get_result();
$properties = [];

while ($row = $result->fetch_assoc()) {
    // NOSQL: Get agent info from MongoDB
    $agentInfo = $mongodb->findOne('agent', ['agentID' => (int)$row['agentID']]);
    if ($agentInfo) {
        // Get agent's username from MySQL Users table
        $userStmt = $conn->prepare("SELECT username FROM Users WHERE userID = ?");
        $userStmt->bind_param('i', $agentInfo['userID']);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $userInfo = $userResult->fetch_assoc();
        $row['agent_name'] = $userInfo ? $userInfo['username'] : 'Unknown';
        $userStmt->close();
    } else {
        $row['agent_name'] = 'Unknown';
    }

    // NOSQL: Check if agent has been reviewed by this seller
    try {
        $reviewQuery = new MongoDB\Driver\Query([
            'agentID' => (int)$row['agentID'],
            'userID' => $sellerID
        ]);
        $cursor = $mongodb->getConnection()->executeQuery("realestate_db.agentReview", $reviewQuery);
        $reviews = iterator_to_array($cursor);
        $row['is_reviewed'] = !empty($reviews);
    } catch (Exception $e) {
        error_log("Error checking reviews: " . $e->getMessage());
        $row['is_reviewed'] = false;
    }

    $properties[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seller Home</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <style>
        body {
            padding-top: 70px;
        }
        .status-approved { background-color: #d4edda; }
        .status-rejected { background-color: #f8d7da; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">View My Listings</h2>
        <?php if (!empty($properties)): ?>
            <div class="table-responsive">
                <table class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th>Flat Type</th>
                            <th>Resale Price</th>
                            <th>Status</th>
                            <th>Town</th>
                            <th>Street Name</th>
                            <th>Block</th>
                            <th>Agent Name</th>
                            <th>Rejected Reason</th>
                            <th>Rejected Comments</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($properties as $row): 
                            $statusClass = $row['approvalStatus'] === 'approved' ? 'status-approved' : 
                                         ($row['approvalStatus'] === 'rejected' ? 'status-rejected' : '');
                        ?>
                            <tr class="<?php echo $statusClass; ?>">
                                <td><?php echo htmlspecialchars($row['flatType']); ?></td>
                                <td>$<?php echo number_format($row['resalePrice'], 0, '.', ','); ?></td>
                                <td><?php echo htmlspecialchars($row['approvalStatus']); ?></td>
                                <td><?php echo htmlspecialchars($row['town']); ?></td>
                                <td><?php echo htmlspecialchars($row['streetName']); ?></td>
                                <td><?php echo htmlspecialchars($row['block']); ?></td>
                                <td><?php echo htmlspecialchars($row['agent_name']); ?></td>
                                <td><?php echo $row['approvalStatus'] === 'rejected' ? htmlspecialchars($row['rejectReason']) : ''; ?></td>
                                <td><?php echo $row['approvalStatus'] === 'rejected' ? htmlspecialchars($row['rejectComments']) : ''; ?></td>
                                <td>
                                <?php if ($row['approvalStatus'] === 'approved'): ?>
                                    <?php if (!$row['is_reviewed']): ?>
                                        <a href='create_review.php?agentID=<?php echo $row['agentID']; ?>&propertyID=<?php echo $row['propertyID']; ?>' 
                                        class='btn btn-success btn-sm'>Review Agent</a>
                                    <?php else: ?>
                                        <span class='text-success'>Reviewed</span>
                                    <?php endif; ?>
                                <?php elseif ($row['approvalStatus'] === 'pending'): ?>
                                    <a href='update_listing.php?id=<?php echo $row['propertyID']; ?>' 
                                    class='btn btn-primary btn-sm'>Update</a>
                                    <a href='delete_listing.php?id=<?php echo $row['propertyID']; ?>' 
                                    class='btn btn-danger btn-sm'
                                    onclick='return confirm("Are you sure you want to delete this listing?");'>Delete</a>
                                <?php elseif ($row['approvalStatus'] === 'rejected'): ?>
                                    <a href='update_listing.php?id=<?php echo $row['propertyID']; ?>&resubmit=true' 
                                    class='btn btn-warning btn-sm'>Resubmit Listing</a>
                                    <a href='delete_listing.php?id=<?php echo $row['propertyID']; ?>' 
                                    class='btn btn-danger btn-sm'
                                    onclick='return confirm("Are you sure you want to delete this listing?");'>Delete</a>
                                <?php endif; ?>
                            </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class='alert alert-info'>You have no property listings.</div>
        <?php endif; ?>
    </div>
</body>
</html>