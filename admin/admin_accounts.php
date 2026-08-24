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
   $delete_admins = $conn->prepare("DELETE FROM `admins` WHERE id = ?");
   $delete_admins->execute([$delete_id]);
   header('location:admin_accounts.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>admin accounts</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="accounts">

   <h1 class="heading">admin accounts</h1>

   <div class="box-container">

   <div class="box">
      <p>add new admin</p>
      <a href="register_admin.php" class="option-btn">register admin</a>
   </div>

   <?php
      $select_accounts = $conn->prepare("SELECT * FROM `admins`");
      $select_accounts->execute();
      if($select_accounts->rowCount() > 0){
         while($fetch_accounts = $select_accounts->fetch(PDO::FETCH_ASSOC)){   
   ?>
   <div class="box">
      <p> admin id : <span><?= htmlspecialchars($fetch_accounts['id'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> admin name : <span><?= htmlspecialchars($fetch_accounts['name'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <div class="flex-btn">
         <a href="admin_accounts.php?delete=<?= htmlspecialchars($fetch_accounts['id'], ENT_QUOTES, 'UTF-8'); ?>&csrf_token=<?= urlencode(csrf_token()); ?>" onclick="return confirm('delete this account?')" class="delete-btn">delete</a>
         <?php
            if($fetch_accounts['id'] == $admin_id){
               echo '<a href="update_profile.php" class="option-btn">update</a>';
            }
         ?>
      </div>
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