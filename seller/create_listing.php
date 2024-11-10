<?php  
session_start();  
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {  
    header("Location: unauthorized.php");
    exit();  
}  
include '../lib/connection.php';  
include '../lib/mongodb.php';  // Add MongoDB connection
include "../inc/sellernav.inc.php";  

// Enable error reporting  
ini_set('display_errors', 1);  
ini_set('display_startup_errors', 1);  
error_reporting(E_ALL);  

try {
    $mongodb = MongoDBConnection::getInstance();
} catch (Exception $e) {
    die("Could not connect to MongoDB");
}

// Fetch distinct flat types from Property table  
$flatQuery = "SELECT DISTINCT flatType FROM Property";  
$flatResult = $conn->query($flatQuery);  

// Fetch distinct towns from Location table  
$locationQuery = "SELECT DISTINCT town FROM Location ORDER BY town ASC";  
$locationResult = $conn->query($locationQuery);  

// NOSQL: Fetch agents from MongoDB
try {
    $query = new MongoDB\Driver\Query(
        [], // Empty filter to get all agents
        ['sort' => ['agentID' => 1]] // Sort by agentID
    );
    $cursor = $mongodb->getConnection()->executeQuery("realestate_db.agent", $query);
    $agents = [];
    
    foreach ($cursor as $agent) {
        // Get user details from MySQL for each agent
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
    echo "Error fetching agents: " . $e->getMessage();
    $agents = [];
}

$feedbackMessage = '';
$sellerID = $_SESSION['userID'];  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  
    $flatType = filter_input(INPUT_POST, 'flatType', FILTER_SANITIZE_STRING);  
    $town = filter_input(INPUT_POST, 'town', FILTER_SANITIZE_STRING);  
    $streetName = filter_input(INPUT_POST, 'streetName', FILTER_SANITIZE_STRING);  
    $block = filter_input(INPUT_POST, 'block', FILTER_SANITIZE_STRING);  
    $resalePrice = filter_input(INPUT_POST, 'resalePrice', FILTER_VALIDATE_FLOAT);  
    $agentID = filter_input(INPUT_POST, 'agentID', FILTER_VALIDATE_INT);  

    if ($flatType && $town && $streetName && $block && $resalePrice && $agentID && $sellerID) {  
        // NOSQL: Check if agent exists in MongoDB
        $agentInfo = $mongodb->findOne('agent', ['agentID' => (int)$agentID]);

        if ($agentInfo) {
            // Check if location exists
            $stmt = $conn->prepare('SELECT locationID FROM Location WHERE town = ? AND streetName = ? AND block = ?');  
            if ($stmt) {  
                $stmt->bind_param('sss', $town, $streetName, $block);  
                $stmt->execute();  
                $locationResult = $stmt->get_result();  
                $location = $locationResult->fetch_assoc();  

                if ($location) {  
                    $locationID = $location['locationID'];  
                } else {  
                    $stmt = $conn->prepare('INSERT INTO Location (town, streetName, block) VALUES (?, ?, ?)');  
                    if ($stmt) {  
                        $stmt->bind_param('sss', $town, $streetName, $block);  
                        $stmt->execute();  
                        $locationID = $conn->insert_id;  
                    }  
                }  

                // Insert property
                $sql = 'INSERT INTO Property (flatType, locationID, agentID, resalePrice, approvalStatus, sellerID) VALUES (?, ?, ?, ?, ?, ?)';  
                $stmt = $conn->prepare($sql);  
                if ($stmt) {  
                    $status = 'pending';  
                    $stmt->bind_param('siidsi', $flatType, $locationID, $agentID, $resalePrice, $status, $sellerID);  

                    if ($stmt->execute()) {  
                        $feedbackMessage = "<div class='alert alert-success'>Property listing created successfully!</div>";  
                        header("Location: seller_home.php");  
                        exit();
                    } else {  
                        $feedbackMessage = "<div class='alert alert-danger'>Error inserting property listing: " . $conn->error . "</div>";  
                    }  
                }  
            }  
        } else {
            $feedbackMessage = "<div class='alert alert-danger'>Invalid agent selected. Please try again.</div>";
        }
    } else {  
        $feedbackMessage = "<div class='alert alert-warning'>Invalid input. Please check your data.</div>";  
    }  
}  
?>  
<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="utf-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1">  
    <title>Create New Listing</title>  
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.png">  
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">  
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>  
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>  
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>  
</head>  
<body>  
    <div class="container mt-5" style="padding-top:100px;">  
        <h2 class="text-center">Create New Listing</h2>  
        <?php if (!empty($feedbackMessage)): ?>  
            <?php echo $feedbackMessage; ?>  
        <?php endif; ?>  

        <form method="POST" class="mt-4">  
            <!-- Flat Type Input -->  
            <div class="form-group">  
                <label for="flatType">Flat Type:</label>
                <select id="flatType" name="flatType" class="form-control" required>  
                    <option value="">Select Flat Type</option>  
                    <?php  
                    if ($flatResult->num_rows > 0) {  
                        while ($row = $flatResult->fetch_assoc()) {  
                            $flatType = htmlspecialchars($row['flatType']);  
                            echo "<option value=\"" . $flatType . "\">" . $flatType . "</option>";  
                        }  
                    } else {  
                        echo "<option value=''>No flat types available</option>";  
                    }  
                    ?>  
                </select>  
            </div>  

            <!-- Resale Price Input -->  
            <div class="form-group">  
                <label for="resalePrice">Resale Price:</label>
                <input type="number" id="resalePrice" name="resalePrice" class="form-control" required>  
            </div>  

            <!-- Town Dropdown -->  
            <div class="form-group">  
                <label for="town">Town:</label>
                <select id="town" name="town" class="form-control" required>  
                    <option value="">Select Town</option>  
                    <?php  
                    if ($locationResult->num_rows > 0) {  
                        while ($row = $locationResult->fetch_assoc()) {  
                            $town = htmlspecialchars($row['town']);  
                            echo "<option value=\"" . $town . "\">" . $town . "</option>";  
                        }  
                    } else {  
                        echo "<option value=''>No locations available</option>";  
                    }  
                    ?>  
                </select>  
            </div>  

            <!-- Street Name Input -->  
            <div class="form-group">  
                <label for="streetName">Street Name:</label>
                <input type="text" id="streetName" name="streetName" class="form-control" required>  
            </div>  

            <!-- Block Input -->  
            <div class="form-group">  
                <label for="block">Block Number:</label>
                <input type="text" id="block" name="block" class="form-control" required>  
            </div>

            <!-- Agent Dropdown -->  
            <div class="form-group">  
                <label for="agentID">Agent:</label>
                <select id="agentID" name="agentID" class="form-control" required>  
                    <option value="">Select Agent</option>  
                    <?php  
                    foreach ($agents as $agent) {
                        echo "<option value=\"" . $agent['agentID'] . "\" data-area-in-charge=\"" . 
                             htmlspecialchars($agent['areaInCharge']) . "\">" . 
                             $agent['agentID'] . " - " . htmlspecialchars($agent['username']) . "</option>";  
                    }
                    ?>  
                </select>  
            </div>  

            <button type="submit" class="btn btn-primary">Submit Listing</button>  
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
    });
    </script>  
</body>  
</html>