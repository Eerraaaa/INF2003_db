<?php
include 'header.php';
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

include "../inc/headproduct.inc.php";
include 'lib/connection.php';
include 'lib/mongodb.php';

try {
    $mongodb = MongoDBConnection::getInstance();
    
    // Get the search term if any
    $search = isset($_POST['search']) ? trim($_POST['search']) : "";

    // Get all agents first
    $query = new MongoDB\Driver\Query(
        [], // Empty filter to get all agents
        ['sort' => ['agentID' => 1]]
    );
    
    $cursor = $mongodb->getConnection()->executeQuery("realestate_db.agent", $query);
    $agentsMap = []; // Using an associative array to prevent duplicates
    
    foreach ($cursor as $agent) {
        $agentData = json_decode(json_encode($agent), true);
        
        // Ensure all required fields exist with default values if not set
        $agentData = array_merge([
            'agentID' => 0,
            'userID' => 0,
            'areaInCharge' => '',
            'rating' => 0,
            'totalReviews' => 0
        ], $agentData);

        // Convert NULL rating to 0 or numeric value
        if ($agentData['rating'] === "NULL" || $agentData['rating'] === null) {
            $agentData['rating'] = 0;
        }
        
        $agentID = $agentData['agentID'];
        
        // Check if this agentID is already in the array
        if (!isset($agentsMap[$agentID])) {
            // Get user details from MySQL
            $userQuery = "SELECT username, email, fname, lname, phone_number FROM Users WHERE userID = ?";
            $stmt = $conn->prepare($userQuery);
            $userID = (int)$agentData['userID'];
            $stmt->bind_param("i", $userID);
            $stmt->execute();
            $userResult = $stmt->get_result();
            $userDetails = $userResult->fetch_assoc();
            
            if ($userDetails) {
                // Set default values for any missing user details
                $userDetails = array_merge([
                    'username' => '',
                    'email' => '',
                    'fname' => '',
                    'lname' => '',
                    'phone_number' => ''
                ], $userDetails);
                
                // Combine agent and user data
                $combinedData = array_merge($agentData, $userDetails);
                
                // If there's a search term, check if this agent matches
                if (!empty($search)) {
                    $found = false;
                    foreach ($combinedData as $value) {
                        if (stripos((string)$value, $search) !== false) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        continue;
                    }
                }
                
                $agentsMap[$agentID] = $combinedData;
            }
        }
    }
    
    // Convert map to a simple array for display
    $agents = array_values($agentsMap);
    
} catch (Exception $e) {
    error_log("Error in admin_home.php: " . $e->getMessage());
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent List</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container-fluid mt-5" style="padding-top: 70px;">
        <h3>List of Agents</h3>
        
        <!-- Search Form -->
        <div class="row search-bar mt-3">
            <form class="col-8 col-lg-7" action="" method="post">
                <div class="input-group">
                    <input type="text" class="form-control" placeholder="Search" 
                           name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Agent Listings Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Agent ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Phone Number</th>
                                <th>Area In Charge</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($agents)): ?>
                                <?php foreach ($agents as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['agentID']); ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['fname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['lname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                                        <td><?php echo htmlspecialchars($row['areaInCharge']); ?></td>
                                        <td><?php echo htmlspecialchars(is_numeric($row['rating']) ? number_format($row['rating'], 1) : 'N/A'); ?></td>
                                        <td>
                                            <form action="update_agent.php" method="post" style="display:inline;">
                                                <input type="hidden" name="agentID" value="<?php echo htmlspecialchars($row['agentID']); ?>">
                                                <button type="submit" class="btn btn-warning btn-sm">Update</button>
                                            </form>
                                            <form action="delete_agent.php" method="post" style="display:inline;">
                                                <input type="hidden" name="agentID" value="<?php echo htmlspecialchars($row['agentID']); ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Are you sure you want to delete this agent?');">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="9" class="text-center">No agents found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>