<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Review</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.png">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <!--Font Awesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <!-- Bootstrap JS-->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Create New Review</h2>

    <?php
    session_start();
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {
        header("Location: unauthorized.php");
        exit();
    }

    include '../lib/connection.php';
    include "../inc/sellernav.inc.php";
        // Get the agentID and propertyID from the URL
    $agentID = isset($_GET['agentID']) ? intval($_GET['agentID']) : 0;
    // $propertyID = isset($_GET['propertyID']) ? intval($_GET['propertyID']) : 0;

    // Validate agentID and propertyID
    if ($agentID === 0) {
        echo "Invalid Agent ID";
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $review = $_POST['review'];
        $rating = $_POST['rating'];

        // Debug: Check if the fields are populated
        if (empty($review) || empty($rating)) {
            echo "Please fill in all fields!";
            exit();
        }

        // Insert query to add a new agent review
        $sql = "INSERT INTO agentReview (agentID, review, review_date, rating, userID) VALUES (?, ?, NOW(), ?, ?)";
        $stmt = $conn->prepare($sql);

        // Check if prepare statement failed
        if (!$stmt) {
            echo "Error preparing statement: " . $conn->error;
            exit();
        }

        $stmt->bind_param("isii", $agentID, $review, $rating, $_SESSION['userID']);

        // Execute the query and check if successful
        if ($stmt->execute()) {
            // Set success message in session
            $_SESSION['success_message'] = "Review successfully created!";
            
            // Redirect back to seller_home.php
            header("Location: seller_home.php");
            exit();
        } else {
            echo "Error executing query: " . $stmt->error;  // Debugging: Display error message
        }

        $stmt->close();
        $conn->close();
    }
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Review</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.png">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Create Review for Agent #<?php echo $agentID; ?></h2>

    <!-- Form for creating a new review -->
    <form method="post" class="mt-4">
        <div class="form-group">
            <label for="review">Review:</label>
            <textarea class="form-control" id="review" name="review" required></textarea>
        </div>
        <div class="form-group">
            <label for="rating">Rating:</label>
            <input type="number" class="form-control" id="rating" name="rating" min="1" max="5" required>
        </div>
        <button type="submit" class="btn btn-success">Submit Review</button>
    </form>
</div>
</body>
</html>