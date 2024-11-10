<?php
session_start();
include '../lib/connection.php';
include '../lib/mongodb.php';
include '../inc/head.inc.php';

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
    header("Location: cart.php");
    exit();
}

// Handle purchase
if (isset($_POST['purchase'])) {
    $totalPrice = $_POST['total_price'];

    try {
        $mongodb = MongoDBConnection::getInstance();
        
        // Start MySQL transaction
        $conn->begin_transaction();

        // Get cart items first
        $cartSql = "SELECT propertyID FROM Cart WHERE userID = ?";
        $cartStmt = $conn->prepare($cartSql);
        $cartStmt->bind_param("i", $userID);
        $cartStmt->execute();
        $cartResult = $cartStmt->get_result();
        $propertyIDs = [];
        
        while ($cartItem = $cartResult->fetch_assoc()) {
            $propertyIDs[] = $cartItem['propertyID'];
        }

        // Update properties to sold in MySQL
        $updateSql = "UPDATE Property 
                     SET availability = 'sold', transactionDate = CURDATE()
                     WHERE propertyID IN (" . implode(',', $propertyIDs) . ")";
        $conn->query($updateSql);

        // Get the next transaction ID from MongoDB
        $query = new MongoDB\Driver\Query(
            [],
            ['sort' => ['transactionID' => -1], 'limit' => 1]
        );
        $cursor = $mongodb->getConnection()->executeQuery("realestate_db.transaction", $query);
        $lastTransaction = current($cursor->toArray());
        $nextTransactionID = $lastTransaction ? $lastTransaction->transactionID + 1 : 1;

        // Insert transactions into MongoDB
        $bulk = new MongoDB\Driver\BulkWrite;
        foreach ($propertyIDs as $propertyID) {
            $bulk->insert([
                'transactionID' => $nextTransactionID++,
                'propertyID' => (int)$propertyID,
                'userID' => (int)$userID,
                'transactionDate' => date('Y-m-d'),
                'totalPrice' => (float)$totalPrice
            ]);
        }
        
        $result = $mongodb->getConnection()->executeBulkWrite('realestate_db.transaction', $bulk);

        if ($result->getInsertedCount() === count($propertyIDs)) {
            // Clear the cart
            $clearCartSql = "DELETE FROM Cart WHERE userID = ?";
            $clearCartStmt = $conn->prepare($clearCartSql);
            $clearCartStmt->bind_param("i", $userID);
            $clearCartStmt->execute();

            // Commit MySQL transaction
            $conn->commit();

            header("Location: purchase_success.php?total_price=" . urlencode($totalPrice));
            exit();
        } else {
            throw new Exception("Failed to record all transactions");
        }
    } catch (Exception $e) {
        // Rollback MySQL transaction on error
        $conn->rollback();
        die("Error occurred: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="../css/cart.css">
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
                    <?php
                    $totalPrice = 0;
                    while ($row = $result->fetch_assoc()):
                        $totalPrice += $row['resalePrice'];
                    ?>
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

            <div class="row">
                <div class="col-md-6">
                    <h3>Total Price: $<?php echo number_format($totalPrice, 2); ?></h3>
                </div>
                <div class="col-md-6 text-end">
                    <form method="post">
                        <input type="hidden" name="total_price" value="<?php echo $totalPrice; ?>">
                        <button type="submit" name="purchase" class="btn btn-success">Proceed to Purchase</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>

        <a href="../index.php" class="btn btn-secondary">Continue Shopping</a>
    </div>
</body>
</html>