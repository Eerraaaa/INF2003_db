<!-- cart codes without the header and footer -->
<?php
session_start();
require 'lib/connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$cartItems = [];

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
    
    <link rel="stylesheet" href="css/addtocart.css">
    <script src="https://www.paypal.com/sdk/js?client-id=AQ1lPcqtP95fOoHhVz3LOlAi0FskSS3opOhs7LiYm-4bebpzWzJXUrGZh8SJ8l6ZpTp7_20WHzoM50tw"></script>

</head>

<body>
<div class="container">
        <header><h3>Your Shopping Cart</h3></header>
        <?php if (empty($cartItems)): ?>
            <p>Your cart is empty.</p>
        <?php else: ?>
            <section>
            <table class="table" aria-label="table">
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
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td>
                                <img src="admin/product_img/<?php echo htmlspecialchars($item['imgname']); ?>" alt="Product Image" style="width: 50px; height: auto;">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </td>
                            <td aria-label="price">$<?php echo htmlspecialchars($item['price']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($item['quantity']); ?>
                            </td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td>
                                <form action="update_cart.php" method="post">
                                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                    <button class="btn btn-small btn-primary" name="action" value="increase">+</button>
                                    <button class="btn btn-small btn-secondary" name="action" value="decrease">-</button>
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
            </section>

            <div id="paypal-button-container"></div>
           
        <?php endif; ?>
    </div>

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
            // This function captures the funds from the transaction
            return actions.order.capture().then(function(details) {
                // After successful capture, you can redirect or display a success message
                alert('Transaction completed by ' + details.payer.name.given_name + '!');
                // Optionally, redirect to a thank-you page
                window.location.href = "thankyou.php";
            });
        }
    }).render('#paypal-button-container');
    </script>


</body>  
</html>
