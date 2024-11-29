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

    try {
        $conn->begin_transaction(); // Start a transaction

        // Lock the cart row before deletion
        $checkSql = "SELECT * FROM Cart WHERE userID = ? AND propertyID = ? FOR UPDATE";
        $checkStmt = $conn->prepare($checkSql);
        if (!$checkStmt) {
            throw new Exception("Prepare failed for locking: " . $conn->error);
        }
        $checkStmt->bind_param("ii", $userID, $removePropertyID);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        if ($checkResult->num_rows === 0) {
            throw new Exception("No matching cart item found.");
        }

        // Perform the deletion
        $removeSql = "DELETE FROM Cart WHERE userID = ? AND propertyID = ?";
        $removeStmt = $conn->prepare($removeSql);
        if (!$removeStmt) {
            throw new Exception("Prepare failed for deletion: " . $conn->error);
        }
        $removeStmt->bind_param("ii", $userID, $removePropertyID);
        $removeStmt->execute();

        $conn->commit(); // Commit the transaction
        header("Location: cart.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback(); // Rollback on failure
        die("Error occurred while removing item from cart: " . $e->getMessage());
    }
}

// Handle purchase
if (isset($_POST['purchase'])) {
    $totalPrice = $_POST['total_price'];

    try {
        $mongodb = MongoDBConnection::getInstance();

        // Start MySQL transaction
        $conn->begin_transaction();

        // Lock cart rows for the current user
        $cartSql = "SELECT propertyID FROM Cart WHERE userID = ? FOR UPDATE";
        $cartStmt = $conn->prepare($cartSql);
        $cartStmt->bind_param("i", $userID);
        $cartStmt->execute();
        $cartResult = $cartStmt->get_result();

        $propertyIDs = [];
        while ($cartItem = $cartResult->fetch_assoc()) {
            $propertyIDs[] = $cartItem['propertyID'];
        }

        if (empty($propertyIDs)) {
            throw new Exception("No items in cart to purchase.");
        }

        // Update properties to sold in MySQL
        $updateSql = "UPDATE Property 
                      SET availability = 'sold', transactionDate = CURDATE()
                      WHERE propertyID IN (" . implode(',', $propertyIDs) . ")";
        if (!$conn->query($updateSql)) {
            throw new Exception("Failed to update property availability: " . $conn->error);
        }

        // Get the next transaction ID from MongoDB
        $query = new MongoDB\Driver\Query([], ['sort' => ['transactionID' => -1], 'limit' => 1]);
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

        if ($result->getInsertedCount() !== count($propertyIDs)) {
            throw new Exception("Failed to record all transactions in MongoDB.");
        }

        // Clear the cart
        $clearCartSql = "DELETE FROM Cart WHERE userID = ?";
        $clearCartStmt = $conn->prepare($clearCartSql);
        $clearCartStmt->bind_param("i", $userID);
        $clearCartStmt->execute();

        // Commit MySQL transaction
        $conn->commit();

        header("Location: purchase_success.php?total_price=" . urlencode($totalPrice));
        exit();
    } catch (Exception $e) {
        // Rollback MySQL transaction on error
        $conn->rollback();
        die("Error occurred during purchase: " . $e->getMessage());
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