<?php

include 'components/connect.php';

session_start();
csrf_verify_or_die();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   header('location:user_login.php');
   exit;
};

include 'components/wishlist_cart.php';

if(isset($_POST['delete'])){
   $wishlist_id = (int) $_POST['wishlist_id'];
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE id = ? AND user_id = ?");
   $delete_wishlist_item->execute([$wishlist_id, $user_id]);
}

if(isset($_GET['delete_all'])){
   $delete_wishlist_item = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist_item->execute([$user_id]);
   header('location:wishlist.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Wishlist</title>
   <!-- favicon -->
   <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="shortcut icon" href="favicon.ico">
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="products">

   <h3 class="heading">your wishlist</h3>

   <div class="box-container">

   <?php
      $grand_total = 0;
      $select_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
      $select_wishlist->execute([$user_id]);
      if($select_wishlist->rowCount() > 0){
         while($fetch_wishlist = $select_wishlist->fetch(PDO::FETCH_ASSOC)){
            $grand_total += $fetch_wishlist['price'];  
   ?>
   <form action="" method="post" class="box">
      <?= csrf_field(); ?>
      <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_wishlist['pid'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="wishlist_id" value="<?= htmlspecialchars($fetch_wishlist['id'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="name" value="<?= htmlspecialchars($fetch_wishlist['name'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="price" value="<?= htmlspecialchars($fetch_wishlist['price'], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="hidden" name="image" value="<?= htmlspecialchars($fetch_wishlist['image'], ENT_QUOTES, 'UTF-8'); ?>">
      <a href="quick_view.php?pid=<?= htmlspecialchars($fetch_wishlist['pid'], ENT_QUOTES, 'UTF-8'); ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= htmlspecialchars($fetch_wishlist['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($fetch_wishlist['name']); ?>">
      <div class="name"><?= htmlspecialchars($fetch_wishlist['name'], ENT_QUOTES, 'UTF-8'); ?></div>
      <div class="flex">
         <div class="price">Rs.<?= htmlspecialchars($fetch_wishlist['price'], ENT_QUOTES, 'UTF-8'); ?>/-</div>
         <input type="number" name="qty" class="qty" min="1" max="99" onkeypress="if(this.value.length == 2) return false;" value="1">
      </div>
      <input type="submit" value="add to cart" class="btn" name="add_to_cart">
      <input type="submit" value="delete item" onclick="return confirm('delete this from wishlist?');" class="delete-btn" name="delete">
   </form>
   <?php
      }
   }else{
      echo '<p class="empty">your wishlist is empty</p>';
   }
   ?>
   </div>

   <div class="wishlist-total">
      <p>grand total : <span>Rs.<?= $grand_total; ?>/-</span></p>
      <a href="shop.php" class="option-btn">continue shopping</a>
      <a href="wishlist.php?delete_all" class="delete-btn <?= ($grand_total > 1)?'':'disabled'; ?>" onclick="return confirm('delete all from wishlist?');">delete all item</a>
   </div>

</section>













<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>