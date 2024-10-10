<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Reviews</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.png">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <!--Font Awesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <!-- Bootstrap JS-->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <style>
        body {
            padding-top: 70px; /* Adjust this value based on your navbar height */
        }
    </style>
</head>
<body>
<?php
include "../inc/agentnav.inc.php";
?>
<div class="container mt-5">
    <h2 class="text-center">My Reviews</h2>
    <?php
        session_start();
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
            header("Location: unauthorized.php");
            exit();
        }
        include '../lib/connection.php';

        // Check if a success message is set
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }

        // Get the agent ID for the logged-in user
        $userID = $_SESSION['userID'];
        $agentIDQuery = "SELECT agentID FROM Agent WHERE userID = ?";
        $agentStmt = $conn->prepare($agentIDQuery);
        $agentStmt->bind_param("i", $userID);
        $agentStmt->execute();
        $agentResult = $agentStmt->get_result();
        
        if ($agentResult->num_rows > 0) {
            $agentRow = $agentResult->fetch_assoc();
            $agentID = $agentRow['agentID'];
            
            // Fetch reviews for the logged-in agent
            $sql = "SELECT ar.*, CONCAT(u.fname, ' ', u.lname) AS seller_name
                    FROM agentReview ar
                    JOIN Users u ON ar.userID = u.userID
                    WHERE ar.agentID = ?
                    ORDER BY ar.review_date DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $agentID);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<table class='table table-striped table-bordered mt-4'>
                        <thead class='thead-dark'>
                            <tr>
                                <th>Seller Name</th>
                                <th>Review</th>
                                <th>Rating</th>
                                <th>Review Date</th>
                                <th>Response</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>";
                while($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>" . htmlspecialchars($row["seller_name"]) . "</td>
                            <td>" . htmlspecialchars($row["review"]) . "</td>
                            <td>" . htmlspecialchars($row["rating"]) . "</td>
                            <td>" . htmlspecialchars($row["review_date"]) . "</td>
                            <td>" . (empty($row["response"]) ? "No response yet" : htmlspecialchars($row["response"])) . "</td>
                            <td>";
                    if (empty($row["response"])) {
                        echo "<a href='respond_review.php?id=" . $row["agentReviewID"] . "' class='btn btn-primary btn-sm'>Respond</a>";
                    } else {
                        echo "Response submitted";
                    }
                    echo "</td></tr>";
                }
                echo "</tbody></table>";
            } else {
                echo "<div class='alert alert-info text-center'>You have no reviews yet.</div>";
            }
            $stmt->close();
        } else {
            echo "<div class='alert alert-danger text-center'>Error: Agent not found.</div>";
        }
        $agentStmt->close();
        $conn->close();
    ?>
</div>
</body>
</html>