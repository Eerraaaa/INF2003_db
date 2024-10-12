<?php
session_start();
include '../lib/connection.php';
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

// Display success message if set
if (isset($_SESSION['success_message'])) {
    echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
    unset($_SESSION['success_message']); // Unset the message after displaying it
}

// Assuming the sellerID is the ID of the currently logged-in user
$sellerID = $_SESSION['userID'];

// Fetch the seller's property listings along with location, status, review information, and agent name
$sql = "SELECT 
            MIN(Property.propertyID) as propertyID,
            Property.flatType, 
            Property.resalePrice, 
            Property.approvalStatus, 
            MAX(Property.rejectReason) as rejectReason,
            MAX(Property.rejectComments) as rejectComments,
            Location.town, 
            Location.streetName, 
            Location.block,
            Property.agentID,
            MAX(CASE WHEN agentReview.agentReviewID IS NOT NULL THEN 1 ELSE 0 END) AS is_reviewed,
            Users.username AS agent_name
        FROM Property
        JOIN Location ON Property.locationID = Location.locationID
        LEFT JOIN agentReview ON Property.agentID = agentReview.agentID AND agentReview.userID = ?
        LEFT JOIN Agent ON Property.agentID = Agent.agentID
        LEFT JOIN Users ON Agent.userID = Users.userID
        WHERE Property.sellerID = ?
        GROUP BY Property.flatType, Property.resalePrice, Property.approvalStatus, Location.town, Location.streetName, Location.block, Property.agentID, Users.username";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Error preparing the query: " . $conn->error;
    exit();
}

$stmt->bind_param('ii', $sellerID, $sellerID);
$stmt->execute();
$result = $stmt->get_result();
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
        .status-approved { background-color: #d4edda; }
        .status-rejected { background-color: #f8d7da; }
    </style>
</head>
<body>
    <div class="container mt-5" style="padding-top:100px;">
        <h2 class="text-center">View My Listings</h2>
        <?php
        // Check if there are listings for the seller
        if ($result->num_rows > 0) {
            echo "<div class='container mt-5'>";
            echo "<table class='table table-bordered table-striped'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th>Flat Type</th>";
            echo "<th>Resale Price</th>";
            echo "<th>Status</th>";
            echo "<th>Town</th>";
            echo "<th>Street Name</th>";
            echo "<th>Block</th>";
            echo "<th>Agent Name</th>";
            echo "<th>Rejected Reason</th>";
            echo "<th>Rejected Comments</th>";
            echo "<th>Actions</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";

            // Fetch and display the rows
            while ($row = $result->fetch_assoc()) {
                $statusClass = '';
                if ($row['approvalStatus'] === 'approved') {
                    $statusClass = 'status-approved';
                } elseif ($row['approvalStatus'] === 'rejected') {
                    $statusClass = 'status-rejected';
                }
                
                echo "<tr class='$statusClass'>";
                echo "<td>" . htmlspecialchars($row['flatType']) . "</td>";
                echo "<td>" . htmlspecialchars($row['resalePrice']) . "</td>";
                echo "<td>" . htmlspecialchars($row['approvalStatus']) . "</td>";
                echo "<td>" . htmlspecialchars($row['town']) . "</td>";
                echo "<td>" . htmlspecialchars($row['streetName']) . "</td>";
                echo "<td>" . htmlspecialchars($row['block']) . "</td>";
                echo "<td>" . htmlspecialchars($row['agent_name']) . "</td>";
                echo "<td>" . ($row['approvalStatus'] === 'rejected' ? htmlspecialchars($row['rejectReason']) : '') . "</td>";
                echo "<td>" . ($row['approvalStatus'] === 'rejected' ? htmlspecialchars($row['rejectComments']) : '') . "</td>";
                echo "<td>";
                if ($row['approvalStatus'] === 'approved') {
                    // If approved, show Review Agent button or Reviewed status
                    if ($row['is_reviewed'] == 0) {
                        echo "<a href='create_review.php?agentID=" . $row['agentID'] . "&propertyID=" . $row['propertyID'] . "' class='btn btn-success'>Review Agent</a> ";
                    } else {
                        echo "<span class='text-success'>Reviewed</span>";
                    }
                } elseif ($row['approvalStatus'] === 'pending') {
                    // If pending, show Update and Delete buttons
                    echo "<a href='update_listing.php?id=" . $row['propertyID'] . "' class='btn btn-primary'>Update</a> ";
                    echo "<a href='delete_listing.php?id=" . $row['propertyID'] . "' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this listing?\");'>Delete</a>";
                }
                echo "</td>";                               
                echo "</tr>";
            }

            echo "</tbody>";
            echo "</table>";
            echo "</div>";
        } else {
            echo "<div class='alert alert-info'>You have no property listings.</div>";
        }

        $stmt->close();
        $conn->close();
        ?>
    </div>
</body>
</html>