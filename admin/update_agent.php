<?php
// update_agent.php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: unauthorized.php");
    exit();
}

include 'lib/connection.php';
include 'lib/mongodb.php';
include "../inc/headform.inc.php";

function get_towns($conn) {
    $stmt = $conn->prepare("SELECT DISTINCT town FROM Location");
    $stmt->execute();
    $result = $stmt->get_result();
    $towns = [];
    while ($row = $result->fetch_assoc()) {
        $towns[] = $row['town'];
    }
    return $towns;
}

$towns = get_towns($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        // Handle form submission for updating agent details
        $agentID = (int)$_POST['agentID'];
        $areaInCharge = $_POST['areaInCharge'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $fname = $_POST['fname'];
        $lname = $_POST['lname'];
        $phone_number = $_POST['phone_number'];

        try {
            $mongodb = MongoDBConnection::getInstance();
            
            // Get userID from MongoDB agent document
            $filter = ['agentID' => $agentID];
            $agentData = $mongodb->findOne('agent', $filter);
            
            if ($agentData) {
                // Update Users table in MySQL
                $stmt = $conn->prepare("UPDATE Users SET username = ?, email = ?, fname = ?, lname = ?, phone_number = ? WHERE userID = ?");
                $stmt->bind_param("sssssi", $username, $email, $fname, $lname, $phone_number, $agentData['userID']);
                $stmt->execute();

                // Update MongoDB agent collection
                $bulk = new MongoDB\Driver\BulkWrite;
                $bulk->update(
                    ['agentID' => $agentID],
                    ['$set' => ['areaInCharge' => $areaInCharge]]
                );
                $mongodb->getConnection()->executeBulkWrite('realestate_db.agent', $bulk);

                header("Location: admin_home.php?message=Agent updated successfully");
                exit();
            }
        } catch (Exception $e) {
            die("Error updating agent: " . $e->getMessage());
        }
    } else {
        // Fetch current agent details
        $agentID = (int)$_POST['agentID'];
        
        try {
            $mongodb = MongoDBConnection::getInstance();
            $filter = ['agentID' => $agentID];
            $agentData = $mongodb->findOne('agent', $filter);
            
            if ($agentData) {
                // Get user details from MySQL
                $stmt = $conn->prepare("SELECT username, email, fname, lname, phone_number FROM Users WHERE userID = ?");
                $stmt->bind_param("i", $agentData['userID']);
                $stmt->execute();
                $result = $stmt->get_result();
                $agent = $result->fetch_assoc();
                $agent['areaInCharge'] = $agentData['areaInCharge'];
            } else {
                die("Agent not found.");
            }
        } catch (Exception $e) {
            die("Error fetching agent details: " . $e->getMessage());
        }
    }
} else {
    header("Location: admin_home.php?message=Invalid request");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Agent Details</title>
    <link rel="stylesheet" href="../css/home.css">
</head>

<body>

<main id="main-content">
    <div class="container" style="margin-top: 220px;">

    <h1>Update Agent Details</h1>

    <?php
      if (isset($_SESSION['form_errors'])) {
        foreach ($_SESSION['form_errors'] as $error) {
          echo "<div class='error-message'>$error</div>";
        }
        // Clear errors after displaying
        unset($_SESSION['form_errors']);
      }
      ?>

    <form id="registrationForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <!-- Form fields -->
        <input type="hidden" name="agentID" value="<?php echo htmlspecialchars($agentID); ?>">
        <div class="field">
            <label for="fname">First Name <span class="required-asterisk">*</span></label>
            <input type="text" id="fname" name="fname" value="<?php echo htmlspecialchars($agent['fname']); ?>" required>
        </div>

        <div class="field">
            <label for="lname">Last Name</label>
            <input type="text" id="lname" name="lname" value="<?php echo htmlspecialchars($agent['lname']); ?>">
        </div>
        
        <div class="field">
          <label for="username">Username:</label>
          <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($agent['username']); ?>" required>
        </div>

        <div class="field">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($agent['email']); ?>" required>
        </div>

        <div class="field">
            <label for="phone_number">Phone Number:</label>
            <input type="text" id="phone_number" name="phone_number" required pattern="\d{8}" title="Phone number should be 8 digits" value="<?php echo htmlspecialchars($agent['phone_number']); ?>" required>
        </div>

        <div class="field">
            <label for="areaInCharge">Area In Charge <span class="required-asterisk">*</span></label>
            <select name="areaInCharge" id="areaInCharge" required>
                <option value="">Select a town</option>
                <?php
                foreach ($towns as $town) {
                    $selected = ($town === $agent['areaInCharge']) ? 'selected' : '';
                    echo "<option value=\"" . htmlspecialchars($town) . "\" $selected>" . htmlspecialchars($town) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="field btns">
          <button type="submit" name="update" class="submit">Update</button>
          <a href="admin_home.php" class="submit">Back</a>
        </div>
      </form>
      </main>
      <script src="../js/formscript.js"></script>
</body>
</html>
