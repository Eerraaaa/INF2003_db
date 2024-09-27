<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the user is logged in
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        // Store the subscription item name in a session variable
        $_SESSION['subscription_item'] = $_POST['item_name'];
        // Add a login alert
        echo '<script>alert("Please log in to subscribe");</script>';
        // Redirect the user to the newlogin.php page
        header('Refresh: 1; url=newlogin.php');
        exit;
    }
}

// Proceed with the subscription process
//...

?>

<head>
    <script src="https://www.paypal.com/sdk/js?client-id=AQ1lPcqtP95fOoHhVz3LOlAi0FskSS3opOhs7LiYm-4bebpzWzJXUrGZh8SJ8l6ZpTp7_20WHzoM50tw"></script>
    <?php include "inc/subhead.inc.php"; ?>
    <?php include "inc/nav.inc.php"; ?>
</head>

<body>
    <div class="sub-container">
        <p><b>Ever Thought of Reading Regularly While Enjoying Exclusive Discounts?</b></p>
        <p><b>Join Our BookHub Membership for a Curated Reading Journey at Great Value!</b></p>
      
            <!-- Subscription Options Section -->
        <div class="container">
            <a class="button" onclick="scrollToSubscribe()">
                <div class="button__line"></div>
                <div class="button__line"></div>
                <span class="button__text">Subscribe Now</span>
                <div class="button__drow1"></div>
                <div class="button__drow2"></div>
            </a>
        </div>
    </div>

    <div class="row membership-container">
        <!-- Basic Tier Card -->
        <form class="col-6 col-lg" action="sub.php" method="post">
            <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
                <div class="membership-card">
                    <h3>Basic Tier: The Novice Nook</h3>
                    <p>One book per month from a select list.</p>
                    <p>Member-exclusive promo codes.</p>
                    <p>5% discount on all additional purchases.</p>
                    <input type="hidden" name="business" value="bernicechngyq@gmail.com">
                    <input type="hidden" name="cmd" value="_xclick-subscriptions">
                    <input type="hidden" name="item_name" value="Basic Tier: The Novice Nook">
                    <input type="hidden" name="a3" value="50.00"> <!-- Monthly price -->
                    <input type="hidden" name="p3" value="1"> <!-- Billing cycle length -->
                    <input type="hidden" name="t3" value="M"> <!-- Billing cycle unit -->
                    <input type="hidden" name="src" value="1"> <!-- Recurring payments -->
                    <input type="hidden" name="sra" value="1"> <!-- Reattempt on failure -->
                    <!-- Add additional input fields as necessary for your subscription -->
                    <input type="hidden" name="currency_code" value="SGD">
                    <input type="hidden" name="notify_url" value="http://yourwebsite.com/paypal_ipn.php"> <!-- For IPN -->
                    <input type="hidden" name="return" value="http://35.212.129.117/index.php">
                    <input type="hidden" name="cancel_return" value="http://35.212.129.117/ContactUs.php">
                    <button type="submit" value="Subscribe">Subscribe</button>
                    <!-- <button onclick="location.href='url_to_subscribe_basic';">Subscribe</button> -->
                </div>
            </form>
        </form>
        <!-- Standard Tier Card -->
        <form class="col-6 col-lg" action="sub.php" method="post">
            <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
                <div class="membership-card">
                    <h3>Standard Tier: The Avid Reader's Retreat</h3>
                    <p>Two books per month, with a wider selection range.</p>
                    <p>Higher value promo codes.</p>
                    <p>10% discount on all additional purchases.</p>
                    <p>Early access to sales and promotions.</p>
                    <input type="hidden" name="business" value="bernicechngyq@gmail.com">
                    <input type="hidden" name="cmd" value="_xclick-subscriptions">
                    <input type="hidden" name="item_name" value="Standard Tier: The Avid Reader's Retreat">
                    <input type="hidden" name="a3" value="100.00"> <!-- Monthly price -->
                    <input type="hidden" name="p3" value="1"> <!-- Billing cycle length -->
                    <input type="hidden" name="t3" value="M"> <!-- Billing cycle unit -->
                    <input type="hidden" name="src" value="1"> <!-- Recurring payments -->
                    <input type="hidden" name="sra" value="1"> <!-- Reattempt on failure -->
                    <!-- Add additional input fields as necessary for your subscription -->
                    <input type="hidden" name="currency_code" value="SGD">
                    <input type="hidden" name="notify_url" value="http://yourwebsite.com/paypal_ipn.php"> <!-- For IPN -->
                    <input type="hidden" name="return" value="http://35.212.129.117/index.php">
                    <input type="hidden" name="cancel_return" value="http://35.212.129.117/ContactUs.php">
                    <button type="submit" value="Subscribe">Subscribe</button>
                    <!-- <button onclick="location.href='url_to_subscribe_basic';">Subscribe</button> -->
                </div>
            </form>
        </form>

        <!-- Premium Tier Card -->
        <form class="col-6 col-lg" action="sub.php" method="post">
            <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
                <div class="membership-card">
                    <h3>Premium Tier: The Literary Laureate</h3>
                    <p>Three books per month with full access to the catalogue.</p>
                    <p>Exclusive high-value promo codes.</p>
                    <p>15% discount on all additional purchases.</p>
                    <p>Access to exclusive author events and webinars.</p>
                    <input type="hidden" name="business" value="bernicechngyq@gmail.com">
                    <input type="hidden" name="cmd" value="_xclick-subscriptions">
                    <input type="hidden" name="item_name" value="Premium Tier: The Literary Laureate">
                    <input type="hidden" name="a3" value="150.00"> <!-- Monthly price -->
                    <input type="hidden" name="p3" value="1"> <!-- Billing cycle length -->
                    <input type="hidden" name="t3" value="M"> <!-- Billing cycle unit -->
                    <input type="hidden" name="src" value="1"> <!-- Recurring payments -->
                    <input type="hidden" name="sra" value="1"> <!-- Reattempt on failure -->
                    <!-- Add additional input fields as necessary for your subscription -->
                    <input type="hidden" name="currency_code" value="SGD">
                    <input type="hidden" name="notify_url" value="http://yourwebsite.com/paypal_ipn.php"> <!-- For IPN -->
                    <input type="hidden" name="return" value="http://35.212.129.117/index.php">
                    <input type="hidden" name="cancel_return" value="http://35.212.129.117/ContactUs.php">
                    <button type="submit" value="Subscribe">Subscribe</button>
                    <!-- <button onclick="location.href='url_to_subscribe_basic';">Subscribe</button> -->
                </div>
            </form>
        </form>

        <!-- Ultra Tier Card (Student Special) -->
        <form class="col-6 col-lg" action="sub.php" method="post">
            <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">
                <div class="membership-card">
                    <h3>Ultra Tier: The Scholar's Sanctuary</h3>
                    <p>Two books per month: one for leisure and one educational.</p>
                    <p>Special promo codes for textbooks and academic materials.</p>
                    <p>20% discount on educational books.</p>
                    <p>Free access to online study groups and book clubs.</p>
                    <input type="hidden" name="business" value="bernicechngyq@gmail.com">
                    <input type="hidden" name="cmd" value="_xclick-subscriptions">
                    <input type="hidden" name="item_name" value="Ultra Tier: The Scholar's Sanctuary">
                    <input type="hidden" name="a3" value="200.00"> <!-- Monthly price -->
                    <input type="hidden" name="p3" value="1"> <!-- Billing cycle length -->
                    <input type="hidden" name="t3" value="M"> <!-- Billing cycle unit -->
                    <input type="hidden" name="src" value="1"> <!-- Recurring payments -->
                    <input type="hidden" name="sra" value="1"> <!-- Reattempt on failure -->
                    <!-- Add additional input fields as necessary for your subscription -->
                    <input type="hidden" name="currency_code" value="SGD">
                    <input type="hidden" name="notify_url" value="http://yourwebsite.com/paypal_ipn.php"> <!-- For IPN -->
                    <input type="hidden" name="return" value="http://35.212.129.117/index.php">
                    <input type="hidden" name="cancel_return" value="http://35.212.129.117/ContactUs.php">
                    <button type="submit" value="Subscribe">Subscribe</button>
                    <!-- <button onclick="location.href='url_to_subscribe_basic';">Subscribe</button> -->
                </div>
            </form>
        </form>

        <!-- Standard Tier Card
        <div class="membership-card">
            <h3>Standard Tier: The Avid Reader's Retreat</h3>
            <p>Two books per month, with a wider selection range.</p>
            <p>Higher value promo codes.</p>
            <p>10% discount on all additional purchases.</p>
            <p>Early access to sales and promotions.</p>
            <button onclick="location.href='url_to_subscribe_standard';">Subscribe</button>
        </div> -->

        <!-- Premium Tier Card
        <div class="membership-card">
            <h3>Premium Tier: The Literary Laureate</h3>
            <p>Three books per month with full access to the catalogue.</p>
            <p>Exclusive high-value promo codes.</p>
            <p>15% discount on all additional purchases.</p>
            <p>Access to exclusive author events and webinars.</p>
            <button onclick="location.href='url_to_subscribe_premium';">Subscribe</button>
        </div> -->

        <!-- Ultra Tier Card (Student Special)
        <div class="membership-card">
            <h3>Ultra Tier: The Scholar's Sanctuary</h3>
            <p>Two books per month: one for leisure and one educational.</p>
            <p>Special promo codes for textbooks and academic materials.</p>
            <p>20% discount on educational books.</p>
            <p>Free access to online study groups and book clubs.</p>
            <button onclick="location.href='url_to_subscribe_ultra';">Subscribe</button>
            -->
    </div>
    
    <script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                // Set up the transaction
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '0.01' // Can reference this value dynamically from your cart total
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                // Capture the funds from the transaction
                return actions.order.capture().then(function(details) {
                    // Show a success message to the buyer
                    alert('Transaction completed by ' + details.payer.name.given_name + '!');
                });
            }
        }).render('#paypal-button-container');


        function scrollToSubscribe() {
            document.querySelector('.membership-container').scrollIntoView({
                behavior: 'smooth'
            });
        }
    </script>
    <?php include "inc/footer.inc.php"; ?>
</body>

</html>