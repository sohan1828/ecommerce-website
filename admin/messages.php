<?php

include '../components/connect.php';

session_start();

if(!isset($_SESSION['admin_id'])){
   header('location:admin_login.php');
   exit;
}
$admin_id = $_SESSION['admin_id'];;

if(isset($_GET['delete'])){
   if(!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'])){
      http_response_code(403);
      die('Sorry, this link has expired or could not be verified. Please go back and try again.');
   }
   $delete_id = $_GET['delete'];
   $delete_message = $conn->prepare("DELETE FROM `messages` WHERE id = ?");
   $delete_message->execute([$delete_id]);
   header('location:messages.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>messages</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="contacts">

<h1 class="heading">messages</h1>

<div class="box-container">

   <?php
      $select_messages = $conn->prepare("SELECT * FROM `messages`");
      $select_messages->execute();
      if($select_messages->rowCount() > 0){
         while($fetch_message = $select_messages->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
   <p> user id : <span><?= htmlspecialchars($fetch_message['user_id'], ENT_QUOTES, 'UTF-8'); ?></span></p>
   <p> name : <span><?= htmlspecialchars($fetch_message['name'], ENT_QUOTES, 'UTF-8'); ?></span></p>
   <p> email : <span><?= htmlspecialchars($fetch_message['email'], ENT_QUOTES, 'UTF-8'); ?></span></p>
   <p> number : <span><?= htmlspecialchars($fetch_message['number'], ENT_QUOTES, 'UTF-8'); ?></span></p>
   <p> message : <span><?= htmlspecialchars($fetch_message['message'], ENT_QUOTES, 'UTF-8'); ?></span></p>
   <a href="messages.php?delete=<?= htmlspecialchars($fetch_message['id'], ENT_QUOTES, 'UTF-8'); ?>&csrf_token=<?= urlencode(csrf_token()); ?>" onclick="return confirm('delete this message?');" class="delete-btn">delete</a>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">you have no messages</p>';
      }
   ?>

</div>

</section>












<script src="../js/admin_script.js"></script>
   
</body>
</html>