<?php

include '../components/connect.php';

session_start();
csrf_verify_or_die();

if(!isset($_SESSION['admin_id'])){
   header('location:admin_login.php');
   exit;
}
$admin_id = $_SESSION['admin_id'];

if(isset($_POST['update_payment'])){
   $order_id = $_POST['order_id'];
   $payment_status = $_POST['payment_status'];
   $payment_status = trim($payment_status);
   $update_payment = $conn->prepare("UPDATE `orders` SET payment_status = ? WHERE id = ?");
   $update_payment->execute([$payment_status, $order_id]);
   $message[] = 'payment status updated!';
}

if(isset($_GET['delete'])){
   if(!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_GET['csrf_token'])){
      http_response_code(403);
      die('Sorry, this link has expired or could not be verified. Please go back and try again.');
   }
   $delete_id = $_GET['delete'];
   $delete_order = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
   $delete_order->execute([$delete_id]);
   header('location:placed_orders.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Placed Orders</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="orders">

<h1 class="heading">placed orders</h1>

<div class="box-container">

   <?php
      $select_orders = $conn->prepare("SELECT * FROM `orders`");
      $select_orders->execute();
      if($select_orders->rowCount() > 0){
         while($fetch_orders = $select_orders->fetch(PDO::FETCH_ASSOC)){
   ?>
   <div class="box">
      <span class="status-badge status-<?= htmlspecialchars($fetch_orders['payment_status'], ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($fetch_orders['payment_status'], ENT_QUOTES, 'UTF-8'); ?></span>
      <p> placed on : <span><?= htmlspecialchars($fetch_orders['placed_on'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> name : <span><?= htmlspecialchars($fetch_orders['name'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> number : <span><?= htmlspecialchars($fetch_orders['number'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> address : <span><?= htmlspecialchars($fetch_orders['address'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> total products : <span><?= htmlspecialchars($fetch_orders['total_products'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <p> total price : <span>Rs.<?= htmlspecialchars($fetch_orders['total_price'], ENT_QUOTES, 'UTF-8'); ?>/-</span> </p>
      <p> payment method : <span><?= htmlspecialchars($fetch_orders['method'], ENT_QUOTES, 'UTF-8'); ?></span> </p>
      <form action="" method="post">
      <?= csrf_field(); ?>
         <input type="hidden" name="order_id" value="<?= htmlspecialchars($fetch_orders['id'], ENT_QUOTES, 'UTF-8'); ?>">
         <select name="payment_status" class="select">
            <option selected disabled><?= htmlspecialchars($fetch_orders['payment_status'], ENT_QUOTES, 'UTF-8'); ?></option>
            <option value="pending">pending</option>
            <option value="completed">completed</option>
         </select>
        <div class="flex-btn">
         <input type="submit" value="update" class="option-btn" name="update_payment">
         <a href="placed_orders.php?delete=<?= htmlspecialchars($fetch_orders['id'], ENT_QUOTES, 'UTF-8'); ?>&csrf_token=<?= urlencode(csrf_token()); ?>" class="delete-btn" onclick="return confirm('delete this order?');">delete</a>
        </div>
      </form>
   </div>
   <?php
         }
      }else{
         echo '<p class="empty">no orders placed yet!</p>';
      }
   ?>

</div>

</section>

</section>












<script src="../js/admin_script.js"></script>
   
</body>
</html>