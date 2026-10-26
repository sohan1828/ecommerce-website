<?php
   if(isset($message)){
      foreach($message as $message){
         echo '
         <div class="message">
            <span>'.$message.'</span>
            <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
         </div>
         ';
      }
   }
?>

<header class="header">

   <section class="flex">

      <a href="../admin/dashboard.php" class="logo">
         <img src="../images/gizmo-logo.svg" alt="Gizmo Logo" class="logo-icon">
         Gizmo <span>Admin</span>
      </a>

      <nav class="navbar" id="navbar">
         <a href="../admin/dashboard.php">Home</a>
         <a href="../admin/products.php">Products</a>
         <a href="../admin/placed_orders.php">Orders</a>
         <a href="../admin/admin_accounts.php">Admins</a>
         <a href="../admin/users_accounts.php">Users</a>
         <a href="../admin/messages.php">Messages</a>
      </nav>

      <div class="icons">
         <button id="menu-btn" type="button" class="fas fa-bars" aria-label="Toggle menu" aria-expanded="false" aria-controls="navbar"></button>
         <button id="user-btn" type="button" class="fas fa-user" aria-label="Account menu" aria-expanded="false" aria-haspopup="true"></button>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
            $select_profile->execute([$admin_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <p><?= htmlspecialchars($fetch_profile['name'], ENT_QUOTES, 'UTF-8'); ?></p>
         <a href="../admin/update_profile.php" class="btn">Update profile</a>
         <div class="flex-btn">
            <a href="../admin/register_admin.php" class="option-btn">Register</a>
            <a href="../admin/admin_login.php" class="option-btn">Login</a>
         </div>
         <a href="../components/admin_logout.php" class="delete-btn" onclick="return confirm('logout from the website?');">logout</a> 
      </div>

   </section>

</header>