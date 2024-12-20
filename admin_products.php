<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
};

if(isset($_POST['add_product'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $price = $_POST['price'];
   $price = filter_var($price, FILTER_SANITIZE_STRING);
   $category = $_POST['category'];
   $category = filter_var($category, FILTER_SANITIZE_STRING);
   $details = $_POST['details'];
   $details = filter_var($details, FILTER_SANITIZE_STRING);
   $barcode = $_POST['barcode'];
   $barcode = filter_var($barcode, FILTER_SANITIZE_STRING);
   $quantity = $_POST['quantity'];
   $quantity = filter_var($quantity, FILTER_SANITIZE_NUMBER_INT);
   $couponCode = $_POST['couponCode'] ? filter_var($_POST['couponCode'], FILTER_SANITIZE_STRING) : null;
   $couponAmount = $_POST['couponAmount'] ? filter_var($_POST['couponAmount'], FILTER_SANITIZE_NUMBER_INT) : 0;

   $image = $_FILES['image']['name'];
   $image = filter_var($image, FILTER_SANITIZE_STRING);
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/' . $image;

   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_products->execute([$name]);

   if($select_products->rowCount() > 0){
      $message[] = 'Product name already exists!';
   }else{
      $insert_products = $conn->prepare("INSERT INTO `products`(name, category, details, price, barcode, quantity, image) VALUES(?,?,?,?,?,?,?)");
      $insert_products->execute([$name, $category, $details, $price, $barcode, $quantity, $image]);

      if($insert_products){
         $productID = $conn->lastInsertId();

         // Insert coupon data if provided
         if($couponCode && $couponAmount > 0){
            $insert_coupon = $conn->prepare("INSERT INTO `coupon` (couponCode, couponAmount, productID) VALUES (?, ?, ?)");
            $insert_coupon->execute([$couponCode, $couponAmount, $productID]);
         }

         if($image_size > 2000000){
            $message[] = 'Image size is too large!';
         }else{
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'New product added!';
         }
      }
   }
}

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];
   $select_delete_image = $conn->prepare("SELECT image FROM `products` WHERE id = ?");
   $select_delete_image->execute([$delete_id]);
   $fetch_delete_image = $select_delete_image->fetch(PDO::FETCH_ASSOC);
   unlink('uploaded_img/' . $fetch_delete_image['image']);
   $delete_products = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_products->execute([$delete_id]);
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ?");
   $delete_wishlist->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
   $delete_cart->execute([$delete_id]);
   header('location:admin_products.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>products</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="add-products">

   <h1 class="title">add new product</h1>

   <form action="" method="POST" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
            <input type="text" name="name" class="box" required placeholder="Enter product name">
            <select name="category" class="box" required>
               <option value="" selected disabled>Select category</option>
               <option value="vegetables">Vegetables</option>
               <option value="fruits">Fruits</option>
               <option value="chocolate">Chocolate</option>
               <option value="others">Others</option>
            </select>
            <input type="text" name="barcode" class="box" required placeholder="Enter product barcode">
            <input type="text" name="couponCode" class="box" placeholder="Enter coupon code (optional)">
            <input type="number" name="couponAmount" class="box" min="0" max="100" placeholder="Enter discount percentage (0-100)">
         </div>
         <div class="inputBox">
            <input type="number" min="0" name="price" class="box" required placeholder="Enter product price">
            <input type="number" min="1" name="quantity" class="box" required placeholder="Enter product quantity">
            <input type="file" name="image" required class="box" accept="image/jpg, image/jpeg, image/png">
         </div>
      </div>
      <textarea name="details" class="box" required placeholder="Enter product details" cols="30" rows="10"></textarea>
      <input type="submit" class="btn" value="Add product" name="add_product">
   </form>

</section>

<section class="show-products">

   <h1 class="title">products added</h1>

   <div class="box-container">

   <?php
      $show_products = $conn->prepare("SELECT * FROM `products`");
      $show_products->execute();
      if($show_products->rowCount() > 0){
         while($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)){  
   ?>
   <div class="box">
      <div class="price">$<?= $fetch_products['price']; ?>/-</div>
      <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
      <div class="name"><?= $fetch_products['name']; ?></div>
      <div class="cat"><?= $fetch_products['category']; ?></div>
      <div class="details"><?= $fetch_products['details']; ?></div>
      <div class="barcode">Barcode: <?= $fetch_products['barcode']; ?></div>
      <div class="quantity">Quantity: <?= $fetch_products['quantity']; ?></div>
      <div class="flex-btn">
         <a href="admin_update_product.php?update=<?= $fetch_products['id']; ?>" class="option-btn">update</a>
         <a href="admin_products.php?delete=<?= $fetch_products['id']; ?>" class="delete-btn" onclick="return confirm('delete this product?');">delete</a>
      </div>
   </div>
   <?php
      }
   }else{
      echo '<p class="empty">No products added yet!</p>';
   }
   ?>

   </div>

</section>

<script src="js/script.js"></script>

</body>
</html>
