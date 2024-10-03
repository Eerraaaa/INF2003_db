<?php
include 'header.php';
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: unauthorized.php"); // Redirect to unauthorized access page
    exit();
}

include "../inc/headproduct.inc.php";
include 'lib/connection.php';

// Use a JOIN to get agent and user details in a single query
$query = "
    SELECT 
        a.agentID, a.areaInCharge, a.rating, 
        u.username, u.email, u.fname, u.lname, u.phone_number
    FROM Agent a
    JOIN Users u ON a.userID = u.userID
    WHERE u.userType = 'agent'
";

$result = $conn->query($query);

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
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="css/pending_orders.css">
    <link rel="stylesheet" href="../css/searchnavStyle.css">
</head>

<body>
    <div class="container homebody">
        <div class="row">
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
          <h5>List of Agents</h5>
        </div>
    </div>
    <form class="col-8 col-lg-7" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <input type="search" class="form-control" placeholder="Search" aria-label="search" name="search">
            <button class="btn btn-primary search" type="submit" name="submit_search">Search</button>
        </form>
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
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8'>No agents found</td></tr>";
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
