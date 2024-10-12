<?php
// Start or resume a session
session_start();
$isLoggedIn = isset($_SESSION['userID']); // Changed from user_id to userID
$userType = $_SESSION['user_type'] ?? ''; // Get the user type from the session
$isBuyer = $isLoggedIn && $userType === 'buyer';

// Check if the user is logged in and retrieve user's first name
if ($isLoggedIn) {
    $userFirstName = $_SESSION['user_first_name'] ?? 'User';
} else {
    $userFirstName = 'Account';
}
?>

<header>
    <div class="row nav-container">
        <div class="col-sm-12 col-md-6">
            <!-- Placeholder for other content(top left) or empty -->
        </div>
        <nav class="col-sm-12 col-md-6 nav-item">
            <ul>
                <li class="account-dropdown">
                    <?php if ($isLoggedIn): ?>
                        <a href="#"><i class="fa fa-user"></i><span><?php echo htmlspecialchars($userFirstName); ?></span></a>
                        <span class="account-dropdown-content">
                            <a href="../buyer/account.php">Account</a>
                            <a href="#" onclick="confirmLogout()">Logout</a>
                        </span>
                    <?php else: ?>
                        <a href="#"><i class="fa fa-user"></i><span>Account</span></a>
                        <span class="account-dropdown-content">
                            <a href="user_management/register.php">Register</a>
                            <a href="user_management/newlogin.php">Login</a>
                        </span>
                    <?php endif; ?>
                </li>
                <?php if ($isBuyer): ?>
                    <li><a href="buyer/cart.php"><i class="fa-solid fa-cart-shopping"></i><span>My Cart(<span id="cartCount">0</span>)</span></a></li>
                    <li><a href="buyer/past_trans.php"><i class="fa-solid fa-clock-rotate-left"></i></i><span>Past Trasnsaction</span></a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<div class="container-fluid">
    <div class="row search-bar mt-3">
        <form class="col-8 col-lg-7" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <input type="search" class="form-control" placeholder="Search" aria-label="search" name="search">
            <button class="btn btn-primary search" type="submit" name="submit_search">Search</button>
        </form>
        
    </div>
</div>
<link rel="stylesheet" href="css/searchnavStyle.css">

<script>
function confirmLogout() {
    if (confirm("Are you sure you want to logout?")) {
        window.location.href = "logout.php";
    }
}
</script>