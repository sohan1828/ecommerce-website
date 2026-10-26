<?php

include '../components/connect.php';

session_start();

if(!isset($_SESSION['admin_id'])){
   header('location:admin_login.php');
   exit;
}
$admin_id = $_SESSION['admin_id'];

if(isset($_GET['delete'])){
   if(!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'])){
      http_response_code(403);
      die('Sorry, this link has expired or could not be verified. Please go back and try again.');
   }
   $delete_id = $_GET['delete'];
   $delete_user = $conn->prepare("DELETE FROM `users` WHERE id = ?");
   $delete_user->execute([$delete_id]);
   $delete_orders = $conn->prepare("DELETE FROM `orders` WHERE user_id = ?");
   $delete_orders->execute([$delete_id]);
   $delete_messages = $conn->prepare("DELETE FROM `messages` WHERE user_id = ?");
   $delete_messages->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart->execute([$delete_id]);
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE user_id = ?");
   $delete_wishlist->execute([$delete_id]);
   header('location:users_accounts.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>users accounts</title>
   <!-- favicon -->
   <link rel="icon" type="image/svg+xml" href="../images/favicon.svg">
   <link rel="icon" type="image/png" href="../images/favicon.png">
   <link rel="shortcut icon" href="../favicon.ico">

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="accounts">

   <h1 class="heading">user accounts</h1>

   <div class="box-container">

   <?php
      $select_accounts = $conn->prepare("SELECT * FROM `users`");
      $select_accounts->execute();
      if($select_accounts->rowCount() > 0){
         while($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)){   
   ?>
   <div class="box">
      <p> user id : <span><?= htmlspecialchars($fetch_accounts['id'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> username : <span><?= htmlspecialchars($fetch_accounts['name'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> email : <span><?= htmlspecialchars($fetch_accounts['email'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <a href="users_accounts.php?delete=<?= htmlspecialchars($fetch_accounts['id'], ENT_QUOTES, 'UTF-8'); ?>&csrf_token=<?= urlencode(csrf_token()); ?>" onclick="return confirm('delete this account? the user related information will also be delete!')" class="delete-btn">delete</a>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">no accounts available!</p>';
      }
   ?>

   </div>

</section>












<script src="../js/admin_script.js"></script>
   
</body>
</html>