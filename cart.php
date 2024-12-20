<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
}

// Handle deleting a single cart item
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    $fetch_cart_quantity = $conn->prepare("SELECT quantity, pid FROM `cart` WHERE id = ?");
    $fetch_cart_quantity->execute([$delete_id]);
    $cart_item = $fetch_cart_quantity->fetch(PDO::FETCH_ASSOC);

    if ($cart_item) {
        $restore_quantity = $conn->prepare("UPDATE `products` SET quantity = quantity + ? WHERE id = ?");
        $restore_quantity->execute([$cart_item['quantity'], $cart_item['pid']]);
    }

    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
    $delete_cart_item->execute([$delete_id]);
    header('location:cart.php');
}

// Handle deleting all cart items
if (isset($_GET['delete_all'])) {
    $select_cart_items = $conn->prepare("SELECT quantity, pid FROM `cart` WHERE user_id = ?");
    $select_cart_items->execute([$user_id]);

    while ($cart_item = $select_cart_items->fetch(PDO::FETCH_ASSOC)) {
        $restore_quantity = $conn->prepare("UPDATE `products` SET quantity = quantity + ? WHERE id = ?");
        $restore_quantity->execute([$cart_item['quantity'], $cart_item['pid']]);
    }

    $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
    $delete_cart_item->execute([$user_id]);
    header('location:cart.php');
}

// Handle updating cart quantity
if (isset($_POST['update_qty'])) {
    $cart_id = $_POST['cart_id'];
    $p_qty = $_POST['p_qty'];
    $p_qty = filter_var($p_qty, FILTER_SANITIZE_STRING);

    $fetch_cart_item = $conn->prepare("SELECT quantity, pid FROM `cart` WHERE id = ?");
    $fetch_cart_item->execute([$cart_id]);
    $cart_item = $fetch_cart_item->fetch(PDO::FETCH_ASSOC);

    $fetch_product_quantity = $conn->prepare("SELECT quantity FROM `products` WHERE id = ?");
    $fetch_product_quantity->execute([$cart_item['pid']]);
    $product = $fetch_product_quantity->fetch(PDO::FETCH_ASSOC);

    if ($p_qty > $product['quantity'] + $cart_item['quantity']) {
        $message[] = 'Not enough stock available';
    } else {
        $quantity_diff = $p_qty - $cart_item['quantity'];
        $update_product_quantity = $conn->prepare("UPDATE `products` SET quantity = quantity - ? WHERE id = ?");
        $update_product_quantity->execute([$quantity_diff, $cart_item['pid']]);

        $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
        $update_qty->execute([$p_qty, $cart_id]);
        $message[] = 'Cart quantity updated';
    }
}

// Handle applying a coupon code
if (isset($_POST['apply_coupon'])) {
    $coupon_code = $_POST['coupon_code'];
    $coupon_code = filter_var($coupon_code, FILTER_SANITIZE_STRING);

    $check_coupon = $conn->prepare("SELECT * FROM `coupon` WHERE couponCode = ?");
    $check_coupon->execute([$coupon_code]);

    if ($check_coupon->rowCount() > 0) {
        $coupon = $check_coupon->fetch(PDO::FETCH_ASSOC);
        $product_id = $coupon['productID'];
        $discount = $coupon['couponAmount'];

        $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE pid = ? AND user_id = ?");
        $check_cart->execute([$product_id, $user_id]);

        if ($check_cart->rowCount() > 0) {
            $message[] = "Coupon applied! You saved $discount% on the product.";
            $_SESSION['applied_discount'][$product_id] = $discount;
        } else {
            $message[] = 'Coupon not applicable to any items in your cart.';
        }
    } else {
        $message[] = 'Invalid coupon code.';
    }
}

// Reset discounts if not applying a coupon
if (!isset($_POST['apply_coupon'])) {
    unset($_SESSION['applied_discount']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="shopping-cart">

    <h1 class="title">Products Added</h1>

    <div class="box-container">

    <?php
        $grand_total = 0;
        $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
        $select_cart->execute([$user_id]);
        if ($select_cart->rowCount() > 0) {
            while ($fetch_cart = $select_cart->fetch(PDO::FETCH_ASSOC)) { 
                $discounted_price = $fetch_cart['price'];
                if (isset($_SESSION['applied_discount'][$fetch_cart['pid']])) {
                    $discount = $_SESSION['applied_discount'][$fetch_cart['pid']];
                    $discounted_price = $fetch_cart['price'] - ($fetch_cart['price'] * $discount / 100);
                }
    ?>
    <form action="" method="POST" class="box">
        <a href="cart.php?delete=<?= $fetch_cart['id']; ?>" class="fas fa-times" onclick="return confirm('Delete this from cart?');"></a>
        <a href="view_page.php?pid=<?= $fetch_cart['pid']; ?>" class="fas fa-eye"></a>
        <img src="uploaded_img/<?= $fetch_cart['image']; ?>" alt="">
        <div class="name"><?= $fetch_cart['name']; ?></div>
        <div class="price">$<?= $discounted_price; ?>/-</div>
        <input type="hidden" name="cart_id" value="<?= $fetch_cart['id']; ?>">
        <div class="flex-btn">
            <input type="number" min="1" value="<?= $fetch_cart['quantity']; ?>" class="qty" name="p_qty">
            <input type="submit" value="update" name="update_qty" class="option-btn">
        </div>
        <div class="sub-total"> Sub Total: <span>$<?= $sub_total = ($discounted_price * $fetch_cart['quantity']); ?>/-</span> </div>
    </form>
    <?php
        $grand_total += $sub_total;
        }
    } else {
        echo '<p class="empty">Your cart is empty</p>';
    }
    ?>
    </div>

    <form action="" method="POST">
        <input type="text" name="coupon_code" placeholder="Enter coupon code" class="box">
        <input type="submit" value="Apply Coupon" name="apply_coupon" class="btn">
    </form>

    <div class="cart-total">
        <p>Grand Total: <span>$<?= $grand_total; ?>/-</span></p>
        <a href="shop.php" class="option-btn">Continue Shopping</a>
        <a href="cart.php?delete_all" class="delete-btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>">Delete All</a>
        <a href="checkout.php" class="btn <?= ($grand_total > 1) ? '' : 'disabled'; ?>">Proceed to Checkout</a>
    </div>
</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>