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
    $sellerID = (int)$_SESSION['userID'];
    
    // NOSQL: Get all reviews by this seller
    $query = new MongoDB\Driver\Query(
        ['userID' => $sellerID],
        ['sort' => ['agentID' => 1, 'review_date' => -1]]
    );
    
    $reviewCursor = $mongodb->getConnection()->executeQuery("realestate_db.agentReview", $query);
    $reviews = [];
    $currentAgentID = null;
    $currentAgentReviews = [];
    
    foreach ($reviewCursor as $review) {
        $reviewData = json_decode(json_encode($review), true);
        
        if ($currentAgentID !== $reviewData['agentID']) {
            if ($currentAgentID !== null) {
                $reviews[] = $currentAgentReviews;
            }
            $currentAgentID = $reviewData['agentID'];
            
            // Get agent info from MongoDB
            $agentInfo = $mongodb->findOne('agent', ['agentID' => (int)$currentAgentID]);
            
            if ($agentInfo) {
                // Get agent name from MySQL
                $userQuery = "SELECT CONCAT(fname, ' ', lname) AS agent_name FROM Users WHERE userID = ?";
                $stmt = $conn->prepare($userQuery);
                $userID = (int)$agentInfo['userID'];
                $stmt->bind_param("i", $userID);
                $stmt->execute();
                $userResult = $stmt->get_result();
                $userDetails = $userResult->fetch_assoc();
                
                $currentAgentReviews = [
                    'agentID' => $currentAgentID,
                    'agent_name' => $userDetails ? $userDetails['agent_name'] : 'Unknown Agent',
                    'reviews' => []
                ];
            }
        }
        
        $currentAgentReviews['reviews'][] = $reviewData;
    }
    
    if ($currentAgentID !== null) {
        $reviews[] = $currentAgentReviews;
    }
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Reviews</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <style>
        body { padding-top: 70px; }
        .rating-stars { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">My Reviews for Agents</h2>
        <?php
        if (!empty($reviews)) {
            foreach ($reviews as $agentReviews) {
                echo "<h3 class='mt-4'>Reviews for " . htmlspecialchars($agentReviews['agent_name']) . "</h3>";
                echo "<div class='table-responsive mt-3'>";
                echo "<table class='table table-bordered table-striped'>";
                echo "<thead class='thead-dark'>";
                echo "<tr><th>Review</th><th>Rating</th><th>Review Date</th><th>Agent Response</th></tr>";
                echo "</thead><tbody>";
                
                foreach ($agentReviews['reviews'] as $review) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($review['review']) . "</td>";
                    echo "<td>" . htmlspecialchars($review['rating']) . "</td>";
                    echo "<td>" . htmlspecialchars($review['review_date']) . "</td>";
                    echo "<td>" . (empty($review['response']) || $review['response'] === 'NULL' ? 
                                 'No response yet' : htmlspecialchars($review['response'])) . "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table></div>";
            }
        } else {
            echo "<div class='alert alert-info'>You haven't left any reviews yet.</div>";
        }
        ?>
    </div>
</body>
</html>