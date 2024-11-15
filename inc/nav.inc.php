<?php
// Start or resume a session
session_start();

// Check if the user is logged in and retrieve user information if so
$userFirstName = ''; // Initialize an empty string for the user's first name
if (isset($_SESSION['userID'])) {
    // Assume $_SESSION['user_id'] contains the user's ID
    // Connect to the database to retrieve user information
    require '../lib/connection.php'; // Ensure you include your database connection here
    
    // Prepare a SQL statement to fetch the user's first name
    $stmt = $conn->prepare("SELECT fname FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['userID']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userFirstName = $row['fname']; // Assign the user's first name to the variable
    }
    $stmt->close();
}
?>


<nav class="navbar navbar-expand-md navbar-light fixed-top bg-white" id="banner">
  <div class="container-fluid d-flex flex-column">
      <!-- Your nav structure -->
      <div class="row nav-container">
            <div class="col-sm-12 col-md-6">
              <!-- Placeholder for other content(top left) or empty -->
            </div>
            <nav class="col-sm-12 col-md-6 nav-item">
                <ul>
                    <?php if (!empty($_SESSION['userID']) && !empty($_SESSION['user_first_name'])): ?>
                          <li class="account-dropdown">
                            <a href="#"><i class="fa fa-user"></i><span><?php echo htmlspecialchars($userFirstName); ?></span></a>
                            <div class="account-dropdown-content">
                                <a href="../user_dashboard.php">Dashboard</a>
                                <a href="#" onclick="confirmLogout()">Logout</a>
                            </div>
                          </li>
                          <li><a href="buyer/cart.php"><i class="fa fa-shopping-cart"></i><span>My Cart</span></a></li>
                        <?php else: ?>
                          <li class="account-dropdown">
                            <a href="#"><i class="fa fa-user"></i><span>Account</span></a>
                            <div class="account-dropdown-content">
                                <a href="../user_management/register.php">Register</a>
                                <a href="../user_management/newlogin.php">Login</a>
                            </div>
                          </li>
                        <?php endif; ?>
                <!-- <li><a href="#">SGD&nbsp;<i class="fa fa-chevron-down"></i></a></li> -->
                </ul>
        </nav>
    </div>
    <!-- Bottom container with other links -->
    <div class="bottom-container d-flex justify-content-end">
      <button class="navbar-toggler" id="navbar-toggler" title="navbar-toggler" type="button" data-toggle="collapse" data-target="#nav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div id="nav" class="collapse navbar-collapse">
          <ul class="navbar-nav ms-auto me-0">
            <li class="nav-item">
              <a class="nav-link font-weight-bold" href="../index.php">Properties</a>
            </li>
          </ul>
      </div>
    </div>
 </div>
</nav>



<script>
  let lastScrollTop = 0;
  window.addEventListener("scroll", function() {
    let currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    if (currentScroll > lastScrollTop) {
      // Scrolling down
      document.getElementById("banner").classList.add("hidden-navbar");
    } else {
      // Scrolling up
      document.getElementById("banner").classList.remove("hidden-navbar");
    }
    lastScrollTop = currentScroll <= 0 ? 0 : currentScroll; // For Mobile or negative scrolling
  }, false);

  function confirmLogout() {
    if (confirm("Are you sure you want to logout?")) {
        window.location.href = "logout.php";
    }
  }
</script>

  <link rel="stylesheet" href="../css/nav.css">