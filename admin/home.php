<?php
include 'header.php';
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: unauthorized.php"); // Redirect to unauthorized access page
    exit();
}

include "../inc/headproduct.inc.php";
include 'lib/connection.php';

// Initialize the search variable
$search = "";

// Check if a search query was submitted
if (isset($_POST['submit_search'])) {
    $search = $_POST['search'];
}

// Use a JOIN to get agent and user details in a single query, with search functionality
$query = "
    SELECT 
        a.agentID, a.areaInCharge, a.rating, 
        u.username, u.email, u.fname, u.lname, u.phone_number
    FROM Agent a
    JOIN Users u ON a.userID = u.userID
    WHERE u.userType = 'agent'
";

// If there is a search term, add a WHERE clause to filter results
if (!empty($search)) {
    $search = "%" . $conn->real_escape_string($search) . "%";
    $query .= "
        AND (
            a.agentID LIKE ? OR 
            a.areaInCharge LIKE ? OR 
            a.rating LIKE ? OR 
            u.username LIKE ? OR 
            u.email LIKE ? OR 
            u.fname LIKE ? OR 
            u.lname LIKE ? OR 
            u.phone_number LIKE ?
        )
    ";
}

// Prepare and execute the query
$stmt = $conn->prepare($query);

if (!empty($search)) {
    // Bind search parameter for all fields
    $stmt->bind_param("ssssssss", $search, $search, $search, $search, $search, $search, $search, $search);
}

$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Database query failed: " . $conn->error);
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
    <br>
    <br>
    <br>
    <br>
    <br>

    <h3>List of Agents</h3>
          
    <div class="container-fluid">
        <div class="row search-bar mt-3">
            <form class="col-8 col-lg-7" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                <input type="search" class="form-control" placeholder="Search" aria-label="search" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-primary search" type="submit" name="submit_search">Search</button>
            </form>
        </div>
        <!-- MAIN BODY -->
        <div class="row">
            <!-- Agent Listings Table -->
            <div class="row mt-5">
                <div class="col-12">
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
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['agentID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['fname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['lname']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['phone_number']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['areaInCharge']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['rating']) . "</td>";
                                    echo "<td>
                                            <form action='update_agent.php' method='post' style='display:inline;'>
                                                <input type='hidden' name='agentID' value='" . htmlspecialchars($row['agentID']) . "'>
                                                <button type='submit' class='btn btn-warning'>Update</button>
                                            </form>
                                            <form action='delete_agent.php' method='post' style='display:inline;'>
                                                <input type='hidden' name='agentID' value='" . htmlspecialchars($row['agentID']) . "'>
                                                <button type='submit' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this agent?\");'>Delete</button>
                                            </form>
                                        </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9'>No agents found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
