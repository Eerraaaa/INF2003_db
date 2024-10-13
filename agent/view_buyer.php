<?php
session_start();
include '../lib/connection.php';
include "../inc/agentnav.inc.php";

// Get the propertyID from the URL
$propertyID = isset($_GET['propertyID']) ? intval($_GET['propertyID']) : null;

if (!$propertyID) {
    echo "Invalid property ID.";
    exit();
}

// Fetch buyer details for the property from the cart
$sqlBuyers = "
    SELECT Users.username, Users.email 
    FROM Cart
    JOIN Users ON Cart.userID = Users.userID
    WHERE Cart.propertyID = ?
";

$stmtBuyers = $conn->prepare($sqlBuyers);
if (!$stmtBuyers) {
    echo "Error preparing query: " . $conn->error;
    exit();
}

$stmtBuyers->bind_param('i', $propertyID);
$stmtBuyers->execute();
$resultBuyers = $stmtBuyers->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Buyer</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5" style="padding-top:100px;">
        <h2>Buyers for Property ID: <?php echo $propertyID; ?></h2>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Buyer Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultBuyers->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <!-- Back Button -->
        <a href="buyer_transaction.php" class="btn btn-secondary">Back</a>
    </div>
</body>
</html>

<?php
$stmtBuyers->close();
$conn->close();
?>