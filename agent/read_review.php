<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
    header("Location: unauthorized.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Reviews</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.png">
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
</head>
<body>
<?php include "../inc/agentnav.inc.php"; ?>

<div class="container mt-5">
    <h2 class="text-center">My Reviews</h2>
    
    <?php
    // Include both MySQL and MongoDB connections
    include '../lib/connection.php';  // MySQL connection
    include '../lib/mongodb.php';     // MongoDB connection

    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }

    try {
        // Initialize MongoDB connection
        $mongodb = MongoDBConnection::getInstance();
        
        // NOSQL: Get agent info from MongoDB 'agent' collection using userID
        $userID = (int)$_SESSION['userID'];
        $agentInfo = $mongodb->findOne('agent', ['userID' => $userID]);
        
        if ($agentInfo) {
            $agentID = (int)$agentInfo['agentID'];
            
            // NOSQL: Get all reviews from MongoDB 'agentReview' collection
            $query = new MongoDB\Driver\Query(
                ['agentID' => $agentID],
                ['sort' => ['review_date' => -1]]  // MongoDB sorting
            );
            
            // NOSQL: Execute query on MongoDB
            $cursor = $mongodb->getConnection()->executeQuery("realestate_db.agentReview", $query);
            $hasReviews = false;

            echo "<table class='table table-striped table-bordered mt-4'>
                    <thead class='thead-dark'>
                        <tr>
                            <th>Seller Name</th>
                            <th>Review</th>
                            <th>Rating</th>
                            <th>Review Date</th>
                            <th>Response</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>";

            // NOSQL: Iterate through MongoDB results
            foreach ($cursor as $review) {
                $hasReviews = true;
                // Convert MongoDB object to array
                $reviewData = json_decode(json_encode($review), true);
                
                // SQL: Get seller name from MySQL Users table
                // (This is the only MySQL query in the loop)
                $userQuery = "SELECT CONCAT(fname, ' ', lname) AS seller_name 
                            FROM Users WHERE userID = ?";
                $stmt = $conn->prepare($userQuery);
                $sellerID = (int)$reviewData['userID'];
                $stmt->bind_param("i", $sellerID);
                $stmt->execute();
                $userResult = $stmt->get_result();
                $userDetails = $userResult->fetch_assoc();
                $sellerName = $userDetails ? $userDetails['seller_name'] : 'Unknown User';

                // Format data from MongoDB
                $reviewDate = date('Y-m-d H:i:s', strtotime($reviewData['review_date']));
                $responseStatus = ($reviewData["response"] === "NULL" || empty($reviewData["response"])) 
                    ? "No response yet" 
                    : htmlspecialchars($reviewData["response"]);

                echo "<tr>
                        <td>" . htmlspecialchars($sellerName) . "</td>
                        <td>" . htmlspecialchars($reviewData["review"]) . "</td>
                        <td>" . htmlspecialchars($reviewData["rating"]) . "</td>
                        <td>" . htmlspecialchars($reviewDate) . "</td>
                        <td>" . $responseStatus . "</td>
                        <td>";

                // Check response status from MongoDB data
                if ($reviewData["response"] === "NULL" || empty($reviewData["response"])) {
                    echo "<a href='respond_review.php?id=" . $reviewData["agentReviewID"] . "' 
                            class='btn btn-primary btn-sm'>Respond</a>";
                } else {
                    echo "Response submitted";
                }

                echo "</td></tr>";
            }
            echo "</tbody></table>";

            if (!$hasReviews) {
                echo "<div class='alert alert-info text-center'>You have no reviews yet.</div>";
            }

        } else {
            echo "<div class='alert alert-danger text-center'>Error: Agent not found.</div>";
        }
        
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo "<div class='alert alert-danger text-center'>An error occurred while fetching the reviews.</div>";
    }
    ?>
</div>

</body>
</html>