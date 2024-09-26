<?php

session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
  header("location:newlogin.php");
  exit; 
}

include 'header.php';
include 'lib/connection.php';
$result = null;
if (isset($_POST['submit'])) {
  $name = $_POST['name'];
  $catagory = $_POST['catagory'];
  $description = $_POST['description'];
  $quantity = $_POST['quantity'];
  $price = $_POST['price'];
  $filename = $_FILES["uploadfile"]["name"];

  if (!is_numeric($quantity) || !is_numeric($price) || $quantity < 0 || $price < 0) {
    $result = "Error: Quantity and Price must be numeric and non-negative.";
  } else {
    // Proceed with the database insert and file upload because the data is valid
    $tempname = $_FILES["uploadfile"]["tmp_name"];
    $folder = "product_img/" . $filename;

    if (move_uploaded_file($tempname, $folder)) {
      $insertSql = "INSERT INTO product (name, catagory, description, quantity, price, imgname) VALUES (?, ?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($insertSql);
      $stmt->bind_param("sssids", $name, $catagory, $description, $quantity, $price, $filename);

      if ($stmt->execute()) {
        $_SESSION['message'] = "Product added successfully."; 
        header("Location: all_product.php"); // Redirect to the all_product.php page
        exit; 
      } else {
        $result = "Error: " . $conn->error;
      }
    } else {
      $result = "Error: There was a problem uploading your file.";
    }
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body>
  <div class="container">
    <?php echo $result; ?>
    <h4>Add Product</h4>
    <br>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="exampleInputName" class="form-label">Product Name</label>
        <input required maxlength=100 type="text" name="name" class="form-control" id="exampleInputName">
      </div>
      <div class="mb-3">
        <label for="exampleInputType" class="form-label">Category</label>
        <div>
          <select required name="catagory" id="exampleInputType" class="form-control">
            <option value="" disabled selected hidden>Choose Category</option>
            <option value="Fiction">Fiction</option>
            <option value="Mystery & Crime">Mystery & Crime</option>
            <option value="Romance">Romance</option>
            <option value="Fantasy">Fantasy</option>
            <option value="Horror">Horror</option>
            <option value="Biography">Biography</option>
            <option value="Poetry">Poetry</option>
            <option value="Drama">Drama</option>
            <option value="Manga">Manga</option>
          </select>
        </div>
      </div>
      <div class="mb-3">
        <label for="exampleInputDescription" class="form-label">Description</label>
        <div class="input-group">
          <textarea required name="description" class="form-control" id="exampleInputDescription" aria-label="With textarea" maxlength=500></textarea>
        </div>

      </div>
      <div class="mb-3">
        <label for="exampleInputQuantity" class="form-label">Quantity</label>
        <input required pattern='[0-9]+' type="number" name="quantity" class="form-control" id="exampleInputQuantity">
      </div>
      <div class="mb-3">
        <label for="exampleInputPrice" class="form-label">Price</label>
        <input required pattern='[0-9]+([,\.][0-9]+)?' type="text" name="price" class="form-control" id="exampleInputPrice">
      </div>
      <div class="mb-3">
        <label for="uploadfile" class="form-label">Image</label>
        <input required accept="image/png, image/jpeg" type="file" name="uploadfile" class="form-control" />
      </div>
      <button type="submit" name="submit" class="btn btn-primary">Submit</button>
    </form>
  </div>
</body>

</html>