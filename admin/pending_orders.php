<?php

use PHPMailer\PHPMailer\PHPMailer;

require __DIR__ . '/../vendor/autoload.php';

$messages = [];

include 'header.php';
SESSION_START();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
  header("location:newlogin.php");
  exit;
}

include 'lib/connection.php';
$sql = "SELECT * FROM orders";
$result = $conn->query($sql);

// After updating the order status
// Check if the status update form was submitted

if (isset($_POST['update_update_btn'])) {
  $update_value = $_POST['update_status'];
  $update_id = $_POST['update_id'];

  // Update the order in the database
  $update_query = "UPDATE `orders` SET status = '$update_value' WHERE id = '$update_id'";
  $update_quantity_query = mysqli_query($conn, $update_query);

  if ($update_quantity_query) {
    // Check if the new status is "Delivered"
    if ($update_value == 'Delivered') {
      $emailQuery = "
             SELECT users.email 
             FROM orders 
             JOIN users ON orders.userid = users.id 
             WHERE orders.id = '$update_id'
         ";
      $emailResult = mysqli_query($conn, $emailQuery);
      $userData = mysqli_fetch_assoc($emailResult);

      if ($userData) {
        $userEmail = $userData['email']; // User's email address

        $mail = new PHPMailer(true);

        try {
          //Server settings
          $mail->isSMTP();
          $mail->Host       = 'smtp.gmail.com';
          $mail->SMTPAuth   = true;
          $mail->Username   = 'bbernicecyq@gmail.com';
          $mail->Password   = 'oaon jitu rcew nxec';
          $mail->SMTPSecure = 'tls';
          $mail->Port       = 587;
          $mail->SMTPDebug = 0;

          //Recipients
          $mail->setFrom('bbernicecyq@gmail.com', 'BookHub');
          $mail->addAddress($userEmail);

          // Content
          // $mail->isHTML(true);
          $mail->Subject = 'Order Delivered';
          $mail->Body    = 'Your order has been delivered. Thank you for shopping with us!';

          $mail->send();
          $messages[] = 'Message has been sent to ' . $userEmail;
        } catch (Exception $e) {
          $messages[] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
      } else {
        $messages[] = "Could not find the user's email address.";
      }
    }

    // Check if the new status is "Shipped"
    if ($update_value == 'Shipped') {
      // Generate a random tracking number
      $trackingNumber = 'TRACK' . strtoupper(substr(md5(rand()), 0, 10));

      $emailQuery = "
       SELECT users.email 
       FROM orders 
       JOIN users ON orders.userid = users.id 
       WHERE orders.id = '$update_id'
   ";
      $emailResult = mysqli_query($conn, $emailQuery);
      $userData = mysqli_fetch_assoc($emailResult);

      if ($userData) {
        $userEmail = $userData['email']; // User's email address
        // Proceed to send the email using PHPMailer...
        $mail = new PHPMailer(true);

        try {
          //Server settings
          $mail->isSMTP();
          $mail->Host       = 'smtp.gmail.com';
          $mail->SMTPAuth   = true;
          $mail->Username   = 'bbernicecyq@gmail.com';
          $mail->Password   = 'oaon jitu rcew nxec';
          $mail->SMTPSecure = 'tls';
          $mail->Port       = 587;
          $mail->SMTPDebug = 0;

          //Recipients
          $mail->setFrom('bbernicecyq@gmail.com', 'BookHub');
          $mail->addAddress($userEmail); // Add the user's email address

          // Content
          // $mail->isHTML(true);
          $mail->Subject = 'Order Shipped';

          $mail->Body    = "Your order has been shipped. Your tracking number is {$trackingNumber}.";

          $mail->send();

          if ($mail->send()) {
            $messages[] = 'Shipping confirmation has been sent to ' . $userEmail;
          } else {
            $messages[] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
          }
        } catch (Exception $e) {
          $messages[] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
      }
    }

    header('location:pending_orders.php');
  } else {
    // Output error message
    $messages[] = "Failed to update order status: " . mysqli_error($conn);
  }
}


if (isset($_POST['submit'])) {
  $_SESSION['filter']['starttime'] = $_POST['starttime'];
  $_SESSION['filter']['endtime'] = $_POST['endtime'];
  $sql = "SELECT * FROM orders WHERE created_at >= ? AND created_at <= ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $_SESSION['filter']['starttime'], $_SESSION['filter']['endtime']);
  $stmt->execute();
  $result = $stmt->get_result();
} else {
  $sql = "SELECT * FROM orders";
  $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="css/pending_orders.css">

</head>

<body>
  <div class="container">
    <div class="row">
      <div class='col-sm-6'>
        <div class="form-group">


          <div class="col-md-6">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
              <label for="starttime">Start (date and time):</label>
              <input type="datetime-local" id="starttime" name="starttime">

              <label for="endtime">End (date and time):</label>
              <input type="datetime-local" id="endtime" name="endtime">

              <input type="submit" name="submit">
            </form>
          </div>
          <div class="col-md-6 text-md-right">
            <a href="exportData.php?report=orders" class="btn btn-outline-success">
              <i class="fas fa-file-export"></i> Export
            </a>
          </div>



        </div>
      </div>
      <div class="container pendingbody">
        <h5>Order Status</h5>
        <table class="table">
          <thead>
            <tr>
              <th scope="col">ID</th>
              <th scope="col">User ID</th>
              <th scope="col">Status</th>
              <th scope="col">Created At</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
              // output data of each row
              while ($row = mysqli_fetch_assoc($result)) {
            ?>
                <tr>
                  <td><?php echo $row["id"] ?></td>
                  <td><?php echo $row["userid"] ?></td>
                  <td><?php echo $row["status"] ?></td>
                  <td><?php echo $row["created_at"] ?></td>
                  <td>
                    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                      <input type="hidden" name="update_id" value="<?php echo $row['id']; ?>">
                      <div>
                        <select name="update_status" class="form-control">
                          <?php
                          $status = ["Pending", "Shipped", "Delivered", "Cancelled"];
                          foreach ($status as $status) {
                            $selected = ($row['status'] == $status) ? 'selected' : '';
                            echo "<option value=\"$status\" $selected>$status</option>";
                          }
                          ?>
                        </select>
                      </div>
                      <input type="submit" value="Update" name="update_update_btn">
                    </form>
                  </td>
                </tr>
            <?php
              }
            } else {
              echo "<tr><td colspan='5'>0 results</td></tr>";
            }
            ?>
          </tbody>
        </table>
        <?php
        foreach ($messages as $message) {
          echo $message . "<br>";
        }
        ?>
      </div>
    </div>
</body>

</html>