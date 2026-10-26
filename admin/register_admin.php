<?php

include '../components/connect.php';

session_start();
csrf_verify_or_die();

if(!isset($_SESSION['admin_id'])){
   header('location:admin_login.php');
   exit;
}
$admin_id = $_SESSION['admin_id'];

if(isset($_POST['submit'])){

   $name = trim(htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8'));
   $raw_pass = $_POST['pass'];
   $raw_cpass = $_POST['cpass'];

   $select_admin = $conn->prepare("SELECT * FROM `admins` WHERE name = ?");
   $select_admin->execute([$name]);

   if($select_admin->rowCount() > 0){
      $message[] = 'username already exist!';
   }else{
      if($raw_pass !== $raw_cpass){
         $message[] = 'confirm password not matched!';
      }elseif(strlen($raw_pass) < 8){
         $message[] = 'password must be at least 8 characters!';
      }else{
         $hashed_pass = password_hash($raw_pass, PASSWORD_DEFAULT);
         $insert_admin = $conn->prepare("INSERT INTO `admins`(name, password) VALUES(?,?)");
         $insert_admin->execute([$name, $hashed_pass]);
         $message[] = 'new admin registered successfully!';
      }
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>register admin</title>
   <!-- favicon -->
   <link rel="icon" type="image/svg+xml" href="../images/favicon.svg">
   <link rel="icon" type="image/png" href="../images/favicon.png">
   <link rel="shortcut icon" href="../favicon.ico">

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">

</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <?= csrf_field(); ?>
      <h3>register now</h3>
      <input type="text" name="name" required placeholder="enter your username" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="pass" required placeholder="enter your password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="cpass" required placeholder="confirm your password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="register now" class="btn" name="submit">
   </form>

</section>












<script src="../js/admin_script.js"></script>
   
</body>
</html>