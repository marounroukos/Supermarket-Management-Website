<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
};

if(isset($_POST['order'])){

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_STRING);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_STRING);
   $method = $_POST['method'];
   $method = filter_var($method, FILTER_SANITIZE_STRING);
   $address = 'flat no. '. $_POST['flat'] .' '. $_POST['street'] .' '. $_POST['city'] .' '. $_POST['state'] .' '. $_POST['country'] .' - '. $_POST['pin_code'];
   $address = filter_var($address, FILTER_SANITIZE_STRING);
   $placed_on = date('d-M-Y');

   $cart_total = 0;
   $cart_products = [];
   $discount_total = 0;

   $cart_query = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $cart_query->execute([$user_id]);
   if($cart_query->rowCount() > 0){
      while($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)){
         $product_id = $cart_item['pid'];
         $product_name = $cart_item['name'].' ( '.$cart_item['quantity'].' )';
         $cart_products[] = $product_name;

         $original_price = $cart_item['price'];
         $discounted_price = $original_price;

         // Apply discount only if the coupon is for this product
         if(isset($_SESSION['applied_discount'][$product_id])){
            $discount = $_SESSION['applied_discount'][$product_id];
            $discounted_price -= ($original_price * $discount / 100);
            $discount_total += ($original_price * $discount / 100) * $cart_item['quantity'];
         }

         $sub_total = $discounted_price * $cart_item['quantity'];
         $cart_total += $sub_total;
      }
   }

   $total_products = implode(', ', $cart_products);

   $order_query = $conn->prepare("SELECT * FROM `orders` WHERE name = ? AND number = ? AND email = ? AND method = ? AND address = ? AND total_products = ? AND total_price = ?");
   $order_query->execute([$name, $number, $email, $method, $address, $total_products, $cart_total]);

   if($cart_total == 0){
      $message[] = 'Your cart is empty.';
   }elseif($order_query->rowCount() > 0){
      $message[] = 'Order already placed!';
   }else{
      $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, discount_applied, placed_on) VALUES(?,?,?,?,?,?,?,?,?,?)");
      $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $cart_total, $discount_total, $placed_on]);
      
      $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart->execute([$user_id]);
      unset($_SESSION['applied_discount']); // Clear applied discounts
      $message[] = 'Order placed successfully!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout</title>

   <!-- font awesome cdn link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="display-orders">

   <?php
      $cart_grand_total = 0;
      $select_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart_items->execute([$user_id]);
      if($select_cart_items->rowCount() > 0){
         while($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)){
            $product_id = $fetch_cart_items['pid'];
            $original_price = $fetch_cart_items['price'];
            $discounted_price = $original_price;

            if (isset($_SESSION['applied_discount'][$product_id])) {
               $discount = $_SESSION['applied_discount'][$product_id];
               $discounted_price -= ($original_price * $discount / 100);
            }

            $cart_total_price = ($discounted_price * $fetch_cart_items['quantity']);
            $cart_grand_total += $cart_total_price;
   ?>
   
   <?php
         }
      }else{
         echo '<p class="empty">Your cart is empty!</p>';
      }
   ?>
   <div class="grand-total">Grand Total: <span>$<?= $cart_grand_total; ?>/-</span></div>
</section>

<section class="checkout-orders">

   <form action="" method="POST">

      <h3>Place Your Order</h3>

      <div class="flex">
         <div class="inputBox">
            <span>Your Name:</span>
            <input type="text" name="name" placeholder="Enter your name" class="box" required>
         </div>
         <div class="inputBox">
            <span>Your Number:</span>
            <input type="number" name="number" placeholder="Enter your number" class="box" required>
         </div>
         <div class="inputBox">
            <span>Your Email:</span>
            <input type="email" name="email" placeholder="Enter your email" class="box" required>
         </div>
         <div class="inputBox">
            <span>Payment Method:</span>
            <select name="method" class="box" required>
               <option value="cash on delivery">Cash on Delivery</option>
               <option value="credit card">Credit Card</option>
               <option value="paytm">Paytm</option>
               <option value="paypal">PayPal</option>
            </select>
         </div>
         <div class="inputBox">
            <span>Address Line 01:</span>
            <input type="text" name="flat" placeholder="e.g. Flat Number" class="box" required>
         </div>
         <div class="inputBox">
            <span>Address Line 02:</span>
            <input type="text" name="street" placeholder="e.g. Street Name" class="box" required>
         </div>
         <div class="inputBox">
            <span>City:</span>
            <input type="text" name="city" placeholder="e.g. Mumbai" class="box" required>
         </div>
         <div class="inputBox">
            <span>State:</span>
            <input type="text" name="state" placeholder="e.g. Maharashtra" class="box" required>
         </div>
         <div class="inputBox">
            <span>Country:</span>
            <input type="text" name="country" placeholder="e.g. India" class="box" required>
         </div>
         <div class="inputBox">
            <span>Pin Code:</span>
            <input type="number" min="0" name="pin_code" placeholder="e.g. 123456" class="box" required>
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($cart_grand_total > 1) ? '' : 'disabled'; ?>" value="Place Order">

   </form>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
