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

// Organize properties by status
$pendingListings = [];
$approvedListings = [];
$rejectedListings = [];

foreach ($properties as $property) {
    switch ($property['approvalStatus']) {
        case 'pending':
            $pendingListings[] = $property;
            break;
        case 'approved':
            $approvedListings[] = $property;
            break;
        case 'rejected':
            $rejectedListings[] = $property;
            break;
    }
}

function renderListingsTable($listings, $tableTitle) {
    if (empty($listings)) {
        return "<div class='alert alert-info'>No {$tableTitle} listings.</div>";
    }
    
    $html = "
    <div class='card mb-4'>
        <div class='card-header'>
            <h3 class='mb-0'>{$tableTitle} Listings</h3>
        </div>
        <div class='card-body'>
            <div class='table-responsive'>
                <table class='table table-bordered table-striped mb-0'>
                    <thead>
                        <tr>
                            <th>Flat Type</th>
                            <th>Resale Price</th>
                            <th>Town</th>
                            <th>Street Name</th>
                            <th>Block</th>
                            <th>Agent Name</th>";
    
    // Add rejection columns only for rejected listings
    if ($tableTitle === 'Rejected') {
        $html .= "
                            <th>Rejected Reason</th>
                            <th>Rejected Comments</th>";
    }
    
    $html .= "
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>";
    
    foreach ($listings as $row) {
        $statusClass = '';
        if ($tableTitle === 'Approved') {
            $statusClass = 'table-success';
        } elseif ($tableTitle === 'Rejected') {
            $statusClass = 'table-danger';
        } elseif ($tableTitle === 'Pending') {
            $statusClass = 'table-warning';
        }

        $html .= "<tr class='{$statusClass}'>";
        $html .= "<td>" . htmlspecialchars($row['flatType']) . "</td>";
        $html .= "<td>$" . number_format($row['resalePrice'], 0, '.', ',') . "</td>";
        $html .= "<td>" . htmlspecialchars($row['town']) . "</td>";
        $html .= "<td>" . htmlspecialchars($row['streetName']) . "</td>";
        $html .= "<td>" . htmlspecialchars($row['block']) . "</td>";
        $html .= "<td>" . htmlspecialchars($row['agent_name']) . "</td>";
        
        // Add rejection details only for rejected listings
        if ($tableTitle === 'Rejected') {
            $html .= "<td>" . htmlspecialchars($row['rejectReason']) . "</td>";
            $html .= "<td>" . htmlspecialchars($row['rejectComments']) . "</td>";
        }
        
        $html .= "<td>";
        
        // Action buttons based on status
        if ($row['approvalStatus'] === 'approved') {
            if (!$row['is_reviewed']) {
                $html .= "<a href='create_review.php?agentID={$row['agentID']}&propertyID={$row['propertyID']}' 
                            class='btn btn-success btn-sm'>Review Agent</a>";
            } else {
                $html .= "<span class='text-success'>Reviewed</span>";
            }
        } elseif ($row['approvalStatus'] === 'pending') {
            $html .= "<a href='update_listing.php?id={$row['propertyID']}' 
                        class='btn btn-primary btn-sm'>Update</a>
                     <a href='delete_listing.php?id={$row['propertyID']}' 
                        class='btn btn-danger btn-sm'
                        onclick='return confirm(\"Are you sure you want to delete this listing?\");'>Delete</a>";
        } elseif ($row['approvalStatus'] === 'rejected') {
            $html .= "<a href='update_listing.php?id={$row['propertyID']}&resubmit=true' 
                        class='btn btn-warning btn-sm'>Resubmit Listing</a>
                     <a href='delete_listing.php?id={$row['propertyID']}' 
                        class='btn btn-danger btn-sm'
                        onclick='return confirm(\"Are you sure you want to delete this listing?\");'>Delete</a>";
        }
        
        $html .= "</td></tr>";
    }
    
    $html .= "
                    </tbody>
                </table>
            </div>
        </div>
    </div>";
    
    return $html;
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
        .card-header { 
            background-color: #f8f9fa; 
        }
        .pending-section .card-header { 
            border-left: 5px solid #ffc107; 
        }
        .approved-section .card-header { 
            border-left: 5px solid #28a745; 
        }
        .rejected-section .card-header { 
            border-left: 5px solid #dc3545; 
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .btn-sm {
            margin: 2px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">View My Listings</h2>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class='alert alert-success'><?php echo $_SESSION['success_message']; ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (empty($pendingListings) && empty($approvedListings) && empty($rejectedListings)): ?>
            <div class='alert alert-info'>You have no property listings.</div>
        <?php else: ?>
            <!-- Pending Listings Section -->
            <div class="pending-section">
                <?php echo renderListingsTable($pendingListings, 'Pending'); ?>
            </div>

            <!-- Approved Listings Section -->
            <div class="approved-section">
                <?php echo renderListingsTable($approvedListings, 'Approved'); ?>
            </div>

            <!-- Rejected Listings Section -->
            <div class="rejected-section">
                <?php echo renderListingsTable($rejectedListings, 'Rejected'); ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>