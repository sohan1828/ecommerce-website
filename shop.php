<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

include 'components/wishlist_cart.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>shop</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="products">

   <h1 class="heading">Shop</h1>

   <div class="box-container">

   <?php
     $select_products = $conn->prepare("SELECT * FROM `products`"); 
     $select_products->execute();
     if($select_products->rowCount() > 0){
      while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
   ?>
   <form action="" method="post" class="box">
      <?= csrf_field(); ?>
      <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_product['id'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="price" value="<?= htmlspecialchars($fetch_product['price'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8'); ?>">
      <?= render_discount_badge($fetch_product['discount']); ?>
      <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
      <a href="quick_view.php?pid=<?= htmlspecialchars($fetch_product['id'], ENT_QUOTES, 'UTF-8'); ?>" class="fas fa-eye"></a>
      <a href="quick_view.php?pid=<?= $fetch_product['id']; ?>">
         <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($fetch_product['name']); ?>">
      </a>
      <div class="name"><?= htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8'); ?></div>
      <?= render_stars($fetch_product['rating'], $fetch_product['rating_count']); ?>
      <?= render_stock_note($fetch_product['stock']); ?>
      <div class="flex">
         <?= render_price($fetch_product['price'], $fetch_product['discount']); ?>
         <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
      </div>
      <input type="submit" value="add to cart" class="btn" name="add_to_cart" <?= $fetch_product['stock'] <= 0 ? 'disabled' : ''; ?>>
   </form>
   <?php
      }
   }else{
      echo '<p class="empty">no products found!</p>';
   }
   ?>

   </div>

</section>













<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>