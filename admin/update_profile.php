<?php

include '../components/connect.php';

session_start();
csrf_verify_or_die();

$admin_id = $_SESSION['admin_id'] ?? null;

if(!isset($admin_id)){
   header('location:admin_login.php');
   exit;
}

$select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
$select_profile->execute([$admin_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

if(isset($_POST['submit'])){

   $name = trim(htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8'));

   $update_profile_name = $conn->prepare("UPDATE `admins` SET name = ? WHERE id = ?");
   $update_profile_name->execute([$name, $admin_id]);

   $raw_old_pass = $_POST['old_pass'];
   $raw_new_pass = $_POST['new_pass'];
   $raw_confirm_pass = $_POST['confirm_pass'];

   $select_current = $conn->prepare("SELECT password FROM `admins` WHERE id = ?");
   $select_current->execute([$admin_id]);
   $current = $select_current->fetch(PDO::FETCH_ASSOC);

   if($raw_old_pass === ''){
      $message[] = 'please enter old password!';
   }elseif(!$current || !password_verify($raw_old_pass, $current['password'])){
      $message[] = 'old password not matched!';
   }elseif($raw_new_pass !== $raw_confirm_pass){
      $message[] = 'confirm password not matched!';
   }else{
      if($raw_new_pass !== ''){
         $hashed_new_pass = password_hash($raw_new_pass, PASSWORD_DEFAULT);
         $update_admin_pass = $conn->prepare("UPDATE `admins` SET password = ? WHERE id = ?");
         $update_admin_pass->execute([$hashed_new_pass, $admin_id]);
         $message[] = 'password updated successfully!';
      }else{
         $message[] = 'please enter a new password!';
      }
   }
   
   // Refresh so the form below reflects whatever just changed.
   $select_profile->execute([$admin_id]);
   $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>update profile</title>
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
      <h3>update profile</h3>
          <input type="text" name="name" value="<?= htmlspecialchars($fetch_profile['name'], ENT_QUOTES, 'UTF-8'); ?>" required placeholder="enter your username" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
          <input type="password" name="old_pass" placeholder="enter old password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
          <input type="password" name="new_pass" placeholder="enter new password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
          <input type="password" name="confirm_pass" placeholder="confirm new password" maxlength="20"  class="box" oninput="this.value = this.value.replace(/\s/g, '')">
          <input type="submit" value="update now" class="btn" name="submit">
   </form>
   

</section>












<script src="../js/admin_script.js"></script>
   
</body>
</html>