<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "inc/head.inc.php"; ?>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/user_dashboard.css">
</head>

<body>
<?php
session_start();
include "inc/nav.inc.php";
// Redirect to login page if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: newlogin.php");
    exit();
}
?>


<div class="container" style="padding-top: 250px;">
    <div class="row justify-content-center">
    <main role="main" class="container"> 
        <!-- Wrap content in a div and center it -->
            <div class="text-center welcome-heading" style="margin-bottom: 30px">
                <h1>Welcome to Your Dashboard, <?php echo htmlspecialchars($_SESSION['user_first_name']); ?></h1>
            </div>
                <!-- Account Details and Subscription Plan -->
                <div class="row justify-content-center">
                    <div class="col-md-4 mx-auto">
                        <div class="account-overview">
                            <h2>Account Details</h2>
                            <ul class="column">
                                <li class="account-details"><a>Name: <?php echo htmlspecialchars($_SESSION['user_first_name']); ?></a></li>
                                <li class="account-details"><a>Email: <?php echo htmlspecialchars($_SESSION['user_email']); ?></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4 mx-auto">
                        <div class="subscription-plan">
                            <h2>Subscription Plan</h2>
                            <p class="subscription-tier">Subscription Tier: Basic</p>
                        </div>
                    </div>
                </div>
            
        </div>
        <!-- Delivery Status and Cart -->
        <div class="col-md-6">
        <div class="delivery-status" style="margin-top: 0px">
            <h2>Delivery Status</h2>
            <p class="delivery-details">Delivery Status: Pending</p>
        </div>
        </div>

        <div class="cart">
            <?php include "cart_body.php"; ?> 
        </div>
    </main>  
</div>
</div>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    function confirmLogout() {
        if (confirm("Are you sure you want to logout?")) {
            window.location.href = "logout.php";
        }
    }
</script>

<?php
include "inc/footer.inc.php";
?> 

</body>

</html>
