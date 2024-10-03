<?php
include 'header.php';
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: unauthorized.php"); // Redirect to unauthorized access page
    exit();
}

include "../inc/headproduct.inc.php";
include 'lib/connection.php';

// Query for property table with search or location, sorting, and pagination
$agentQuery = "SELECT * FROM Agent";
$agentResult = $conn->query($agentQuery);

if (!$agentResult) {
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
    <link rel="stylesheet" href="css/home.css">
</head>

<body>
    <div class="container homebody">
        <div class="row">
            <div class="col-md-12">
                <h1>List of Agents</h1>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-lg-2"></div>
        <div class="col-lg-10 title">
            <h2>List of Agents</h2>
        </div>
    </div>

    <!-- MAIN BODY -->
    <div class="row">
        <!-- MAIN BAR-->
        <div class="col-sm-12 col-lg-10 mt-3 main-content">
            <div class="filter-container">
                <ul class="grid-container">
                    <li class="active"><i class="fa-sharp fa-solid fa-grip icon"></i></li>
                    <li><i class="fa-sharp fa-solid fa-list icon"></i></li>
                </ul>
                <div class="drop-down">
                    <label for="filter-type">Sort By:</label>
                    <select class="filter-type" id="filter-type">
                        <!-- Populate this dropdown with sorting options -->
                        <option value="username">Username</option>
                        <option value="rating">Rating</option>
                        <!-- Add more options as needed -->
                    </select>
                </div>
            </div>

            <!-- Property Listings Table -->
            <div class="row mt-5">
                <div class="col-12">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Agent ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($agentResult->num_rows > 0) {
                                while ($row = $agentResult->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['agentID']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['rating']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4'>No agents found</td></tr>";
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
