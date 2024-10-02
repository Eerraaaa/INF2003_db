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
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
        header("Location: unauthorized.php"); // Redirect to unauthorized access page
        exit();
    }
    include '../lib/connection.php';
    include "../inc/agentnav.inc.php";

    // Check if the form has been submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $agentID = $_POST['agentID'];
        $review = $_POST['review'];
        $rating = $_POST['rating'];

        // Insert query to add a new review
        $sql = "INSERT INTO agentReview (agentID, review, rating) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isi", $agentID, $review, $rating);

        if ($stmt->execute()) {
            // Set success message
            $_SESSION['success_message'] = "Review successfully created!";
            
            // Redirect to read_review.php
            header("Location: read_review.php");
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        
        $stmt->close();
        $conn->close();
    }
    ?>

    <!-- Form for creating a new review -->
    <form method="post" class="mt-4">
        <div class="form-group">
            <label for="agentID">Agent ID:</label>
            <input type="number" class="form-control" id="agentID" name="agentID" required>
        </div>
        <div class="form-group">
            <label for="review">Review:</label>
            <textarea class="form-control" id="review" name="review" required></textarea>
        </div>
        <div class="form-group">
            <label for="rating">Rating:</label>
            <input type="number" class="form-control" id="rating" name="rating" min="1" max="5" required>
        </div>
        <button type="submit" class="btn btn-success">Create Review</button>
    </form>
</div>
</body>
</html>
