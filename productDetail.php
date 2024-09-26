<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    include "inc/headproduct.inc.php";
    // Include database connection and start session
    include 'lib/connection.php';
    session_start();

    // Get product ID from URL parameter
    $productId = isset($_GET['product_id']) ? $_GET['product_id'] : null;

    // Initialize userId to null
    $userId = null;

    // Initialize reviewSuccess to false
    $reviewSuccess = false;

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
        // Retrieve product ID from the form data
        $productId = isset($_POST['product_id']) ? $_POST['product_id'] : null;
        $rating = isset($_POST['rating']) ? $_POST['rating'] : null;
        $comment = isset($_POST['review']) ? $_POST['review'] : null;
    
        // Validate user input
        // Check if user input is valid
        if ($productId && $rating && $comment) {
            // Insert review into database
            $stmt = $conn->prepare("INSERT INTO project.reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $productId, $userId, $rating, $comment);
            $stmt->execute();
            $stmt->close();

            // Set reviewSuccess to true
            $reviewSuccess = true;

            // Calculate the new average rating for the product
            $stmt = $conn->prepare("SELECT ROUND(AVG(rating), 2) AS average_rating FROM project.reviews WHERE product_id = ?");
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $averageRating = $result->fetch_assoc()['average_rating'];
                // Update the product table with the new average rating
                $updateStmt = $conn->prepare("UPDATE project.product SET rating = ? WHERE id = ?");
                $updateStmt->bind_param("di", $averageRating, $productId);
                $updateStmt->execute();
                $updateStmt->close();
            }
            $stmt->close();

            // Redirect to prevent duplicate form submissions
            header("Location: " . $_SERVER['REQUEST_URI'] . "?product_id=" . $productId);
            exit();
        } else {
            // Handle validation errors (e.g., display an error message)
            $errorMessage = "Please provide both rating and review description.";
        }
    }
}


    // Retrieve product details from the database
    $product = [];
    if ($productId) {
        $stmt = $conn->prepare("SELECT * FROM project.product WHERE id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
        }
        $stmt->close();
    }
    ?>

    <title><?php echo htmlspecialchars($product['imgname']); ?></title>
    <!-- Custom JS -->
    <script defer src="js/productDetail.js"></script>
    <link rel="stylesheet" href="css/productDetail.css">
</head>
<body>
    <main class="product-container mt-3 mb-5">
        <?php include "inc/searchnav.inc.php"; ?>

        <!-- BREADCRUMB -->
        <div class="breadcrumb mt-5">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="product.php">Collections</a></li>
                <li><a href="product.php">Products</a></li>
                <li><?php echo htmlspecialchars($product['name']); ?></li>
            </ul>
        </div>

        <!-- Product Detail Section -->
        <div class="row mt-5">
            <div class="col-5 image-container">
                <div class="image">
                    <img class="img-fluid product-img" src="admin/product_img/<?php echo htmlspecialchars($product['imgname']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
            </div>
            <div class="col-6">
                <div class="item-details-container">
                    <!-- Product name and details -->
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p>By Author</p>
                    <!-- Star ratings -->
                    <div class="row item-review-container mb-3">
                        <div class="col-3 star-rating">
                            <?php
                            // Display star ratings
                            for ($i = 1; $i <= 5; $i++) {
                                $starClass = ($i <= $product['rating']) ? 'fa-solid' : 'fa-regular';
                                echo "<i class='fa-star $starClass' style='color: #FFD43B;'></i>";
                            }
                            ?>
                        </div>
                        <p class="col-1"><?php echo htmlspecialchars($product['rating']); ?></p>
                        <a class="col-3"  href="#customer-review">Write a Review</a>
                    </div>
                    <!-- Product price -->
                    <div class="price"><span>$<?php echo htmlspecialchars($product['Price']); ?></span></div>
                    <!-- Add to cart button -->
                    <?php if ($isLoggedIn): ?>
                        <button class="btn btn-primary button-cart" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                        <button class="btn btn-primary button-fav" onclick="addToWishlist(<?php echo $product['id']; ?>)">Add to Wishlist</button>
                    <?php else: ?>
                        <p>Please <a href="newlogin.php" style="color: orange; text-decoration: underline;">login</a> to add items to your cart.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Product Description Section -->
        <div class="tab mt-5">
            <span class="tablinks active" onclick="openTab(event, 'overview')">Overview</span>
            <span class="tablinks" onclick="openTab(event, 'delivery')">Delivery</span>
        </div>
        
        <div id="overview" class="tabcontent active">
            <h3>Overview</h3>
            <p>
            <?php echo htmlspecialchars($product['description']); ?>
            </p>
        </div>
            
        <div id="delivery" class="tabcontent">
            <h3>Delivery</h3>
            <p>
                HOW MUCH DOES DELIVERY COST?
                <br>
                Our deliveries may be made any time between 9:00am and 8:00pm. Please try to ensure that there is access 
                to the delivery property during this window, however if you are not at home, our couriers will attempt 
                delivery to a secure location or leave your parcel with a neighbour.
            </p>
            <table>
                <thead>
                    <tr>
                        <th>Delivery Options</th>
                        <th>Delivery Times</th>
                        <th>Delivery Costs</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Standard</td>
                        <td>3 - 5 working days (if ordered before 8pm)</td>
                        <td>$1.99</td>
                    </tr>
                    <tr>
                        <td>Same-Day</td>
                        <td>Within the same day</td>
                        <td>$4.99</td>
                    </tr>
                    <tr>
                        <td>Self-Collect</td>
                        <td>At your convenience</td>
                        <td>Free</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Customer Review Section -->
        <div class="customer-reviews mt-5" id="customer-review">
            <h2>Customer Reviews</h2>
            <?php if ($isLoggedIn): ?>
            <button class="btn btn-primary review-btn mb-3">Write a Review</button>
            <div class="mb-3" id="reviewForm" style="display: none;">
                <!-- Form to submit a review -->
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                    <div class="star-rating">
                        <input type="radio" id="star1" name="rating" value="1"><label for="star1"></label>
                        <input type="radio" id="star2" name="rating" value="2"><label for="star2"></label>
                        <input type="radio" id="star3" name="rating" value="3"><label for="star3"></label>
                        <input type="radio" id="star4" name="rating" value="4"><label for="star4"></label>
                        <input type="radio" id="star5" name="rating" value="5"><label for="star5"></label>
                    </div>
                    <textarea id="review-feedback" name="review" placeholder="Write your review"></textarea>
                    <div class="review-form-footer">
                        <button id="cancel-review" class="cancel-button">Cancel</button>
                        <button type="submit" name="submit_review" class="submit-button">Submit Review</button>
                    </div>
                </form>
                <?php 
                // Display success message if review was successfully submitted
                if ($reviewSuccess) {
                    echo "<p class='success-message'>Your review has been submitted successfully!</p>";
                }
                ?>
                <?php if (isset($errorMessage)) { echo "<p class='error-message'>$errorMessage</p>"; } ?>
            </div>
            <?php else: ?>
            <p>Please <a href="newlogin.php" style="color: orange; text-decoration: underline;">login</a> to submit a review.</p>
            <?php endif; ?>
        </div>

        <!-- Display reviews -->
        <?php
        // Fetch reviews associated with the current product
        $reviewsQuery = $conn->prepare("SELECT reviews.id, users.f_name, reviews.rating, reviews.comment, reviews.created_at FROM reviews JOIN users ON reviews.user_id = users.id WHERE reviews.product_id = ?");
        $reviewsQuery->bind_param("i", $productId);
        $reviewsQuery->execute();
        $reviewsResult = $reviewsQuery->get_result();

        if ($reviewsResult->num_rows > 0) {
            while ($review = $reviewsResult->fetch_assoc()) {
                echo "<div class='review'>";
                echo "<div class='reviewer-info'>";
                // Display reviewer name
                echo "<h4>" . htmlspecialchars($review['f_name']) . "</h4>";
                // Display star rating as Font Awesome icons
                echo "<div class='star-rating'>";
                for ($i = 1; $i <= 5; $i++) {
                    // Determine whether to display a filled or empty star based on the rating value
                    $starClass = $i <= $review['rating'] ? 'fa-solid fa-star' : 'fa-regular fa-star';
                    echo "<i class='$starClass' style='color: #FFD43B;'></i>";
                }
                echo "</div>";
                // Display review date
                echo "<p class='review-date'>Posted on " . date('F j, Y', strtotime($review['created_at'])) . "</p>";
                echo "</div>";
                // Display review content
                echo "<div class='review-content'>";
                echo "<p>{$review['comment']}</p>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            // If there are no reviews, display a message
            echo "<p>No reviews available for this product.</p>";
        }
        ?>
    </main>
    <?php include "inc/footer.inc.php"; ?>
    <script>

        var isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
        function addToCart(productId) {
            if (isLoggedIn) {
                window.location.href = "add_to_cart.php?product_id=" + productId;
            } else {
                alert("Please login before adding items to your cart.");
                // Store the product ID in session via AJAX call or directly pass as URL parameter
                window.location.href = "newlogin.php?redirect=product&product_id=" + productId;
            }
        }

        function addToWishlist(productId) {
            if (isLoggedIn) {
                window.location.href = "add_to_wishlist.php?product_id=" + productId;
            } else {
                alert("Please login before adding items to your wishlist.");
                window.location.href = "newlogin.php?redirect=product&product_id=" + productId;
            }
        }
        
        // Check if wishlist_exists session variable is set
        <?php if(isset($_SESSION['wishlist_exists'])): ?>
            alert('This item is already in your wishlist.');
        <?php
            // Unset the session variable after displaying the pop-up
            unset($_SESSION['wishlist_exists']);
        ?>
        <?php endif; ?>
    </script>
</body>
</html>
