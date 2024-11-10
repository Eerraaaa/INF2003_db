<?php
session_start();
include '../lib/connection.php';
include '../lib/mongodb.php';  // Add MongoDB connection
include "../inc/sellernav.inc.php";

// Check if the seller is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: unauthorized.php");
    exit();
}

try {
    $mongodb = MongoDBConnection::getInstance();
} catch (Exception $e) {
    die("Could not connect to MongoDB");
}

// Fetch the property details
$propertyID = isset($_GET['id']) ? (int)$_GET['id'] : null;
$resubmit = isset($_GET['resubmit']) && $_GET['resubmit'] === 'true';

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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flatType = filter_input(INPUT_POST, 'flatType', FILTER_SANITIZE_STRING);
    $town = filter_input(INPUT_POST, 'town', FILTER_SANITIZE_STRING);
    $streetName = filter_input(INPUT_POST, 'streetName', FILTER_SANITIZE_STRING);
    $block = filter_input(INPUT_POST, 'block', FILTER_SANITIZE_STRING);
    $resalePrice = filter_input(INPUT_POST, 'resalePrice', FILTER_VALIDATE_FLOAT);
    $agentID = filter_input(INPUT_POST, 'agentID', FILTER_VALIDATE_INT);

    if ($flatType && $town && $streetName && $block && $resalePrice && $agentID) {
        // NOSQL: Check if agent exists in MongoDB
        $agentInfo = $mongodb->findOne('agent', ['agentID' => (int)$agentID]);
        
        if ($agentInfo) {
            // Update the location if it exists
            $stmt = $conn->prepare('SELECT locationID FROM Location WHERE town = ? AND streetName = ? AND block = ?');
            $stmt->bind_param('sss', $town, $streetName, $block);
            $stmt->execute();
            $locationResult = $stmt->get_result();
            $location = $locationResult->fetch_assoc();

            if ($location) {
                $locationID = $location['locationID'];
            } else {
                // Insert new location
                $stmt = $conn->prepare('INSERT INTO Location (town, streetName, block) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $town, $streetName, $block);
                $stmt->execute();
                $locationID = $conn->insert_id;
            }

            // Update property details
            $sql = "UPDATE Property SET flatType = ?, locationID = ?, agentID = ?, resalePrice = ?, approvalStatus = ? WHERE propertyID = ?";
            $stmt = $conn->prepare($sql);
            $approvalStatus = $resubmit ? 'pending' : $row['approvalStatus'];
            $stmt->bind_param('siidsi', $flatType, $locationID, $agentID, $resalePrice, $approvalStatus, $propertyID);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Listing updated successfully!";
                header("Location: seller_home.php");
                exit();
            } else {
                $error = "Error updating listing: " . $conn->error;
            }
        } else {
            $error = "Invalid agent selected.";
        }
    } else {
        $error = "Invalid input. Please check your data.";
    }
}

// Fetch flat types from MySQL
$flatQuery = "SELECT DISTINCT flatType FROM Property";
$flatResult = $conn->query($flatQuery);

// Fetch towns from MySQL
$townQuery = "SELECT DISTINCT town FROM Location ORDER BY town ASC";
$townResult = $conn->query($townQuery);

// NOSQL: Fetch agents from MongoDB
try {
    $query = new MongoDB\Driver\Query(
        [], // Empty filter to get all agents
        ['sort' => ['agentID' => 1]]
    );
    $cursor = $mongodb->getConnection()->executeQuery("realestate_db.agent", $query);
    $agents = [];
    
    foreach ($cursor as $agent) {
        // Get agent username from MySQL
        $userQuery = "SELECT username FROM Users WHERE userID = ?";
        $stmt = $conn->prepare($userQuery);
        $userID = (int)$agent->userID;
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $userResult = $stmt->get_result();
        $userDetails = $userResult->fetch_assoc();
        
        if ($userDetails) {
            $agents[] = [
                'agentID' => $agent->agentID,
                'username' => $userDetails['username'],
                'areaInCharge' => $agent->areaInCharge
            ];
        }
    }
} catch (Exception $e) {
    $error = "Error fetching agents: " . $e->getMessage();
    $agents = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $resubmit ? 'Update and Resubmit Listing' : 'Update Listing'; ?></title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <style>
        body {
            padding-top: 70px;
        }
        .form-group label {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center"><?php echo $resubmit ? 'Update and Resubmit Listing' : 'Update Listing'; ?></h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-4">
        <!-- Flat Type Input -->
        <div class="form-group">
            <label for="flatType">Flat Type:</label>
            <select id="flatType" name="flatType" class="form-control" required>
                <option value="">Select Flat Type</option>
                <?php
                if ($flatResult->num_rows > 0) {
                    while ($flatRow = $flatResult->fetch_assoc()) {
                        $flatType = htmlspecialchars($flatRow['flatType']);
                        $selected = ($row['flatType'] == $flatRow['flatType']) ? 'selected' : '';
                        echo "<option value=\"$flatType\" $selected>$flatType</option>";
                    }
                }
                ?>
            </select>
        </div>

        <!-- Resale Price -->
        <div class="form-group">
            <label for="resalePrice">Resale Price:</label>
            <input type="number" id="resalePrice" name="resalePrice" 
                   value="<?php echo $row['resalePrice']; ?>" class="form-control" required>
        </div>

        <!-- Town Input -->
        <div class="form-group">
            <label for="town">Town:</label>
            <select id="town" name="town" class="form-control" required>
                <option value="">Select Town</option>
                <?php
                if ($townResult->num_rows > 0) {
                    while ($townRow = $townResult->fetch_assoc()) {
                        $town = htmlspecialchars($townRow['town']);
                        $selected = ($row['town'] == $townRow['town']) ? 'selected' : '';
                        echo "<option value=\"$town\" $selected>$town</option>";
                    }
                }
                ?>
            </select>
        </div>

        <!-- Street Name -->
        <div class="form-group">
            <label for="streetName">Street Name:</label>
            <input type="text" id="streetName" name="streetName" 
                   value="<?php echo htmlspecialchars($row['streetName']); ?>" class="form-control" required>
        </div>

        <!-- Block -->
        <div class="form-group">
            <label for="block">Block Number:</label>
            <input type="text" id="block" name="block" 
                   value="<?php echo htmlspecialchars($row['block']); ?>" class="form-control" required>
        </div>

        <!-- Agent -->
        <div class="form-group">
            <label for="agentID">Agent:</label>
            <select id="agentID" name="agentID" class="form-control" required>
                <option value="">Select Agent</option>
                <?php foreach ($agents as $agent): ?>
                    <option value="<?php echo $agent['agentID']; ?>" 
                            data-area-in-charge="<?php echo htmlspecialchars($agent['areaInCharge']); ?>"
                            <?php echo ($row['agentID'] == $agent['agentID']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($agent['username']) . ' (' . htmlspecialchars($agent['areaInCharge']) . ')'; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Buttons -->
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <?php echo $resubmit ? 'Update and Resubmit Listing' : 'Update Listing'; ?>
            </button>
            <a href="seller_home.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    $('#town').on('change', function() {
        var selectedTown = $(this).val();
        $('#agentID option').hide();
        $('#agentID option[data-area-in-charge="' + selectedTown + '"]').show();
        if ($('#agentID option:visible').length > 1) {
            $('#agentID option[value=""]').remove();
        }
    });

    // Trigger town change on page load if town is selected
    if ($('#town').val()) {
        $('#town').trigger('change');
    }
});
</script>
</body>
</html>