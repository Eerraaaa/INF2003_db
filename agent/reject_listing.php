<?php
session_start();
include '../lib/connection.php';
include "../inc/agentnav.inc.php";

// Ensure the user is logged in and is an agent
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
    header("Location: unauthorized.php");
    exit();
}

// Get the propertyID from the URL
$propertyID = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$propertyID) {
    echo "Invalid property ID.";
    exit();
}

// Fetch property details
$sql = "SELECT flatType, resalePrice, town, streetName, block FROM Property 
        JOIN Location ON Property.locationID = Location.locationID 
        WHERE propertyID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $propertyID);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();
$stmt->close();

if (!$property) {
    echo "Property not found.";
    exit();
}

$feedbackMessage = ''; // Initialize a variable to hold the feedback message
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reject Listing</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.png">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <!--Font Awesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <!-- Bootstrap JS-->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5" style="padding-top:100px;">
        <h2 class="text-center">Reject Listing</h2>
        <!-- Display feedback message -->
        <?php if (!empty($feedbackMessage)): ?>
            <?php echo $feedbackMessage; ?>
        <?php endif; ?>

        <h3>Property Details</h3>
        <p>Flat Type: <?php echo htmlspecialchars($property['flatType']); ?></p>
        <p>Resale Price: <?php echo htmlspecialchars($property['resalePrice']); ?></p>
        <p>Location: <?php echo htmlspecialchars($property['block'] . ' ' . $property['streetName'] . ', ' . $property['town']); ?></p>

        <form action="process_reject.php" method="POST" class="mt-4">
            <input type="hidden" name="propertyID" value="<?php echo $propertyID; ?>">
            
            <div class="form-group">
                <label for="reason">Reason for Rejection:</label>
                <select name="reason" id="reason" class="form-control" required>
                    <option value="">Select a reason</option>
                    <option value="Incorrect pricing">Incorrect pricing</option>
                    <option value="Incomplete information">Incomplete information</option>
                    <option value="Misleading description">Misleading description</option>
                    <option value="Property unavailable">Property unavailable</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="comments">Additional Comments:</label>
                <textarea name="comments" id="comments" class="form-control" rows="4" required></textarea>
            </div>

            <button type="submit" class="btn btn-danger">Reject Listing</button>
        </form>
    </div>
</body>
</html>