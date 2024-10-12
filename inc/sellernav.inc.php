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
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: unauthorized.php"); // Redirect to unauthorized access page
    exit();
}
include '../lib/connection.php';
?>
    <title>Seller Home</title>
</head>
<body>
    
<nav class="navbar navbar-expand-md navbar-light fixed-top shrink bg-white" id="banner">
      <div class="container">
        <div class="d-flex align-items-center">
          <!-- Modified Seller title -->
          <span class="navbar-brand font-weight-bold text-dark mb-0 mr-2" style="font-size: 2.5rem;">Seller</span>
          
          <!-- Navbar links start-->
          <div id="nav">
            <div class="navbar-nav">
              <a class="nav-link font-weight-bold" href="create_listing.php">Sell Property</a>
              <a class="nav-link font-weight-bold" href="seller_home.php">My Properties</a>
              <a class="nav-link font-weight-bold" href="view_my_reviews.php">View Reviews</a>
              <a class="nav-link font-weight-bold" href="view_allagent_reviews.php">View all Agent Reviews</a>
              <a class="nav-link font-weight-bold" href="">All Transactions</a>
              <a class="nav-link font-weight-bold" href="account.php">My Account</a>
            </div>
          </div>
          <!-- Navbar links end-->
        </div>

        <!-- Toggler/collapsibe Button start-->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsibleNavbar">
          <span class="navbar-toggler-icon" id="navbar-toggler" title="navbar-toggler"></span>
        </button>
        
        
        <a href="../logout.php" class="ml-auto">(logout)</a>
      </div>
    </nav>
</body>

</html>