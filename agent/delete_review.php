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

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Use a prepared statement to delete the record
    $sql = "DELETE FROM agentReview WHERE agentReviewID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); // 'i' indicates that it's an integer

    if ($stmt->execute()) {
        // Set success message
        $_SESSION['success_message'] = "Review successfully deleted!";
    } else {
        echo "Error deleting record: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    // Redirect back to read_review.php
    header("Location: read_review.php");
    exit();
}
?>