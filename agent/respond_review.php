<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'agent') {
    header("Location: unauthorized.php");
    exit();
}
include '../lib/connection.php';

$reviewID = isset($_GET['id']) ? $_GET['id'] : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = $_POST['response'];
    $reviewID = $_POST['reviewID'];

    $sql = "UPDATE agentReview SET response = ? WHERE agentReviewID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $response, $reviewID);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Response submitted successfully!";
        header("Location: read_review.php");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }    

    $stmt->close();
} elseif ($reviewID) {
    $sql = "SELECT * FROM agentReview WHERE agentReviewID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $reviewID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "Review not found.";
        exit();
    }
    $stmt->close();
} else {
    echo "Invalid request.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Respond to Review</title>
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
    <h2>Respond to Review</h2>
    <form method="post">
        <input type="hidden" name="reviewID" value="<?php echo $reviewID; ?>">
        <div class="form-group">
            <label>Original Review:</label>
            <p><?php echo htmlspecialchars($row['review']); ?></p>
        </div>
        <div class="form-group">
            <label for="response">Your Response:</label>
            <textarea class="form-control" id="response" name="response" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Response</button>
    </form>
</div>
</body>
</html>