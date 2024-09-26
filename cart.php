<?php
session_start();
require 'lib/connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: newlogin.php');
    exit;
}

$userId = $_SESSION['user_id'];
$cartItems = [];

// Fetch product items from the cart
$sql = "SELECT p.id, p.name, p.price, c.quantity, p.imgname 
        FROM cart c 
        JOIN product p ON c.productid = p.id 
        WHERE c.userid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
    }
}

// Assuming subscription information is stored in session as shown in your previous snippets
if (isset($_SESSION['cart']['subscription'])) {
    $subscriptionItem = $_SESSION['cart']['subscription'];
    // Append the subscription item to the cart items array
    $cartItems[] = array(
        'id' => 'subscription', // This could be the ID of the subscription if you have one
        'name' => $subscriptionItem['item_name'], // Use the name key that you have in your session
        'price' => $subscriptionItem['price'],
        'quantity' => 1, // Subscription quantity is always 1
        'tier_name' => $subscriptionItem['item_name'] // Assuming tier_name is used in the HTML below
    );
}

$totalAmount = 0;
foreach ($cartItems as $item) {
    $totalAmount += $item['price'] * $item['quantity'];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart</title>

    <link rel="stylesheet" href="css/addtocart.css">

    <?php
    include "inc/nav.inc.php";
    include "inc/head.inc.php";
    ?>

    <script src="https://www.paypal.com/sdk/js?client-id=AQ1lPcqtP95fOoHhVz3LOlAi0FskSS3opOhs7LiYm-4bebpzWzJXUrGZh8SJ8l6ZpTp7_20WHzoM50tw"></script>

</head>

<body>
    <div class="container" style="padding-top: 230px">
        <h1>Your Shopping Cart</h1>
        <?php if (empty($cartItems)) : ?>
            <p>Your cart is empty.</p>
        <?php else : ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cartItems as $item) : ?>
                        <tr>
                            <td>
                                <?php if (isset($item['imgname'])) : ?>
                                    <!-- Product Image -->
                                    <img src="admin/product_img/<?php echo htmlspecialchars($item['imgname']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 50px; height: auto;">
                                <?php endif; ?>
                                <!-- Item Name -->
                                <?php echo htmlspecialchars($item['name']); ?>
                            </td>
                            <td>
                                <!-- Price -->
                                $<?php echo htmlspecialchars($item['price']); ?>
                            </td>
                            <td>
                                <!-- Quantity -->
                                <?php echo htmlspecialchars($item['quantity']); ?>
                            </td>
                            <td>
                                <!-- Total -->
                                $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </td>
                            <td>
                                <form action="update_cart.php" method="post">
                                    <!-- Hidden Fields -->
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                    <input type="hidden" name="item_type" value="<?php echo isset($item['tier_name']) ? 'subscription' : 'product'; ?>">
                                    <?php if (!isset($item['tier_name'])) : ?>
                                        <!-- Product Buttons -->
                                        <button class="btn btn-small btn-primary" name="action" value="increase">+</button>
                                        <button class="btn btn-small btn-secondary" name="action" value="decrease">-</button>
                                    <?php endif; ?>
                                    <!-- Remove Button for Both -->
                                    <button class="btn btn-small btn-danger" name="action" value="remove">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="3" style="text-align: right;">Total:</th>
                        <th>$<?php echo number_format($totalAmount, 2); ?></th>
                   
                    </tr>
                </tfoot>
            </table>

            <div id="paypal-button-container"></div>


        <?php endif; ?>
    </div>

    <?php
    include "inc/footer.inc.php";
    ?>

    <script>
        paypal.Buttons({
            createOrder: function(data, actions) {
                // This function sets up the details of the transaction, including the amount and currency code
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?php echo $totalAmount; ?>' // Use PHP to dynamically set the cart total
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // Make an AJAX request to your server-side script
                    fetch('process_orders.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            // You might need to send additional data based on your setup
                        }).then(response => response.text())
                        .then(data => {
                            console.log(data); // Log the response from your PHP script
                            // Redirect or show a success message
                            alert('Transaction completed!');
                            // window.location.href = "user_dashobard.php";
                        }).catch((error) => {
                            console.error('Error:', error);
                        });
                });
            }

        }).render('#paypal-button-container');
    </script>


</body>

</html>