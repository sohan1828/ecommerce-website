<?php

include 'components/connect.php';

session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';

include 'components/wishlist_cart.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Home</title>

   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">



</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<div class="home-bg">
   <section class="home">
      <div class="swiper home-slider">
         <div class="swiper-wrapper">
            <div class="swiper-slide slide">
               <div class="image">
                  <img src="images/home-img-1.png" alt="Latest smartphones on sale">
               </div>
               <div class="content">
                  <span>Upto 30% off</span>
                  <h3>Latest smartphones</h3>
                  <p>Flagship performance, the newest cameras, and all-day battery life &mdash; find your next phone.</p>
                  <a href="shop.php" class="btn">shop now</a>
               </div>
            </div>

            <div class="swiper-slide slide">
               <div class="image">
                  <img src="images/home-img-2.png" alt="Latest watches on sale">
               </div>
               <div class="content">
                  <span>Upto 30% off</span>
                  <h3>Latest watches</h3>
                  <p>Track your fitness, stay connected, and look sharp doing it.</p>
                  <a href="shop.php" class="btn">shop now</a>
               </div>
            </div>

            <div class="swiper-slide slide">
               <div class="image">
                  <img src="images/home-img-3.png" alt="Latest headsets on sale">
               </div>
               <div class="content">
                  <span>Upto 30% off</span>
                  <h3>Latest headsets</h3>
                  <p>Immersive sound and noise cancellation for work, gaming, and everything between.</p>
                  <a href="shop.php" class="btn">shop now</a>
               </div>
            </div>
         </div>

         <div class="swiper-pagination"></div>
      </div>
   </section>
</div>

<section class="category">
   <h1 class="heading">Shop by Category</h1>

   <div class="swiper category-slider">
      <div class="swiper-wrapper">
         <a href="category.php?category=laptop" class="swiper-slide slide">
            <img src="images/icon-1.png" alt="">
            <h3>Laptop</h3>
         </a>

         <a href="category.php?category=tv" class="swiper-slide slide">
            <img src="images/icon-2.png" alt="">
            <h3>TV</h3>
         </a>

         <a href="category.php?category=camera" class="swiper-slide slide">
            <img src="images/icon-3.png" alt="">
            <h3>Camera</h3>
         </a>

         <a href="category.php?category=mouse" class="swiper-slide slide">
            <img src="images/icon-4.png" alt="">
            <h3>Mouse</h3>
         </a>

         <a href="category.php?category=fridge" class="swiper-slide slide">
            <img src="images/icon-5.png" alt="">
            <h3>Refrigerator</h3>
         </a>

         <a href="category.php?category=washing" class="swiper-slide slide">
            <img src="images/icon-6.png" alt="">
            <h3>Washing machine</h3>
         </a>

         <a href="category.php?category=smartphone" class="swiper-slide slide">
            <img src="images/icon-7.png" alt="">
            <h3>Smartphone</h3>
         </a>

         <a href="category.php?category=watch" class="swiper-slide slide">
            <img src="images/icon-8.png" alt="">
            <h3>Watch</h3>
         </a>

         <a href="category.php?category=mixers" class="swiper-slide slide">
            <img src="images/icon-9.svg" alt="">
            <h3>Mixers</h3>
         </a>
      </div>

      <div class="swiper-pagination"></div>
   </div>
</section>

<section class="home-products">
   <h1 class="heading">Latest products</h1>

   <div class="swiper products-slider">
      <div class="swiper-wrapper">
         <?php
         $select_products = $conn->prepare("SELECT * FROM `products` LIMIT 6"); 
         $select_products->execute();
         if($select_products->rowCount() > 0){
            while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
         ?>
         <form action="" method="post" class="swiper-slide slide">
      <?= csrf_field(); ?>
            <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_product['id'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="price" value="<?= htmlspecialchars($fetch_product['price'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8'); ?>">
            <?= render_discount_badge($fetch_product['discount']); ?>
            <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
            <a href="quick_view.php?pid=<?= htmlspecialchars($fetch_product['id'], ENT_QUOTES, 'UTF-8'); ?>" class="fas fa-eye"></a>
            <img src="uploaded_img/<?= htmlspecialchars($fetch_product['image_01'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($fetch_product['name']); ?>">
            <div class="name"><?= htmlspecialchars($fetch_product['name'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?= render_stars($fetch_product['rating'], $fetch_product['rating_count']); ?>
            <?= render_stock_note($fetch_product['stock']); ?>
            <div class="flex">
               <?= render_price($fetch_product['price'], $fetch_product['discount']); ?>
               <input type="number" name="qty" class="qty" min="1" max="99" value="1">
            </div>
            <input type="submit" value="add to cart" class="btn" name="add_to_cart" <?= $fetch_product['stock'] <= 0 ? 'disabled' : ''; ?>>
         </form>
         <?php
            }
         }else{
            echo '<p class="empty">no products added yet!</p>';
         }
         ?>
      </div>

      <div class="swiper-pagination"></div>
   </div>
</section>

<?php
   
   $show_deals = false;
   try {
      $select_deals = $conn->prepare("SELECT * FROM `products` WHERE discount > 0 LIMIT 6");
      $select_deals->execute();
      $show_deals = $select_deals->rowCount() > 0;
   } catch (PDOException $e) {
      error_log('Deals section skipped: ' . $e->getMessage());
   }
   if($show_deals){
?>
<section class="home-products">
   <h1 class="heading">Best deals</h1>

   <div class="swiper deals-slider">
      <div class="swiper-wrapper">
         <?php
            while($fetch_deal = $select_deals->fetch(PDO::FETCH_ASSOC)){
         ?>
         <form action="" method="post" class="swiper-slide slide">
      <?= csrf_field(); ?>
            <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_deal['id'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_deal['name'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="price" value="<?= htmlspecialchars($fetch_deal['price'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_deal['image_01'], ENT_QUOTES, 'UTF-8'); ?>">
            <?= render_discount_badge($fetch_deal['discount']); ?>
            <button class="fas fa-heart" type="submit" name="add_to_wishlist"></button>
            <a href="quick_view.php?pid=<?= htmlspecialchars($fetch_deal['id'], ENT_QUOTES, 'UTF-8'); ?>" class="fas fa-eye"></a>
            <img src="uploaded_img/<?= htmlspecialchars($fetch_deal['image_01'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($fetch_deal['name'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="name"><?= htmlspecialchars($fetch_deal['name'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?= render_stars($fetch_deal['rating'], $fetch_deal['rating_count']); ?>
            <?= render_stock_note($fetch_deal['stock']); ?>
            <div class="flex">
               <?= render_price($fetch_deal['price'], $fetch_deal['discount']); ?>
               <input type="number" name="qty" class="qty" min="1" max="99" value="1">
            </div>
            <input type="submit" value="add to cart" class="btn" name="add_to_cart" <?= $fetch_deal['stock'] <= 0 ? 'disabled' : ''; ?>>
         </form>
         <?php
            }
         ?>
      </div>

      <div class="swiper-pagination"></div>
   </div>
</section>
<?php
   }
?>


   <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
<script src="js/script.js"></script>

<?php include 'components/footer.php'; ?>





</body>
</html>