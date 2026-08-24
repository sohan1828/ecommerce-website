<?php

include 'components/connect.php';

session_start();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
}else{
   $user_id = '';
};

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>orders</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="orders">

   <h1 class="heading">Placed orders</h1>

   <div class="box-container">

   <?php
      if($user_id == ''){
         echo '<p class="empty">please login to see your orders</p>';
      }else{
         $select_orders = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ?");
         $select_orders->execute([$user_id]);
         if($select_orders->rowCount() > 0){
            while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <span class="status-badge status-<?= htmlspecialchars($fetch_orders['payment_status'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($fetch_orders['payment_status'], ENT_QUOTES, 'UTF-8'); ?></span>
      <p>placed on : <span><?= htmlspecialchars($fetch_orders['placed_on'], ENT_QUOTES, 'UTF-8'); ?></span></p>
      <p>name : <span><?= htmlspecialchars($fetch_orders['name'], ENT_QUOTES, 'UTF-8'); ?></span></p>
      <p>email : <span><?= htmlspecialchars($fetch_orders['email'], ENT_QUOTES, 'UTF-8'); ?></span></p>
      <p>number : <span><?= htmlspecialchars($fetch_orders['number'], ENT_QUOTES, 'UTF-8'); ?></span></p>
      <p>address : <span><?= htmlspecialchars($fetch_orders['address'], ENT_QUOTES, 'UTF-8'); ?></span></p>
      <p>payment method : <span><?= htmlspecialchars($fetch_orders['method'], ENT_QUOTES, 'UTF-8'); ?></span></p>
      <p>your orders : <span><?= htmlspecialchars($fetch_orders['total_products'], ENT_QUOTES, 'UTF-8'); ?></span></p>
      <p>total price : <span>Rs.<?= htmlspecialchars($fetch_orders['total_price'], ENT_QUOTES, 'UTF-8'); ?>/-</span></p>
   </div>
   <?php
      }
      }else{
         echo '<p class="empty">no orders placed yet!</p>';
      }
      }
   ?>

   </div>

</section>













<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>