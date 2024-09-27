<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    session_start(); // Start the session if not already started

    include "inc/headproduct.inc.php";
    include 'lib/connection.php'; // Ensure you include your database connection here

    // Check if the user is logged in
    if (isset($_SESSION['user_id'])) {
        // Retrieve the user ID from the session
        $userId = $_SESSION['user_id'];

        // Query to retrieve wishlist items with product details for the logged-in user
        $sql = "SELECT p.*, w.user_id 
                FROM product p
                INNER JOIN wishlist w ON p.id = w.product_id
                WHERE w.user_id = $userId"; 

        $result = $conn->query($sql); // Execute the query
    } else {
        // If the user is not logged in, redirect them to the login page
        header("Location: login.php");
        exit(); // Stop further execution of the script
    }
    ?> 
    <title>Wishlist</title>
    <link rel="stylesheet" href="css/wishlist.css">
</head>

<body>
    <main class="product-container mt-3 mb-5">
        <?php
        include "inc/searchnav.inc.php";
        ?> 
        <!-- BREADCRUMB -->
        <div class="breadcrumb mt-5">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li>WishList</li>
            </ul>
        </div>
        <div class="row mt-3">
            <div class="col-lg-2"></div>
            <div class="col-lg-10 title"><h2>Wishlist</h2></div>
        </div>


        <!-- MAIN BODY -->
        <div class="row">
            <!-- SIDE BAR -->
            <div class="col-lg-2 mt-3 side-bar">
                <div class="Deals">
                    <h4>Deals</h4>
                    <ul>
                        <li><a href="#">Today's Deal</a></li>
                        <li><a href="#">Top Seller</a></li>
                        <li><a href="#">Most Viewed's</a></li>
                    </ul>
                </div>
                <div class="category">
                    <h4>Category</h4>
                    <ul>
                        <li><a href="#">Fiction</a></li>
                        <li><a href="#">Mystery & Crime</a></li>
                        <li><a href="#">Romance</a></li>
                        <li><a href="#">Fantasy</a></li>
                        <li><a href="#">Horror</a></li>
                        <li><a href="#">Biography</a></li>
                        <li><a href="#">Poetry</a></li>
                        <li><a href="#">Drama</a></li>
                        <li><a href="#">Manga</a></li>
                    </ul>
                </div>
            </div>

            <!-- MAIN BAR-->
            <div class="col-sm-12 col-lg-10 mt-3 main-content">
                <div class="row mt-3">
                    <?php
                    if ($result->num_rows > 0) {
                        // output data of each row
                        while ($row = $result->fetch_assoc()) {
                            ?>
                            <div class="col-md-4 mb-5 item-grid">
                                <a class="item-description" href="productDetail.php?product_id=<?php echo $row['id']; ?>">
                                    <img class="img-fluid mx-auto img-file" src="admin/product_img/<?php echo $row['imgname']; ?>" alt="">
                                    <h6><?php echo $row['name']; ?></h6>
                                    <p class="price">$<?php echo $row['Price']; ?></p>
                                </a>

                                <ul class="button-container mt-3">
                                    <!-- Add button to remove from wishlist -->
                                    <li><a href="remove_from_wishlist.php?product_id=<?php echo $row['id']; ?>"><i class="fa-solid fa-xmark"></i></a></li>
                                    <!-- Add button to add to cart -->
                                    <li><a href="add_to_cart.php?product_id=<?php echo $row['id']; ?>"><i class="fa-solid fa-cart-shopping"></i></a></li>
                                    <!-- Add button to view product details -->
                                    <li><a href="productDetail.php?product_id=<?php echo $row['id']; ?>"><i class="fa-solid fa-eye"></i></a></li>
                                </ul>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<p>No products found in wishlist.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </main>

    <?php
    include "inc/footer.inc.php";
    ?> 
</body>
</html>
