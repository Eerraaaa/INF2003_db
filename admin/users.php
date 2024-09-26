<?php
SESSION_START();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
  header("location:newlogin.php");
  exit; // Don't forget to exit after sending a header redirect.
}

include 'header.php';
include 'lib/connection.php';

$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <!-- Bootstrap library -->
  <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
  <!-- Stylesheet file -->
  <link rel="stylesheet" href="assets/css/style.css">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
</head>

<body>

  <!-- Export link -->
  <div class="container my-4">
    <div class="row">
      <div class="col-md-6">
        <h5>All Users</h5>
      </div>
      <div class="col-md-6 text-md-right">
        <a href="exportData.php?report=users" class="btn btn-outline-success">
          <i class="fas fa-file-export"></i> Export
        </a>
      </div>
    </div>
  </div>
  <div class="container pendingbody">
    <table class="table">
      <thead>
        <tr>
          <th scope="col">Id</th>
          <th scope="col">First Name</th>
          <th scope="col">Last Name</th>
          <th scope="col">Email</th>
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
              <td><?php echo $row["f_name"] ?></td>
              <td><?php echo $row["l_name"] ?></td>
              <td><?php echo $row["email"] ?></td>
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