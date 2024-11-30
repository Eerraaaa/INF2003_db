<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: unauthorized.php");
    exit();
}

include '../lib/connection.php';
include '../lib/mongodb.php';  // Add MongoDB connection
include "../inc/sellernav.inc.php";

// Get the agentID and propertyID from the URL
$agentID = isset($_GET['agentID']) ? intval($_GET['agentID']) : 0;
$propertyID = isset($_GET['propertyID']) ? intval($_GET['propertyID']) : 0;

// Validate agentID and propertyID
if ($agentID === 0 || $propertyID === 0) {
    header("Location: seller_home.php");
    exit();
}

try {
    $mongodb = MongoDBConnection::getInstance();

    // Check if property has already been reviewed
    $reviewQuery = new MongoDB\Driver\Query([
        'propertyID' => $propertyID
    ]);
    $cursor = $mongodb->getConnection()->executeQuery("realestate_db.agentReview", $reviewQuery);
    $existingReviews = iterator_to_array($cursor);
    
    if (!empty($existingReviews)) {
        $_SESSION['error_message'] = "This property has already been reviewed.";
        header("Location: seller_home.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $review = $_POST['review'];
        $rating = intval($_POST['rating']);
        $userID = (int)$_SESSION['userID'];

        // Debug: Check if the fields are populated
        if (empty($review) || empty($rating)) {
            echo "Please fill in all fields!";
            exit();
        }

        // NOSQL: Get the next agentReviewID
        $query = new MongoDB\Driver\Query(
            [],
            ['sort' => ['agentReviewID' => -1], 'limit' => 1]
        );
        $cursor = $mongodb->getConnection()->executeQuery("realestate_db.agentReview", $query);
        $lastReview = current($cursor->toArray());
        $newReviewID = $lastReview ? ($lastReview->agentReviewID + 1) : 1;

        // NOSQL: Insert new review into MongoDB
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert([
            'agentReviewID' => $newReviewID,
            'agentID' => (int)$agentID,
            'propertyID' => (int)$propertyID,
            'review' => $review,
            'review_date' => date('Y-m-d H:i:s'),
            'rating' => $rating,
            'userID' => $userID,
            'response' => "NULL"  // Initialize response as NULL
        ]);

        try {
            $mongodb->getConnection()->executeBulkWrite('realestate_db.agentReview', $bulk);
            $_SESSION['success_message'] = "Review successfully created!";
            header("Location: seller_home.php");
            exit();
        } catch (Exception $e) {
            echo "Error creating review: " . $e->getMessage();
        }
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Review</title>
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
        .rating-input {
            width: 80px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Create Review for Agent <?php echo $agentID; ?></h2>

        <!-- Form for creating a new review -->
        <form method="post" class="mt-4">
            <div class="form-group">
                <label for="review">Review:</label>
                <textarea class="form-control" id="review" name="review" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="rating">Rating (1-5):</label>
                <input type="number" class="form-control rating-input" id="rating" name="rating" 
                       min="1" max="5" required>
                <small class="form-text text-muted">Please rate from 1 (lowest) to 5 (highest)</small>
            </div>
            <button type="submit" class="btn btn-success">Submit Review</button>
            <a href="seller_listings.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>