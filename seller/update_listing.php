<?php
session_start();
include '../lib/connection.php';
include "../inc/sellernav.inc.php";

// Check if the seller is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: unauthorized.php");
    exit();
}

// Fetch the property details for the given propertyID
$propertyID = isset($_GET['id']) ? $_GET['id'] : null;

if (!$propertyID) {
    echo "Invalid property ID.";
    exit();
}

// Fetch property data to autofill the form
$sql = "
    SELECT Property.*, Location.town, Location.streetName, Location.block 
    FROM Property 
    JOIN Location ON Property.locationID = Location.locationID 
    WHERE Property.propertyID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $propertyID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "No property found.";
    exit();
}

$row = $result->fetch_assoc();

// Handle the form submission for updating the listing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flatType = filter_input(INPUT_POST, 'flatType', FILTER_SANITIZE_STRING);
    $town = filter_input(INPUT_POST, 'town', FILTER_SANITIZE_STRING);
    $streetName = filter_input(INPUT_POST, 'streetName', FILTER_SANITIZE_STRING);
    $block = filter_input(INPUT_POST, 'block', FILTER_SANITIZE_STRING);
    $resalePrice = filter_input(INPUT_POST, 'resalePrice', FILTER_VALIDATE_FLOAT);
    $agentID = filter_input(INPUT_POST, 'agentID', FILTER_VALIDATE_INT);

    if ($flatType && $town && $streetName && $block && $resalePrice && $agentID) {
        // Update the location if it already exists
        $stmt = $conn->prepare('SELECT locationID FROM Location WHERE town = ? AND streetName = ? AND block = ?');
        $stmt->bind_param('sss', $town, $streetName, $block);
        $stmt->execute();
        $locationResult = $stmt->get_result();
        $location = $locationResult->fetch_assoc();

        if ($location) {
            $locationID = $location['locationID'];
        } else {
            // If location doesn't exist, insert it
            $stmt = $conn->prepare('INSERT INTO Location (town, streetName, block) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $town, $streetName, $block);
            $stmt->execute();
            $locationID = $conn->insert_id;
        }

        // Update the property details
        $sql = "UPDATE Property SET flatType = ?, locationID = ?, agentID = ?, resalePrice = ? WHERE propertyID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('siidi', $flatType, $locationID, $agentID, $resalePrice, $propertyID);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Listing updated successfully!</div>";
            // If the listing is successfully updated, redirect to the view listing page
            header("Location: seller_home.php");
            exit(); // Ensure that no further code is executed after the redirect

        } else {
            echo "<div class='alert alert-danger'>Error updating listing: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Invalid input. Please check your data.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Update Listing</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5" style="padding-top:100px;">
    <h2 class="text-center">Update Listing</h2>
    <form method="POST">
        <!-- Flat Type Input -->
        <div class="form-group">
            <label for="flatType">Flat Type:</label>
            <select id="flatType" name="flatType" class="form-control" required>
                <option value="">Select Flat Type</option>
                <?php
                // Fetch flat data to autofill
                $flatQuery = "SELECT DISTINCT flatType FROM Property"; // or the relevant table for flat types
                $flatResult = $conn->query($flatQuery);

                if ($flatResult->num_rows > 0) {
                    while ($flatRow = $flatResult->fetch_assoc()) {
                        $flatType = htmlspecialchars($flatRow['flatType']);
                        $selected = ($row['flatType'] == $flatRow['flatType']) ? 'selected' : '';
                        echo "<option value=\"$flatType\" $selected>$flatType</option>";
                    }
                } else {
                    echo "<option value=''>No flat types available</option>";
                }
                ?>
            </select>
        </div>

        <!-- Resale Price -->
        <div class="form-group">
            <label for="resalePrice">Resale Price:</label>
            <input type="number" id="resalePrice" name="resalePrice" value="<?php echo $row['resalePrice']; ?>" class="form-control" required>
        </div>

        <!-- Town Input -->
        <div class="form-group">
            <label for="town">Town:</label>
            <select id="town" name="town" class="form-control" required>
                <option value="">Select Town</option>
                <?php
                // Fetch town data to autofill
                $townQuery = "SELECT DISTINCT town FROM Location ORDER BY town ASC";
                $townResult = $conn->query($townQuery);

                if ($townResult->num_rows > 0) {
                    while ($townRow = $townResult->fetch_assoc()) {
                        $town = htmlspecialchars($townRow['town']);
                        $selected = ($row['town'] == $townRow['town']) ? 'selected' : '';
                        echo "<option value=\"$town\" $selected>$town</option>";
                    }
                } else {
                    echo "<option value=''>No towns available</option>";
                }
                ?>
            </select>
        </div>

        <!-- Street Name -->
        <div class="form-group">
            <label for="streetName">Street Name:</label>
            <input type="text" id="streetName" name="streetName" value="<?php echo $row['streetName']; ?>" class="form-control" required>
        </div>

        <!-- Block -->
        <div class="form-group">
            <label for="block">Block Number:</label>
            <input type="number" id="block" name="block" value="<?php echo $row['block']; ?>" class="form-control" required>
        </div>

        <!-- Agent -->
        <div class="form-group">
            <label for="agentID">Agent:</label>
            <select id="agentID" name="agentID" class="form-control" required>
                <option value="">Select Agent</option>
                <?php
                // Fetch agents from the User table
                $agentQuery = "SELECT agentID, username FROM Users JOIN Agent ON Users.userID = Agent.userID";
                $agentResult = $conn->query($agentQuery);

                if ($agentResult->num_rows > 0) {
                    while ($agentRow = $agentResult->fetch_assoc()) {
                        $selected = ($row['agentID'] == $agentRow['agentID']) ? 'selected' : '';
                        echo "<option value='" . $agentRow['agentID'] . "' " . $selected . ">" . $agentRow['username'] . "</option>";
                    }
                } else {
                    echo "<option value=''>No agents available</option>";
                }
                ?>
            </select>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Update Listing</button>
    </form>
</div>
</body>
</html>
