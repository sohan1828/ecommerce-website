<?php

include 'components/connect.php';
require_once 'components/google_auth.php';

session_start();
csrf_verify_or_die();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
   header('location:home.php');
   exit;
}else{
   $user_id = '';
};

// Check for session flash errors (e.g. from callback)
if (isset($_SESSION['auth_flash_error'])) {
   $message[] = $_SESSION['auth_flash_error'];
   unset($_SESSION['auth_flash_error']);
}

// 1. User clicked "Continue with Google"
if (isset($_GET['google_login'])) {
   $authUrl = get_google_auth_url();
   if ($authUrl) {
      header('Location: ' . $authUrl);
      exit;
   } else {
      $message[] = 'Google Client ID is not configured in .env';
   }
}

// 2. Google OAuth Redirect Callback (?code=... or ?error=...)
if (isset($_GET['code']) || isset($_GET['error'])) {
   $res = process_google_callback($conn);
   if (!empty($res['success'])) {
      header('location:home.php');
      exit;
   } elseif (!empty($res['error'])) {
      $message[] = $res['error'];
   }
}

if(isset($_POST['submit'])){

   $email = trim($_POST['email']);
   $raw_pass = $_POST['pass'];

   $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
   $select_user->execute([$email]);
   $row = $select_user->fetch(PDO::FETCH_ASSOC);

   if($row && password_verify($raw_pass, $row['password'])){
      session_regenerate_id(true);
      $_SESSION['user_id'] = $row['id'];
      header('location:home.php');
      exit;
   }else{
      $message[] = 'incorrect username or password!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Sign In - Gizmo Store</title>
   <!-- favicon -->
   <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="shortcut icon" href="favicon.ico">
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom base css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <!-- modern split-screen phone-theme auth css -->
   <link rel="stylesheet" href="css/sign-in-theme.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<main class="signin-page-wrapper">
   <!-- Left column: sign-in form -->
   <section class="signin-left-col">
      <div class="signin-form-box">
         <div class="signin-brand-badge animate-element animate-delay-100">
            <img src="images/gizmo-logo.svg" alt="Gizmo Logo" class="brand-logo-img">
            <span class="brand-logo-name">Gizmo Store</span>
         </div>
         <div class="animate-element animate-delay-100">
            <h1 class="signin-title">
               <span class="welcome-light">Welcome</span>
               Back
            </h1>
         </div>
         <p class="animate-element animate-delay-200 signin-subtitle">
            Access your account and continue your journey with us
         </p>

         <form action="" method="post" class="signin-form">
            <?= csrf_field(); ?>

            <div class="animate-element animate-delay-300 signin-input-group">
               <label class="signin-label" for="login-email">Email Address</label>
               <div class="glass-input-wrapper">
                  <input id="login-email" name="email" type="email" required placeholder="Enter your email address" maxlength="50" autocomplete="email" oninput="this.value = this.value.replace(/\s/g, '')">
               </div>
            </div>

            <div class="animate-element animate-delay-400 signin-input-group">
               <label class="signin-label" for="login-pass">Password</label>
               <div class="glass-input-wrapper">
                  <input id="login-pass" name="pass" type="password" required placeholder="Enter your password" maxlength="50" autocomplete="current-password" oninput="this.value = this.value.replace(/\s/g, '')">
                  <button type="button" id="toggle-password-btn" class="password-toggle-btn" aria-label="Toggle password visibility">
                     <i class="far fa-eye" id="toggle-eye-icon"></i>
                  </button>
               </div>
            </div>

            <div class="animate-element animate-delay-500 signin-meta-row">
               <label class="remember-label">
                  <input type="checkbox" name="rememberMe" class="custom-checkbox">
                  <span>Keep me signed in</span>
               </label>
               <a href="forgot_password.php" class="reset-link">Reset password</a>
            </div>

            <button type="submit" name="submit" class="animate-element animate-delay-600 signin-submit-btn">
               Sign In
            </button>
         </form>

         <div class="animate-element animate-delay-700 signin-divider-row">
            <span class="divider-line"></span>
            <span class="divider-text">Or continue with</span>
         </div>

         <a href="user_login.php?google_login=1" class="animate-element animate-delay-800 google-signin-btn">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48">
               <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s12-5.373 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-2.641-.21-5.236-.611-7.743z" />
               <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z" />
               <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z" />
               <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571l6.19 5.238C42.022 35.026 44 30.038 44 24c0-2.641-.21-5.236-.611-7.743z" />
            </svg>
            Continue with Google
         </a>

         <p class="animate-element animate-delay-900 signin-footer-text">
            New to our platform? <a href="user_register.php">Create Account</a>
         </p>
      </div>
   </section>

   <!-- Right column: clean phone hero theme (no chat bubbles) -->
   <section class="signin-right-col">
      <div class="phone-hero-canvas animate-slide-right animate-delay-300">
         <div class="phone-hero-overlay">
            <div class="phone-hero-badge">
               <i class="fas fa-mobile-alt"></i> Gizmo Flagship Smartphones
            </div>

            <div class="phone-showcase-box animate-element animate-delay-500">
               <h2 class="phone-showcase-title">Next-Gen Mobile Technology</h2>
               <p class="phone-showcase-desc">Experience flagship performance, high-refresh AMOLED displays, and studio-grade cameras curated for you.</p>
               <div class="phone-pills-row">
                  <span class="phone-pill"><i class="fas fa-bolt"></i> Fast Express Delivery</span>
                  <span class="phone-pill"><i class="fas fa-shield-alt"></i> 100% Genuine Warranty</span>
               </div>
            </div>
         </div>
      </div>
   </section>
</main>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>
<script>
   // Toggle password visibility
   const togglePassBtn = document.getElementById('toggle-password-btn');
   const passInput = document.getElementById('login-pass');
   const eyeIcon = document.getElementById('toggle-eye-icon');

   if (togglePassBtn && passInput && eyeIcon) {
      togglePassBtn.addEventListener('click', function() {
         const isPassword = passInput.type === 'password';
         passInput.type = isPassword ? 'text' : 'password';
         eyeIcon.className = isPassword ? 'far fa-eye-slash' : 'far fa-eye';
      });
   }
</script>

</body>
</html>