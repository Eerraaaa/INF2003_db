<?php
session_start();
include '../lib/connection.php';
include '../inc/searchnav.inc.php';

// Check if user is logged in and is a buyer
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'buyer') {
    header("Location: ../unauthorized.php");
    exit();
}

$userID = $_SESSION['userID'];

// Fetch cart items
$sql = "SELECT c.*, p.flatType, p.resalePrice, l.town 
        FROM Cart c 
        JOIN Property p ON c.propertyID = p.propertyID 
        JOIN Location l ON p.locationID = l.locationID 
        WHERE c.userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

// Handle remove from cart
if (isset($_POST['remove_from_cart'])) {
    $removePropertyID = $_POST['remove_from_cart'];
    $removeSql = "DELETE FROM Cart WHERE userID = ? AND propertyID = ?";
    $removeStmt = $conn->prepare($removeSql);
    $removeStmt->bind_param("ii", $userID, $removePropertyID);
    $removeStmt->execute();
    header("Location: cart.php"); // Refresh the page
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="../css/styles.css"> <!-- Adjust the path as needed -->
</head>
<body>
    <div class="container">
        <h1>Your Cart</h1>
        <?php if ($result->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Flat Type</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['flatType']); ?></td>
                            <td><?php echo htmlspecialchars($row['town']); ?></td>
                            <td>$<?php echo number_format($row['resalePrice'], 2); ?></td>
                            <td>
                                <form method="post">
                                    <button type="submit" name="remove_from_cart" value="<?php echo $row['propertyID']; ?>" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
        <a href="../index.php" class="btn btn-secondary">Continue Shopping</a>
    </div>
</body>
</html>