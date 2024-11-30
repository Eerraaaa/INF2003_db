<?php
session_start();
include '../lib/connection.php';
include '../lib/mongodb.php';  // Add MongoDB connection
include "../inc/agentnav.inc.php";

// Ensure the user is logged in and is an agent
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
    header("Location: unauthorized.php");
    exit();
}

// Initialize variables
$userID = (int)$_SESSION['userID'];
$agentID = null;
$listings = [];
$errorMsg = null;

try {
    // Get MongoDB connection
    $mongodb = MongoDBConnection::getInstance();

    // NOSQL: Get agent info from MongoDB instead of MySQL
    $agentInfo = $mongodb->findOne('agent', ['userID' => $userID]);

    if ($agentInfo) {
        $agentID = $agentInfo['agentID'];

        // Keep MySQL query for property listings since it involves multiple table joins
        $sqlListings = "
        SELECT Property.propertyID, Property.flatType, Property.resalePrice, Property.approvalStatus, 
               Location.town, Location.streetName, Location.block,
               Users.fname AS seller_fname, Users.lname AS seller_lname
        FROM Property
        JOIN Location ON Property.locationID = Location.locationID
        JOIN Users ON Property.sellerID = Users.userID
        WHERE Property.agentID = ?
        ";

        $stmtListings = $conn->prepare($sqlListings);
        if (!$stmtListings) {
            $errorMsg = "Error preparing the listings query: " . $conn->error;
        } else {
            $stmtListings->bind_param('i', $agentID);
            $stmtListings->execute();
            $resultListings = $stmtListings->get_result();

            if ($resultListings->num_rows > 0) {
                while ($row = $resultListings->fetch_assoc()) {
                    $listings[] = $row;
                }
            } else {
                $errorMsg = "You are not linked to any property listings.";
            }
            $stmtListings->close();
        }
    } else {
        $errorMsg = "No agent found for the logged-in user.";
    }
} catch (Exception $e) {
    $errorMsg = "Error: " . $e->getMessage();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="shortcut icon" type="image/x-icon"  href="../img/favicon.png">
    <meta charset="utf-8">
    <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css"
      integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <!--Font Awesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <!-- Bootstrap JS-->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <style>
        body {
            padding-top: 70px;
        }
    </style>
    <title>Agent Home</title>
</head>
<body>
    <div class="container mt-5">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

    <h2>Property Listings</h2>
        <?php if ($errorMsg): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php else: ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Agent Information</h5>
                    <p class="card-text"><strong>Area in Charge:</strong> <?php echo htmlspecialchars($agentInfo['areaInCharge']); ?></p>
                </div>
            </div>

            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Flat Type</th>
                        <th>Resale Price</th>
                        <th>Status</th>
                        <th>Town</th>
                        <th>Street Name</th>
                        <th>Block</th>
                        <th>Seller</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listings as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['flatType']); ?></td>
                            <td>$<?php echo number_format($row['resalePrice'], 0, '.', ','); ?></td>
                            <td><?php echo htmlspecialchars($row['approvalStatus']); ?></td>
                            <td><?php echo htmlspecialchars($row['town']); ?></td>
                            <td><?php echo htmlspecialchars($row['streetName']); ?></td>
                            <td><?php echo htmlspecialchars($row['block']); ?></td>
                            <td><?php echo htmlspecialchars($row['seller_fname'] . ' ' . $row['seller_lname']); ?></td>
                            <!-- Replace the Actions column TD in agent_home.php with this: -->
                            <td>
                                <?php if ($row['approvalStatus'] === 'pending'): ?>
                                    <a href="approve_listing.php?id=<?php echo $row['propertyID']; ?>" 
                                    class="btn btn-success btn-sm" 
                                    onclick="return confirm('Are you sure you want to approve this listing?');">
                                        Approve
                                    </a>
                                    <a href="reject_listing.php?id=<?php echo $row['propertyID']; ?>" 
                                    class="btn btn-danger btn-sm">
                                        Reject
                                    </a>
                                <?php elseif ($row['approvalStatus'] === 'approved'): ?>
                                    <span class="text-success">Approved</span>
                                    <a href="agentdelete_listing.php?id=<?php echo $row['propertyID']; ?>" 
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Are you sure you want to delete this listing? This action cannot be undone.');">
                                        Delete Listing
                                    </a>
                                <?php elseif ($row['approvalStatus'] === 'rejected'): ?>
                                    <span class="text-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>