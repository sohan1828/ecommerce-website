<?php
include 'components/connect.php'; // Include your database connection script

if (isset($message)) {
    foreach ($message as $message) {
        echo '
        <div class="message">
              <span>'.htmlspecialchars($message, ENT_QUOTES, 'UTF-8').'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
        </div>
        ';
    }
}
?>

<header class="header">

   <section class="flex">

      <a href="home.php" class="logo">Gizmo</a>

      <nav class="navbar" id="navbar">
         <a href="home.php">Home</a>
         <div class="nav-dropdown">
            <a href="shop.php" class="nav-primary">Categories <i class="fas fa-chevron-down"></i></a>
            <div class="nav-dropdown-menu">
               <a href="category.php?category=laptop">Laptop</a>
               <a href="category.php?category=tv">TV</a>
               <a href="category.php?category=camera">Camera</a>
               <a href="category.php?category=mouse">Mouse</a>
               <a href="category.php?category=fridge">Refrigerator</a>
               <a href="category.php?category=washing">Washing machine</a>
               <a href="category.php?category=smartphone">Smartphone</a>
               <a href="category.php?category=watch">Watch</a>
               <a href="category.php?category=mixers">Mixers</a>
            </div>
         </div>
         <a href="shop.php" class="nav-primary">Shop</a>
         <a href="about.php">About</a>
         <a href="orders.php">Orders</a>
         <a href="contact.php">Contact</a>
      </nav>

      <div class="icons">
         <?php
            // Now $conn is available here
            $count_wishlist_items = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
            $count_wishlist_items->execute([$user_id]);
            $total_wishlist_counts = $count_wishlist_items->rowCount();

            $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $total_cart_counts = $count_cart_items->rowCount();
         ?>
         <button id="menu-btn" type="button" class="fas fa-bars" aria-label="Toggle menu" aria-expanded="false" aria-controls="navbar"></button>
         <a href="search_page.php" aria-label="Search"><i class="fas fa-search"></i></a>
         <a href="wishlist.php" aria-label="Wishlist"><i class="fas fa-heart"></i><span>(<?= $total_wishlist_counts; ?>)</span></a>
         <a href="cart.php" aria-label="Cart"><i class="fas fa-shopping-cart"></i><span>(<?= $total_cart_counts; ?>)</span></a>
         <button id="user-btn" type="button" class="fas fa-user" aria-label="Account menu" aria-expanded="false" aria-haspopup="true"></button>
      </div>

      <div class="profile">
         <?php          
            $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
            $select_profile->execute([$user_id]);
            if($select_profile->rowCount() > 0){
                $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p><?= htmlspecialchars($fetch_profile["name"], ENT_QUOTES, 'UTF-8'); ?></p>
         <a href="update_user.php" class="btn">Update Profile</a>
         <div class="flex-btn">
            <a href="user_register.php" class="option-btn">Register</a>
            <a href="user_login.php" class="option-btn">Login</a>
         </div>
         <a href="components/user_logout.php" class="delete-btn" onclick="return confirm('logout from the website?');">Logout</a> 
         <?php
            }else{
         ?>
         <p>please login or register first!</p>
         <div class="flex-btn">
            <a href="user_register.php" class="option-btn">Register</a>
            <a href="user_login.php" class="option-btn">Login</a>
         </div>
         <?php
            }
         ?>      
      </div>

   </section>

</header>