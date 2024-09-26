<?php
SESSION_START();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
  header("location:newlogin.php");
  exit; // Don't forget to exit after sending a header redirect.
}

include 'header.php';
include 'lib/connection.php';

$sql = "SELECT * FROM product";
$result = $conn->query($sql);

if (isset($_POST['update_update_btn'])) {
  $update_id = $_POST['update_id'];
  // Handle image upload if a new image is uploaded
  if (isset($_FILES['update_img']) && $_FILES['update_img']['error'] === UPLOAD_ERR_OK) {
    // Process image
    $imgName = $_FILES['update_img']['name'];
    $imgTmp = $_FILES['update_img']['tmp_name'];
    $imgNewPath = "product_img/" . $imgName;

    if (move_uploaded_file($imgTmp, $imgNewPath)) {
      // Since you've moved the new image successfully, update the imgname column in the database
      $update_image_query = "UPDATE `product` SET imgname = ? WHERE id = ?";
      $stmt = $conn->prepare($update_image_query);
      $stmt->bind_param("si", $imgName, $update_id);
      if (!$stmt->execute()) {
        $result = "Error updating image: " . $conn->error;
      } else {
        $result = "Image updated successfully.";
      }
    } else {
      $result = "Error: There was a problem uploading your file.";
    }
  }
  $name = $_POST['update_name'];
  $catagory = $_POST['update_catagory'];
  $quantity = $_POST['update_quantity'];
  $price = $_POST['update_Price'];
  $update_id = $_POST['update_id'];
  $update_quantity_query = mysqli_query($conn, "UPDATE `product` SET quantity = '$quantity' , name='$name' , catagory='$catagory' ,price='$price'  WHERE id = '$update_id'");
  if ($update_quantity_query) {
    header('location:all_product.php');
  };
};

if (isset($_GET['remove'])) {
  $remove_id = $_GET['remove'];
  mysqli_query($conn, "DELETE FROM `reviews` WHERE product_id = '$remove_id'");
  mysqli_query($conn, "DELETE FROM `wishlist` WHERE product_id = '$remove_id'");
  mysqli_query($conn, "DELETE FROM `product` WHERE id = '$remove_id'");
  if (!mysqli_query($conn, "DELETE FROM `product` WHERE id = '$remove_id'")) {
    echo "Error deleting record: " . mysqli_error($conn);
  } else {
    header('location:all_product.php');
    exit;
  }

};
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="css/pending_orders.css">
  <!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/css/bootstrap.min.css">
<!-- Bootstrap Bundle JS (includes Popper.js) -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha1/js/bootstrap.bundle.min.js"></script>
</head>

<body>

  <div class="container">
    <div class="container my-4">
      <div class="row">
        <div class="col-md-6">
          <h5>All Products</h5>
        </div>
        <div class="col-md-6 text-md-right">
          <a href="exportData.php?report=products" class="btn btn-outline-success">
            <i class="fas fa-file-export"></i> Export
          </a>
        </div>
      </div>
    </div>

    <?php

    // Check if a message has been set in the session, then display and unset it
    if (isset($_SESSION['message'])) {
      echo "<div class='alert alert-success'>" . $_SESSION['message'] . "</div>";
      unset($_SESSION['message']); // Remove the message from the session
    } ?>

<div class="row">
    <div class="table-responsive">     
<table class="table">
      <thead>
        <tr>
          <th scope="col">Image</th>
          <th scope="col">Name</th>
          <th scope="col">Category</th>

          <th scope="col">Quantity</th>
          <th scope="col">Price</th>
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
              <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                <td>
                  <input type="file" name="update_img" class="form-control">
                  <input type="hidden" name="existing_imgname" value="<?php echo $row['imgname']; ?>">
                  <img src="product_img/<?php echo $row['imgname']; ?>" style="width:70px;">
                </td>
                
                <!-- <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post"> -->
                <input type="hidden" name="update_id" value="<?php echo $row['id']; ?>">
                <td><input type="text" name="update_name" value="<?php echo $row['name']; ?>"></td>
                <!-- <td><input type="text" name="update_catagory"  value="<?php echo $row['catagory']; ?>" ></td> -->
                <td>
                  <div>
                    <select name="update_catagory" class="form-control">
                      <?php
                      $categories = ["Fiction", "Mystery & Crime", "Romance", "Fantasy", "Horror", "Biography", "Poetry", "Drama", "Manga"];
                      foreach ($categories as $category) {
                        $selected = ($row['catagory'] == $category) ? 'selected' : '';
                        echo "<option value=\"$category\" $selected>$category</option>";
                      }
                      ?>
                    </select>
                  </div>
                </td>

                <td><input type="number" name="update_quantity" value="<?php echo $row['quantity']; ?>"></td>
                <td> <input type="text" name="update_Price" value="<?php echo $row['Price']; ?>"></td>
                <td> <button type="submit" value="update" class="btn btn-primary" name="update_update_btn">Update</button>
              </form>
              </td>
              <td>
  <a href="all_product.php?remove=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove this item?');">Remove</a>
</td>


            </tr>
        <?php
          }
        } else
          echo "0 results";
        ?>
      </tbody>
    </table>

    </div>
</div>

  </div>

</body>

</html>