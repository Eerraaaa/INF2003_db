<?php
session_start();
include '../lib/connection.php';
include "../inc/sellernav.inc.php";

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in and is a seller
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {
    header("Location: unauthorized.php");
    exit();
}

// Fetch all agent reviews
$reviewSql = "SELECT ar.*, CONCAT(u.fname, ' ', u.lname) AS agent_name
              FROM agentReview ar
              JOIN Agent a ON ar.agentID = a.agentID
              JOIN Users u ON a.userID = u.userID
              ORDER BY a.agentID, ar.review_date DESC";

$reviewStmt = $conn->prepare($reviewSql);
if (!$reviewStmt) {
    echo "Error preparing the review query: " . $conn->error;
    exit();
}

$reviewStmt->execute();
$reviewResult = $reviewStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>All Agent Reviews</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.png">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container mt-5" style="padding-top:100px;">
        <h2 class="text-center">All Agent Reviews</h2>
        <?php
        if ($reviewResult->num_rows > 0) {
            $currentAgent = null;
            while ($review = $reviewResult->fetch_assoc()) {
                if ($currentAgent !== $review['agentID']) {
                    if ($currentAgent !== null) {
                        echo "</tbody></table></div>";
                    }
                    $currentAgent = $review['agentID'];
                    echo "<h3 class='mt-4'>Reviews for " . htmlspecialchars($review['agent_name']) . "</h3>";
                    echo "<div class='container mt-3'>";
                    echo "<table class='table table-bordered table-striped'>";
                    echo "<thead class='thead-dark'>";
                    echo "<tr>";
                    echo "<th>Review</th>";
                    echo "<th>Rating</th>";
                    echo "<th>Review Date</th>";
                    echo "<th>Agent Response</th>";
                    echo "</tr>";
                    echo "</thead>";
                    echo "<tbody>";
                }
                echo "<tr>";
                echo "<td>" . htmlspecialchars($review['review']) . "</td>";
                echo "<td>" . htmlspecialchars($review['rating']) . "</td>";
                echo "<td>" . htmlspecialchars($review['review_date']) . "</td>";
                echo "<td>" . (empty($review['response']) ? 'No response yet' : htmlspecialchars($review['response'])) . "</td>";
                echo "</tr>";
            }
            echo "</tbody></table></div>";
        } else {
            echo "<div class='alert alert-info'>There are no agent reviews available.</div>";
        }
        ?>
    </div>
</body>
</html>
<?php
$reviewStmt->close();
$conn->close();
?>