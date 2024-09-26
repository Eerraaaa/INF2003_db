<?php
 include'header.php';
 include'lib/connection.php';


if(isset($_SESSION['auth']))
{
   if($_SESSION['auth']!=1)
   {
       header("location:login.php");
   }
}
else
{
   header("location:login.php");
}
if(isset($_POST['order_btn'])){
  $userid = $_POST['user_id'];
  $name = $_POST['user_name'];
  $number = $_POST['number'];
  $address = $_POST['address'];
  $mobnumber = $_POST['mobnumber'];
  $txid = $_POST['txid'];
  /*$price_total = $_POST['total'];*/
  $status="pending";

  $cart_query = mysqli_query($conn, "SELECT * FROM `cart` where userid='$userid'");
  $price_total = 0;
  if(mysqli_num_rows($cart_query) > 0){
     while($product_item = mysqli_fetch_assoc($cart_query)){
        $product_name[] = $product_item['productid'] .' ('. $product_item['quantity'] .') ';
        $product_price = number_format($product_item['price'] * $product_item['quantity']);
        $price_total += $product_price;
        $sql = "SELECT * FROM product";
        $result = $conn -> query ($sql);
      
        if (mysqli_num_rows($result) > 0) {
          // output data of each row
          while($row = mysqli_fetch_assoc($result)) {
            if($row['id']===$product_item['productid'])
            {
              if($product_item['quantity']<=$row['quantity'])
              {
                $update_id=$row['id'];
                $t=$row['quantity']-$product_item['quantity'];
                $update_quantity_query = mysqli_query($conn, "UPDATE `product` SET quantity = '$t' WHERE id = '$update_id'");
                

                $flag=1;


                

              }
              else
              {
                echo "out of stock " .$row['name']." Quantity:".$row['quantity'];
              }
            }
          }

        }

     };
     if($flag==1)
     {
       $total_product = implode(', ',$product_name);
       $detail_query = mysqli_query($conn, "INSERT INTO `orders`(userid, name, address, phone,  mobnumber, txid, totalproduct, totalprice, status) VALUES('$userid','$name','$address','$number','$mobnumber','$txid','$total_product','$price_total','$status')") or die($conn -> error);
           
             $cart_query1 = mysqli_query($conn, "delete FROM `cart` where userid='$userid'");
             header("location:index.php");

     }
  };



}

$id=$_SESSION['userid'];
 $sql = "SELECT * FROM cart where userid='$id'";
 $result = $conn -> query ($sql);

 if(isset($_POST['update_update_btn'])){
  $update_value = $_POST['update_quantity'];
  $update_id = $_POST['update_quantity_id'];
  $update_quantity_query = mysqli_query($conn, "UPDATE `cart` SET quantity = '$update_value' WHERE id = '$update_id'");
  if($update_quantity_query){
     header('location:cart.php');
  };
};

if(isset($_GET['remove'])){
  $remove_id = $_GET['remove'];
  mysqli_query($conn, "DELETE FROM `cart` WHERE id = '$remove_id'");
  header('location:cart.php');
};


?>

<div class="container pendingbody">
  <h5>cart</h5>
<table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Name</th>
      <th scope="col">Quantity</th>
      <th scope="col">Price</th>
      <th scope="col">Action</th>
    </tr>
  </thead>
  <tbody>
  <?php
  $total=0;
          if (mysqli_num_rows($result) > 0) {
            // output data of each row
            while($row = mysqli_fetch_assoc($result)) {
              ?>
    <tr>
      <th scope="row">1</th>
      <td><?php echo $row["name"] ?></td>
  
      <td><form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <input type="hidden" name="update_quantity_id"  value="<?php echo  $row['id']; ?>" >
        <input type="number" name="update_quantity" min="1"  value="<?php echo $row['quantity']; ?>" >
        <input type="submit" value="update" name="update_update_btn">
      </form></td> 
      <td><?php echo $row["price"]*$row["quantity"]  ?></td>
      <?php $total=$total+$row["price"]*$row["quantity"] ;?>
     

      <input type="hidden" name="status" value="pending">   
      <td><a href="cart.php?remove=<?php echo $row['id']; ?>">remove</a></td>
    </tr>
    <?php 
    }
    echo "total=" . $total;
        } 
        else 
            echo "0 results";
        ?>
  <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">

        <h5>if Cash On delivary Then Put 0 in bkash Field</h5>
      <div class="input-group form-group">
      <input type="hidden" name="total" value="<?php echo $total ?>">
      <input type="hidden" name="user_id" value="<?php echo $_SESSION['userid']; ?>">
      <input type="hidden" name="user_name" value="<?php echo $_SESSION['username']; ?>">
        <input type="text" class="form-control" placeholder="Address" name="address">
       </div>
       <div class="input-group form-group">
        <input type="number" class="form-control" placeholder="Phone Number" name="number">
       </div>
       <div class="input-group form-group">
        <input type="number" class="form-control" placeholder="Bkash/Nogod/Rocket Number" name="mobnumber">
       </div>
       <div class="input-group form-group">
        <input type="text" class="form-control" placeholder="Txid" name="txid">
       </div>

      <div class="form-group">
      <input type="submit" value="Order Now" name="order_btn">
    </div>

    </form>
  </tbody>
</table>
</div>


<?php
 include'footer.php';
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php
            include "inc/headproduct.inc.php";
        ?> 
        <link rel="stylesheet" href="css/cart.css">
    </head>
    
    <body>
        <main class="container mt-3 mb-5">
            <header>
                <div class="row mt-3 nav-container">
                    <div class="col-sm-12 col-md-6">
                        ORDER ONLINE OR CALL US (1800) 000 8080
                    </div>
                    <nav class="col-sm-12 col-md-6 nav-item">
                        <ul>              
                            <li><a href="#"><i class="fa-solid fa-user"></i><span>Account</span></a></li>
                            <li><a href="#"><i class="fa-solid fa-heart"></i><span>Wishlist</span></a></li>
                            <li><a href="#">SGD&nbsp;<i class="fa-solid fa-chevron-down"></i></a></li>
                        </ul>
                    </nav>
                </div>
            </header>
            <div class="row search-bar mt-3">
                <div class="col-12 col-lg-2">Logo</div>
                <i class="col-2 col-lg-1 fa-sharp fa-solid fa-bars icon"></i>
                <form class="col-8 col-lg-7">
                    <input type="search" class="form-control" placeholder="Search" aria-label="search">
                    <button class="btn btn-primary">Search</button>
                </form>
                <div class="col-2 col-lg-2 cart">
                    <a href="#">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span>MY CART</span>
                    </a>
                </div>
            </div>
            <div class="breadcrumb mt-5">
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Your Shopping Cart</a></li>
                </ul>
            </div>

            <div class="row">
                <div class="col-xs-12 col-lg-7 item-list" >
                    <div style="border: 1px solid black">
                        <div class="row mt-3">
                            <div class="title"><h4>Your Shopping Cart</h4></div>
                        </div>
                        <br>
                        <div class="cart-container">
                            <!-- Start Item-->
                            <div class="row">
                                <div class="col-3">
                                <img class="img-fluid" src='https://images-fe.ssl-images-amazon.com/images/I/814mI0-rkxL._AC_UL160_SR160,160_.jpg' width="100%">
                                </div>
                                <div class="col">
                                    <div class="item-details-container mb-3">
                                        <h4>Elon Musk Walter Isaacson</h4>
                                        <p>By Author</p>
                                        <div class="row">
                                            <p class="col price"><span>$14.99</span></p>
                                            <div class="col quantity-input-container">
                                                <button class="quantity-button remove-item" type="button">
                                                    <span class="visually-hidden">Remove item</span>
                                                    <i class="fa-solid fa-minus"></i>
                                                </button>                
                                                <input class="quantity-input" type="number" name="quantity" value="1" min="1" aria-label="Quantity">
                                                <button class="quantity-button add-item" type="button">
                                                    <span class="visually-hidden">Add item</span>
                                                    <i class="fa-solid fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary remove-button">Remove</button>
                                </div>
                            </div>
                            <br>
                            <!-- End item-->
                        </div>
                    </div>
                    

                </div>
                <div class="col-xs-12 col-lg-5 summary-container">
                    <div class="summary-border" style="border: 1px solid black;">
                        <h2>Cart Summary</h2>
                        <p>FREE SHIPPING with minimum purchase of S$20.00
                        </p>
                        <div class="d-flex">
                            <h6>Delivery fee: </h6> 
                            <span>$2.99</span>
                        </div>
                        <div class="d-flex">
                            <h6>Saving: </h6> 
                            <span>(-$0.00)</span>
                        </div>
                        <hr>
                        <div class="d-flex">
                            <h6>Total Cart Value: </h6> 
                            <span>$29.98</span>
                        </div>
                        <hr>
                        <div>
                            <h6>Special instruction</h6>
                            <textarea name="note" id="CartNote" rows="8" form="cart"></textarea>
                        </div>
                        <button class="btn btn-primary checkout-button">Check Out</button>
                        <button class="btn btn-primary continue-button">Continue Shopping</button>
                    </div>

                </div>
            </div>

        </main>

        <?php
        include "inc/footer.inc.php";
        ?> 
    </body>
</html>