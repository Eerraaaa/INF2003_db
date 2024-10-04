<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

include 'lib/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        // Handle form submission for updating agent details
        $agentID = $_POST['agentID'];
        $areaInCharge = $_POST['areaInCharge'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $phone_number = $_POST['phone_number'];

        // Update agent and user details
        $conn->begin_transaction();
        try {
            // Update Users table
            $updateUserQuery = "
                UPDATE Users 
                SET username = ?, email = ?, fname = ?, lname = ?, phone_number = ? 
                WHERE userID = (SELECT userID FROM Agent WHERE agentID = ?)
            ";
            $stmt = $conn->prepare($updateUserQuery);
            $stmt->bind_param("sssssi", $username, $email, $fname, $lname, $phone_number, $agentID);
            $stmt->execute();

            // Update Agent table
            $updateAgentQuery = "UPDATE Agent SET areaInCharge = ? WHERE agentID = ?";
            $stmt = $conn->prepare($updateAgentQuery);
            $stmt->bind_param("si", $areaInCharge, $agentID);
            $stmt->execute();

            // Commit transaction
            $conn->commit();

            // Redirect after successful update
            header("Location: home.php?message=Agent updated successfully");
            exit();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            die("Error updating agent: " . $e->getMessage());
        }
    } else {
        // Fetch current agent details
        $agentID = $_POST['agentID'];
        $query = "
            SELECT 
                a.areaInCharge, 
                u.username, 
                u.email, 
                u.fname, 
                u.lname, 
                u.phone_number
            FROM Agent a
            JOIN Users u ON a.userID = u.userID
            WHERE a.agentID = ?
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $agentID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $agent = $result->fetch_assoc();
        } else {
            die("Agent not found.");
        }
    }
} else {
    // Redirect if accessed directly without POST
    header("Location: home.php?message=Invalid request");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Agent</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <h3>Update Agent Details</h3>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <input type="hidden" name="agentID" value="<?php echo htmlspecialchars($agentID); ?>">
        <div>
            <label for="areaInCharge">Area In Charge:</label>
            <input type="text" id="areaInCharge" name="areaInCharge" value="<?php echo htmlspecialchars($agent['areaInCharge']); ?>" required>
        </div>
        <div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($agent['username']); ?>" required>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($agent['email']); ?>" required>
        </div>
        <div>
            <label for="fname">First Name:</label>
            <input type="text" id="fname" name="fname" value="<?php echo htmlspecialchars($agent['fname']); ?>" required>
        </div>
        <div>
            <label for="lname">Last Name:</label>
            <input type="text" id="lname" name="lname" value="<?php echo htmlspecialchars($agent['lname']); ?>" required>
        </div>
        <div>
            <label for="phone_number">Phone Number:</label>
            <input type="text" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($agent['phone_number']); ?>" required>
        </div>
        <div>
            <button type="submit" name="update" class="btn btn-primary">Update</button>
            <a href="home.php" class="btn btn-secondary">Back</a>
        </div>
    </form>
</body>

</html>