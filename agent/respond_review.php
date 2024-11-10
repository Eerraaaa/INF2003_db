<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
    header("Location: unauthorized.php");
    exit();
}
include '../lib/connection.php';
include '../lib/mongodb.php';  // Include MongoDB connection

try {
    $mongodb = MongoDBConnection::getInstance();
} catch (Exception $e) {
    die("Could not connect to MongoDB");
}

$reviewID = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = $_POST['response'];
    $reviewID = (int)$_POST['reviewID'];

    // NOSQL: Update review response in MongoDB
    try {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->update(
            ['agentReviewID' => $reviewID],
            ['$set' => ['response' => $response]]
        );

        $mongodb->getConnection()->executeBulkWrite('realestate_db.agentReview', $bulk);
        $_SESSION['success_message'] = "Response submitted successfully!";
        header("Location: read_review.php");
        exit();
    } catch (Exception $e) {
        error_log("Error updating review: " . $e->getMessage());
        echo "Error updating response.";
    }

} elseif ($reviewID) {
    // NOSQL: Get review from MongoDB
    try {
        $query = new MongoDB\Driver\Query(['agentReviewID' => $reviewID]);
        $cursor = $mongodb->getConnection()->executeQuery('realestate_db.agentReview', $query);
        $review = current($cursor->toArray());

        if ($review) {
            $row = json_decode(json_encode($review), true);
        } else {
            echo "Review not found.";
            exit();
        }
    } catch (Exception $e) {
        error_log("Error fetching review: " . $e->getMessage());
        echo "Error fetching review.";
        exit();
    }
} else {
    echo "Invalid request.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Respond to Review</title>
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
    <h2>Respond to Review</h2>
    <form method="post">
        <input type="hidden" name="reviewID" value="<?php echo $reviewID; ?>">
        <div class="form-group">
            <label>Original Review:</label>
            <p><?php echo htmlspecialchars($row['review']); ?></p>
            <p><strong>Rating:</strong> <?php echo htmlspecialchars($row['rating']); ?> stars</p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($row['review_date']); ?></p>
        </div>
        <div class="form-group">
            <label for="response">Your Response:</label>
            <textarea class="form-control" id="response" name="response" required rows="4"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Response</button>
        <a href="read_review.php" class="btn btn-secondary">Back to Reviews</a>
    </form>
</div>

</body>
</html>