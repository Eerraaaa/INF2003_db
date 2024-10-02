<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Read Reviews</title>
    <link rel="shortcut icon" type="image/x-icon" href="../img/favicon.png">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <!--Font Awesome-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <!-- Bootstrap JS-->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Agent Reviews</h2>
    <?php
    session_start();
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
        header("Location: unauthorized.php"); // Redirect to unauthorized access page
        exit();
    }
    include '../lib/connection.php';
    include "../inc/agentnav.inc.php";

    // Check if a success message is set
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
        // Unset the success message so it doesn't show again
        unset($_SESSION['success_message']);
    }

    // Fetch all reviews
    $sql = "SELECT * FROM agentReview";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table class='table table-striped table-bordered mt-4'>
                <thead class='thead-dark'>
                    <tr>
                        <th>Agent Review ID</th>
                        <th>Agent ID</th>
                        <th>Review</th>
                        <th>Rating</th>
                        <th>Review Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>";
        // Output data of each row
        while($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . $row["agentReviewID"] . "</td>
                    <td>" . $row["agentID"] . "</td>
                    <td>" . $row["review"] . "</td>
                    <td>" . $row["rating"] . "</td>
                    <td>" . $row["review_date"] . "</td>
                    <td>
                        <a href='update_review.php?id=" . $row["agentReviewID"] . "' class='btn btn-primary btn-sm'>Edit</a> 
                        <a href='delete_review.php?id=" . $row["agentReviewID"] . "' class='btn btn-danger btn-sm'>Delete</a>
                    </td>
                  </tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<div class='alert alert-warning text-center'>No reviews found</div>";
    }

    $conn->close();
    ?>
</div>
</body>
</html>
