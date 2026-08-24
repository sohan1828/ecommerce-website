<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

include 'components/wishlist_cart.php';

$user_rating = null; // the current user's own existing rating for this product, if any

if(isset($_POST['submit_rating'])){
   $rate_pid = (int) $_POST['pid'];
   $rate_value = (int) $_POST['rating'];

   if($user_id === ''){
      header('location:user_login.php');
      exit;
   }elseif($rate_value < 1 || $rate_value > 5){
      $message[] = 'please choose a rating between 1 and 5 stars!';
   }else{
      $save_rating = $conn->prepare("INSERT INTO `product_ratings` (pid, user_id, rating) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE rating = VALUES(rating)");
      $save_rating->execute([$rate_pid, $user_id, $rate_value]);

      // Recalculate this product's average rating and count from every rating on file.
      $recalc = $conn->prepare("SELECT AVG(rating) AS avg_rating, COUNT(*) AS total FROM `product_ratings` WHERE pid = ?");
      $recalc->execute([$rate_pid]);
      $stats = $recalc->fetch(PDO::FETCH_ASSOC);

      $update_product = $conn->prepare("UPDATE `products` SET rating = ?, rating_count = ? WHERE id = ?");
      $update_product->execute([round($stats['avg_rating'], 1), $stats['total'], $rate_pid]);

      $message[] = 'thanks for rating this product!';
   }
}

if($user_id !== '' && isset($_GET['pid'])){
   $select_user_rating = $conn->prepare("SELECT rating FROM `product_ratings` WHERE pid = ? AND user_id = ?");
   $select_user_rating->execute([(int) $_GET['pid'], $user_id]);
   $existing = $select_user_rating->fetch(PDO::FETCH_ASSOC);
   if($existing){
      $user_rating = (int) $existing['rating'];
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>quick view</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="quick-view">

   <h1 class="heading">quick view</h1>

   <?php
     $pid = $_GET['pid'];
     $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?"); 
     $select_products->execute([$pid]);
     if($select_products->rowCount() > 0){
      while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" method="post" class="box">
      <?= csrf_field(); ?>
      <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_product['id'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="price" value="<?= htmlspecialchars($fetch_product['price'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8'); ?>">
      <div class="row">
         <div class="image-container">
            <div class="main-image">
               <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($fetch_product['name']); ?>">
            </div>
            <div class="sub-image">
               <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($fetch_product['name']); ?> view 1">
               <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_02'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($fetch_product['name']); ?> view 2">
               <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_03'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($fetch_product['name']); ?> view 3">
            </div>
         </div>
         <div class="content">
            <div class="name"><?= htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?= render_stars($fetch_product['rating'], $fetch_product['rating_count']); ?>
            <?= render_stock_note($fetch_product['stock']); ?>
            <div class="flex">
               <?= render_price($fetch_product['price'], $fetch_product['discount']); ?>
               <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
            </div>
            <div class="details"><?= htmlspecialchars($fetch_product['details'], ENT_QUOTES, 'UTF-8'); ?></div>
            <div class="flex-btn">
               <input type="submit" value="add to cart" class="btn" name="add_to_cart" <?= $fetch_product['stock'] <= 0 ? 'disabled' : ''; ?>>
               <input class="option-btn" type="submit" name="add_to_wishlist" value="add to wishlist">
            </div>
         </div>
      </div>
   </form>

   <form action="" method="post" class="rate-product">
      <?= csrf_field(); ?>
      <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_product['id'], ENT_QUOTES, 'UTF-8'); ?>">
      <h3><?= $user_rating ? 'update your rating' : 'rate this product'; ?></h3>
      <div class="star-input">
         <?php for($i = 5; $i >= 1; $i--){ ?>
         <input type="radio" id="star<?= $i; ?>" name="rating" value="<?= $i; ?>" <?= $user_rating == $i ? 'checked' : ''; ?> required>
         <label for="star<?= $i; ?>" title="<?= $i; ?> stars"><i class="fas fa-star"></i></label>
         <?php } ?>
      </div>
      <input type="submit" value="<?= $user_rating ? 'update rating' : 'submit rating'; ?>" class="option-btn" name="submit_rating">
   </form>
   <?php
      }
   }else{
      echo '<p class="empty">no products added yet!</p>';
   }
   ?>

</section>













<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>