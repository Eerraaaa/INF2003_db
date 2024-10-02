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

<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
    header("Location: unauthorized.php"); // Redirect to unauthorized access page
    exit();
}
include '../lib/connection.php';
include "inc/agentnav.inc.php";

// Check if the form has been submitted for the update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reviewID = $_POST['agentReviewID'];
    $review = $_POST['review'];
    $rating = $_POST['rating'];

    // Update query
    $sql = "UPDATE agentReview SET review = ?, rating = ? WHERE agentReviewID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $review, $rating, $reviewID);

    if ($stmt->execute()) {
        // Set success message
        $_SESSION['success_message'] = "Review successfully updated!";
        
        // Redirect to read_review.php
        header("Location: read_review.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }    

    $stmt->close();
    $conn->close();
}

// Fetch the current review to display in the form (if needed for editing)
if (isset($_GET['id'])) {
    $reviewID = $_GET['id'];
    $sql = "SELECT * FROM agentReview WHERE agentReviewID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reviewID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Use the data in the form (e.g., $row['review'], $row['rating'])
    }
    $stmt->close();
}
?>
<!-- Form for updating the review goes here -->
<form method="post">
    <input type="hidden" name="agentReviewID" value="<?php echo $row['agentReviewID']; ?>">
    <label>Review:</label>
    <textarea name="review"><?php echo htmlspecialchars($row['review']); ?></textarea><br>
    <label>Rating:</label>
    <input type="number" name="rating" value="<?php echo $row['rating']; ?>"><br>
    <button type="submit">Update Review</button>
</form>