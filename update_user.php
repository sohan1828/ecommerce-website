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

$select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_profile->execute([$user_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['submit'])){

   $name = trim(htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8'));
   $email = trim(htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8'));

   $update_profile = $conn->prepare("UPDATE `users` SET name = ?, email = ? WHERE id = ?");
   $update_profile->execute([$name, $email, $user_id]);

   $raw_old_pass = $_POST['old_pass'];
   $raw_new_pass = $_POST['new_pass'];
   $raw_cpass = $_POST['cpass'];

   // Look up the current stored hash server-side instead of round-tripping it
   // through a hidden form field, which exposed the hash in the page HTML.
   $select_current = $conn->prepare("SELECT password FROM `users` WHERE id = ?");
   $select_current->execute([$user_id]);
   $current = $select_current->fetch(PDO::FETCH_ASSOC);

   if($raw_old_pass === ''){
      $message[] = 'please enter old password!';
   }elseif(!$current || !password_verify($raw_old_pass, $current['password'])){
      $message[] = 'old password not matched!';
   }elseif($raw_new_pass !== $raw_cpass){
      $message[] = 'confirm password not matched!';
   }else{
      if($raw_new_pass !== ''){
         $hashed_new_pass = password_hash($raw_new_pass, PASSWORD_DEFAULT);
         $update_admin_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
         $update_admin_pass->execute([$hashed_new_pass, $user_id]);
         $message[] = 'password updated successfully!';
      }else{
         $message[] = 'please enter a new password!';
      }
   }
   
   // Refresh so the form below reflects whatever just changed.
   $select_profile->execute([$user_id]);
   $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="form-container">

   <form action="" method="post">
      <?= csrf_field(); ?>
      <h3>update now</h3>
      <input type="text" name="name" required placeholder="enter your username" maxlength="20"  class="box" value="<?= htmlspecialchars($fetch_profile["name"], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="email" name="email" required placeholder="enter your email" maxlength="50"  class="box" oninput="this.value = this.value.replace(/\s/g, '')" value="<?= htmlspecialchars($fetch_profile["email"], ENT_QUOTES, 'UTF-8'); ?>">
      <input type="password" name="old_pass" placeholder="enter your old password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="new_pass" placeholder="enter your new password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="password" name="cpass" placeholder="confirm your new password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
      <input type="submit" value="update now" class="btn" name="submit">
   </form>

</section>













<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>