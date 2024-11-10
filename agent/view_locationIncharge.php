<?php
session_start();
include '../lib/connection.php';
include '../lib/mongodb.php';  // Add MongoDB connection
include "../inc/agentnav.inc.php";

// Ensure the user is logged in and is an agent
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
    header("Location: unauthorized.php");
    exit();
}

// Initialize variables
$userID = (int)$_SESSION['userID'];
$agentID = null;
$locations = [];
$errorMsg = null;

try {
    $mongodb = MongoDBConnection::getInstance();

    // NOSQL: Fetch agent info from MongoDB instead of MySQL
    $agentInfo = $mongodb->findOne('agent', ['userID' => $userID]);
    
    if ($agentInfo) {
        $agentID = $agentInfo['agentID'];
        $areaInCharge = $agentInfo['areaInCharge'];
        
        // Get agent name from MySQL Users table
        $sqlUser = "SELECT fname, lname FROM Users WHERE userID = ?";
        $stmt = $conn->prepare($sqlUser);
        $stmt->bind_param('i', $userID);
        $stmt->execute();
        $resultUser = $stmt->get_result();
        $userInfo = $resultUser->fetch_assoc();
        $agentName = $userInfo['fname'] . ' ' . $userInfo['lname'];
        $stmt->close();

        // Fetch all unique locations for the agent's area in charge
        $sqlLocations = "SELECT DISTINCT town, streetName 
                        FROM Location 
                        WHERE town = ?
                        ORDER BY streetName";
        
        $stmtLocations = $conn->prepare($sqlLocations);
        if (!$stmtLocations) {
            $errorMsg = "Error preparing the locations query: " . $conn->error;
        } else {
            $stmtLocations->bind_param('s', $areaInCharge);
            $stmtLocations->execute();
            $resultLocations = $stmtLocations->get_result();

            while ($row = $resultLocations->fetch_assoc()) {
                $locations[] = $row;
            }

            if (empty($locations)) {
                $errorMsg = "No locations found for your area in charge.";
            }
            $stmtLocations->close();
        }
    } else {
        $errorMsg = "No agent found for the logged-in user.";
    }
} catch (Exception $e) {
    $errorMsg = "Error: " . $e->getMessage();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agent Locations</title>
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
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>My Area in Charge</h2>
        <?php if ($errorMsg): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php else: ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Agent Information</h5>
                    <p class="card-text"><strong>Name:</strong> <?php echo htmlspecialchars($agentName); ?></p>
                    <p class="card-text"><strong>Area in Charge:</strong> <?php echo htmlspecialchars($areaInCharge); ?></p>
                </div>
            </div>

            <h3>Locations in <?php echo htmlspecialchars($areaInCharge); ?></h3>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Town</th>
                        <th>Street Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($locations as $location): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($location['town']); ?></td>
                            <td><?php echo htmlspecialchars($location['streetName']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>