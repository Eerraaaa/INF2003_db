<?php
session_start();
include '../lib/connection.php';
include "../inc/agentnav.inc.php";

// Ensure the user is logged in and is an agent
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
    header("Location: unauthorized.php");
    exit();
}

// Initialize variables
$userID = $_SESSION['userID']; // Get the userID from the session
$agentID = null;
$listings = [];
$errorMsg = null;

// First, fetch the agentID using the userID from the session
$sqlAgent = "SELECT agentID FROM Agent WHERE userID = ?";
$stmtAgent = $conn->prepare($sqlAgent);
if (!$stmtAgent) {
    $errorMsg = "Error preparing the agent query: " . $conn->error;
} else {
    $stmtAgent->bind_param('i', $userID); // Bind userID as an integer
    $stmtAgent->execute();
    $resultAgent = $stmtAgent->get_result();

    if ($resultAgent->num_rows > 0) {
        $agentRow = $resultAgent->fetch_assoc();
        $agentID = $agentRow['agentID']; // Get the agentID from the result
    } else {
        $errorMsg = "No agent found for the logged-in user.";
    }
    $stmtAgent->close();
}

// Fetch the listings linked to the agent if the agentID is found
if ($agentID) {
    $sqlListings = "
    SELECT Property.propertyID, Property.flatType, Property.resalePrice, Property.approvalStatus, 
           Location.town, Location.streetName, Location.block
    FROM Property
    JOIN Location ON Property.locationID = Location.locationID
    WHERE Property.agentID = ?
    ";

    $stmtListings = $conn->prepare($sqlListings);
    if (!$stmtListings) {
        $errorMsg = "Error preparing the listings query: " . $conn->error;
    } else {
        $stmtListings->bind_param('i', $agentID); // Bind agentID as an integer
        $stmtListings->execute();
        $resultListings = $stmtListings->get_result();

        if ($resultListings->num_rows > 0) {
            // Fetch all results
            while ($row = $resultListings->fetch_assoc()) {
                $listings[] = $row; // Store the results in the $listings array
            }
        } else {
            $errorMsg = "You are not linked to any property listings.";
        }
        $stmtListings->close();
    }
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
    <!-- ScrollReveal.js library -->
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="../js/home.js"></script>
    <title>Agent Home</title>
</head>
<body>
    <div class="container mt-5" style="padding-top:100px;">
        <h2>Property Listings</h2>
        <?php if ($errorMsg): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php else: ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Flat Type</th>
                        <th>Resale Price</th>
                        <th>Status</th>
                        <th>Town</th>
                        <th>Street Name</th>
                        <th>Block</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listings as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['flatType']); ?></td>
                            <td><?php echo htmlspecialchars($row['resalePrice']); ?></td>
                            <td><?php echo htmlspecialchars($row['approvalStatus']); ?></td>
                            <td><?php echo htmlspecialchars($row['town']); ?></td>
                            <td><?php echo htmlspecialchars($row['streetName']); ?></td>
                            <td><?php echo htmlspecialchars($row['block']); ?></td>
                            <td>
                                <?php if ($row['approvalStatus'] === 'pending'): ?>
                                    <a href="approve_listing.php?id=<?php echo $row['propertyID']; ?>" 
                                    class="btn btn-success" 
                                    onclick="return confirm('Are you sure you want to approve this listing?');">
                                        Approve
                                    </a>
                                    <a href="reject_listing.php?id=<?php echo $row['propertyID']; ?>" 
                                    class="btn btn-danger">
                                        Reject
                                    </a>
                                <?php elseif ($row['approvalStatus'] === 'approved'): ?>
                                    <span class="text-success">Approved</span>
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