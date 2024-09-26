<?php
include 'header.php';
SESSION_START();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
  header("location:newlogin.php");
  exit; 
}

include 'lib/connection.php';
$sql = "SELECT * FROM contact_submissions";
$result = $conn->query($sql);
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

  <div class="container pendingbody">
    <div class="container my-4">
      <div class="row">
        <div class="col-md-6">
          <h5>Contact Us Submissions</h5>
        </div>
        <div class="col-md-6 text-md-right">
          <a href="exportData.php?report=contact" class="btn btn-outline-success">
            <i class="fas fa-file-export"></i> Export
          </a>
        </div>
      </div>
    </div>

    <table class="table">
      <thead>
        <tr>

          <th scope="col">Salutation</th>
          <th scope="col">Name</th>
          <th scope="col">Email</th>
          <th scope="col">Message</th>
          <th scope="col">Timestamp</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if (mysqli_num_rows($result) > 0) {
          // output data of each row
          while ($row = mysqli_fetch_assoc($result)) {
        ?>
            <tr>

              <td><?php echo $row["salutation"] ?></td>
              <td><?php echo $row["name"] ?></td>
              <td><?php echo $row["email"] ?></td>
              <td><?php echo $row["message"] ?></td>
              <td><?php echo $row["submitted_at"] ?></td>
            </tr>
        <?php
          }
        } else
          echo "0 results";
        ?>
      </tbody>
    </table>
  </div>

</body>

</html>