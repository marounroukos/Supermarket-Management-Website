<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>about</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="about">

   <div class="row">

      <div class="box">
         <img src="images/about-img-1.png" alt="">
         <h3>why choose us?</h3>
         <p>Because we’re not just a supermarket—we’re your partner in everyday living! We deliver quality you can trust, unbeatable prices, and convenience that fits your lifestyle. With a commitment to freshness, sustainability, and customer satisfaction, we’re here to make your shopping experience seamless and enjoyable. Choose us for better value, better service, and a better way to shop!</p>
         <a href="contact.php" class="btn">contact us</a>
      </div>

      <div class="box">
         <img src="images/about-img-2.png" alt="">
         <h3>what we provide?</h3>
         <p> Everything you need for your home and kitchen! From farm-fresh fruits and vegetables to pantry staples, premium meats, dairy products, and household essentials, we’ve got you covered. Plus, enjoy exclusive deals, eco-friendly options, and a seamless shopping experience with fast delivery or easy in-store pickup. At FreshNest Market, we provide quality, variety, and value—all in one place!</p>
         <a href="shop.php" class="btn">our shop</a>
      </div>

   </div>

</section>

<section class="reviews">

   <h1 class="title">Who are we?</h1>

   <div class="box-container">

      <div class="box">
         <img src="images/maroun.jpeg" alt="">
         <p>I'm Maroun Roukos, a dedicated computer engineering student at LAU with a keen eye for detail and a drive for excellence. I specialize in turning creative concepts into functional realities, using technology to enhance user experiences and deliver practical solutions.</p>
         <h3>Maroun Roukos</h3>
      </div>

      <div class="box">
         <img src="images/jurgen.jpeg" alt="">
         <p>I'm Jurgen Aouad, a computer engineering student at LAU with a passion for creating impactful digital solutions. I thrive on tackling challenges with innovative ideas and technical expertise, aiming to make everyday tasks easier and more efficient for everyone.</p>
         <h3>Jurgen Aouad</h3>
      </div>

   </div>

</section>









<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>