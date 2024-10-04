<?php
    session_start();
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'seller') {
        header("Location: unauthorized.php"); // Redirect to unauthorized access page
        exit();
    }
    include '../lib/connection.php';
    include "../inc/sellernav.inc.php";

    // Enable error reporting
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Fetch distinct flat types from Property table
    $flatQuery = "SELECT DISTINCT flatType FROM Property";
    $flatResult = $conn->query($flatQuery);

    // Fetch distinct towns from Location table
    $locationQuery = "SELECT DISTINCT town FROM Location ORDER BY town ASC";
    $locationResult = $conn->query($locationQuery);

    // Fetch agents from User table where userType is 'agent'
    $agentQuery = "
        SELECT Agent.agentID, Users.username 
        FROM Agent
        JOIN Users ON Agent.userID = Users.userID";
    $agentResult = $conn->query($agentQuery);

    // Check if the form has been submitted
    $feedbackMessage = ''; // Initialize a variable to hold the feedback message

    // Logged in user is the seller
    $sellerID = $_SESSION['userID'];

    // Check if the form has been submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "Form is submitting"; // Debug

        // Validate and sanitize the inputs
        $flatType = filter_input(INPUT_POST, 'flatType', FILTER_SANITIZE_STRING);
        $town = filter_input(INPUT_POST, 'town', FILTER_SANITIZE_STRING);
        $streetName = filter_input(INPUT_POST, 'streetName', FILTER_SANITIZE_STRING);
        $block = filter_input(INPUT_POST, 'block', FILTER_SANITIZE_STRING);
        $resalePrice = filter_input(INPUT_POST, 'resalePrice', FILTER_VALIDATE_FLOAT);
        $agentID = filter_input(INPUT_POST, 'agentID', FILTER_VALIDATE_INT);

        // Check if all form fields are filled
        if ($flatType && $town && $streetName && $block && $resalePrice && $agentID && $sellerID) {
            echo "All form fields are valid"; // Debug

            // Check if the agent exists in the Agent table
            $agentCheckQuery = "SELECT agentID FROM Agent WHERE agentID = ?";
            $stmt = $conn->prepare($agentCheckQuery);
            if (!$stmt) {
                echo "Error preparing the agent check query: " . $conn->error; // Debug the SQL error
            } else {
                $stmt->bind_param('i', $agentID);
                $stmt->execute();
                $agentResult = $stmt->get_result();

                if ($agentResult->num_rows == 0) {
                    echo "Error: Selected agentID does not exist in the Agent table!";
                    $feedbackMessage = "<div class='alert alert-danger'>Invalid agent selected. Please try again.</div>";
                    exit(); // Stop the process if agentID is invalid
                } else {
                    echo "Agent exists with agentID: " . $agentID; // Debug
                }
            }

            // Check if the location already exists
            $stmt = $conn->prepare('SELECT locationID FROM Location WHERE town = ? AND streetName = ? AND block = ?');
            if (!$stmt) {
                echo "Error preparing the SELECT query: " . $conn->error; // Debug the SQL error
            } else {
                $stmt->bind_param('sss', $town, $streetName, $block);
                $stmt->execute();
                $locationResult = $stmt->get_result();
                $location = $locationResult->fetch_assoc();

                if ($location) {
                    $locationID = $location['locationID'];
                } else {
                    // If location does not exist, insert it
                    $stmt = $conn->prepare('INSERT INTO Location (town, streetName, block) VALUES (?, ?, ?)');
                    if (!$stmt) {
                        echo "Error preparing the INSERT query for Location: " . $conn->error;
                    } else {
                        $stmt->bind_param('sss', $town, $streetName, $block);
                        $stmt->execute();
                        $locationID = $conn->insert_id;
                    }
                }

                // Prepare the Property Insert Query (with sellerID)
                $sql = 'INSERT INTO Property (flatType, locationID, agentID, resalePrice, approvalStatus, sellerID) VALUES (?, ?, ?, ?, ?, ?)';
                echo "<p>SQL Query: $sql</p>"; // Debug the query

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    echo "Error preparing the INSERT query for Property: " . $conn->error; // Show the MySQL error
                } else {
                    $status = 'pending';
                    $stmt->bind_param('siidsi', $flatType, $locationID, $agentID, $resalePrice, $status, $sellerID);

                    if ($stmt->execute()) {
                        echo "Property inserted successfully"; // Debug
                        $feedbackMessage = "<div class='alert alert-success'>Property listing created successfully!</div>";
                        // Redirect to the view listing page after successful insertion
                        header("Location: seller_home.php");
                        exit(); // Stop further code execution
                    } else {
                        echo "Error inserting property: " . $conn->error; // Show the MySQL error
                        $feedbackMessage = "<div class='alert alert-danger'>Error inserting property listing: " . $conn->error . "</div>";
                    }
                }
            }
        } else {
            echo "Some form fields are missing"; // Debug
            $feedbackMessage = "<div class='alert alert-warning'>Invalid input. Please check your data.</div>";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create New Listing</title>
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
    <div class="container mt-5" style="padding-top:100px;">
        <h2 class="text-center">Create New Listing</h2>
        <!-- Display feedback message -->
        <?php if (!empty($feedbackMessage)): ?>
            <?php echo $feedbackMessage; ?>
        <?php endif; ?>

        <!-- Form to create a property listing -->
        <form method="POST" class="mt-4">
            <!-- Flat Type Input -->
            <div class="form-group">
                <label for="flatType">Flat Type:</label><br>
                <select id="flatType" name="flatType" class="form-control" required>
                    <option value="">Select Flat Type</option>
                    <?php
                    if ($flatResult->num_rows > 0) {
                        while ($row = $flatResult->fetch_assoc()) {
                            $flatType = htmlspecialchars($row['flatType']);
                            echo "<option value=\"" . $flatType . "\">" . $flatType . "</option>";
                        }
                    } else {
                        echo "<option value=''>No flat types available</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Resale Price Input -->
            <div class="form-group">
                <label for="resalePrice">Resale Price:</label><br>
                <input type="number" id="resalePrice" name="resalePrice" class="input-field" required>
            </div>

            <!-- Town Dropdown -->
            <div class="form-group">
                <label for="town">Town:</label><br>
                <select id="town" name="town" class="form-control" required>
                    <option value="">Select Town</option>
                    <?php
                    if ($locationResult->num_rows > 0) {
                        while ($row = $locationResult->fetch_assoc()) {
                            $town = htmlspecialchars($row['town']);
                            echo "<option value=\"" . $town . "\">" . $town . "</option>";
                        }
                    } else {
                        echo "<option value=''>No locations available</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Street Name Input -->
            <div class="form-group">
                <label for="streetName">Street Name:</label><br>
                <input type="text" id="streetName" name="streetName" class="input-field" required>
            </div>

            <!-- Block Input -->
            <div class="form-group">
                <label for="block">Block Number:</label><br>
                <input type="text" id="block" name="block" class="input-field" required>
            </div>

            <!-- Agent Dropdown -->
            <div class="form-group">
                <label for="agentID">Agent:</label><br>
                <select id="agentID" name="agentID" class="form-control" required>
                    <option value="">Select Agent</option>
                    <?php
                    if ($agentResult->num_rows > 0) {
                        while ($row = $agentResult->fetch_assoc()) {
                            $agentID = $row['agentID'];
                            $agentName = htmlspecialchars($row['username']);
                            echo "<option value=\"" . $agentID . "\">" . $agentID . " - " . $agentName . "</option>";
                        }
                    } else {
                        echo "<option value=''>No agents available</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary">Submit Listing</button>
        </form>
    </div>
</body>
</html>