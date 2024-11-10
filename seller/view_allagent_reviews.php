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
    
    // NOSQL: Get all agents with their reviews
    $query = new MongoDB\Driver\Query(
        [],
        ['sort' => ['agentID' => 1]]
    );
    
    // Get all agents
    $agentCursor = $mongodb->getConnection()->executeQuery("realestate_db.agent", $query);
    $agents = [];
    $processedAgents = []; // Keep track of processed agents to avoid duplicates
    
    foreach ($agentCursor as $agent) {
        $agentID = (int)$agent->agentID;
        
        // Skip if we've already processed this agent
        if (isset($processedAgents[$agentID])) {
            continue;
        }
        
        // Get agent's name from MySQL
        $userQuery = "SELECT CONCAT(fname, ' ', lname) AS agent_name FROM Users WHERE userID = ?";
        $stmt = $conn->prepare($userQuery);
        $userID = (int)$agent->userID;
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $userResult = $stmt->get_result();
        $userDetails = $userResult->fetch_assoc();
        
        if ($userDetails) {
            // Get reviews for this agent
            $reviewQuery = new MongoDB\Driver\Query(
                ['agentID' => $agentID],
                ['sort' => ['review_date' => -1]]
            );
            $reviewCursor = $mongodb->getConnection()->executeQuery("realestate_db.agentReview", $reviewQuery);
            
            // Calculate average rating
            $reviews = [];
            $totalRating = 0;
            $reviewCount = 0;
            
            foreach ($reviewCursor as $review) {
                $reviews[] = json_decode(json_encode($review), true);
                $totalRating += $review->rating;
                $reviewCount++;
            }
            
            if ($reviewCount > 0) {
                $agents[] = [
                    'agentID' => $agentID,
                    'agent_name' => $userDetails['agent_name'],
                    'reviews' => $reviews,
                    'avg_rating' => $totalRating / $reviewCount
                ];
                
                // Mark this agent as processed
                $processedAgents[$agentID] = true;
            }
        }
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
    <title>All Agent Reviews</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        body { padding-top: 70px; }
        .rating-stars { color: #ffc107; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">All Agent Reviews</h2>
        <?php
        function displayStars($rating) {
            $output = '';
            for ($i = 1; $i <= floor($rating); $i++) {
                $output .= '<i class="fas fa-star"></i>';
            }
            if ($rating - floor($rating) >= 0.5) {
                $output .= '<i class="fas fa-star-half-alt"></i>';
            }
            for ($i = ceil($rating); $i < 5; $i++) {
                $output .= '<i class="far fa-star"></i>';
            }
            return '<span class="rating-stars">' . $output . '</span>';
        }

        if (!empty($agents)) {
            foreach ($agents as $agent) {
                echo "<h3 class='mt-4'>Reviews for " . htmlspecialchars($agent['agent_name']) . "</h3>";
                echo "<p>Average Rating: " . displayStars($agent['avg_rating']) . 
                     " (" . number_format($agent['avg_rating'], 2) . " / 5)</p>";
                
                echo "<div class='table-responsive mt-3'>";
                echo "<table class='table table-bordered table-striped'>";
                echo "<thead class='thead-dark'>";
                echo "<tr><th>Review</th><th>Rating</th><th>Review Date</th><th>Agent Response</th></tr>";
                echo "</thead><tbody>";
                
                foreach ($agent['reviews'] as $review) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($review['review']) . "</td>";
                    echo "<td>" . displayStars($review['rating']) . "</td>";
                    echo "<td>" . htmlspecialchars($review['review_date']) . "</td>";
                    echo "<td>" . (empty($review['response']) || $review['response'] === 'NULL' ? 
                                 'No response yet' : htmlspecialchars($review['response'])) . "</td>";
                    echo "</tr>";
                }
                echo "</tbody></table></div>";
            }
        } else {
            echo "<div class='alert alert-info'>There are no agent reviews available.</div>";
        }
        ?>
    </div>
</body>
</html>