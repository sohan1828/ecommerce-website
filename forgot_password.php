<?php

include 'components/connect.php';

session_start();
csrf_verify_or_die();

if(isset($_SESSION['user_id'])){
   $user_id = $_SESSION['user_id'];
   header('location:home.php');
   exit;
}else{
   $user_id = '';
};

require_once __DIR__ . '/components/mailer.php';

// Allow user to cancel or restart the recovery process
if(isset($_GET['action']) && $_GET['action'] === 'restart'){
   unset($_SESSION['reset_email'], $_SESSION['reset_step'], $_SESSION['reset_verified'], $_SESSION['reset_simulated_otp']);
   header('location:forgot_password.php');
   exit;
}

// Current step resolution
$current_step = isset($_SESSION['reset_step']) ? (int)$_SESSION['reset_step'] : 1;
if(empty($_SESSION['reset_email']) && $current_step > 1){
   $current_step = 1;
   $_SESSION['reset_step'] = 1;
}
if($current_step === 3 && empty($_SESSION['reset_verified'])){
   $current_step = 2;
   $_SESSION['reset_step'] = 2;
}

$reset_success = ($current_step === 4);

// Handle POST submissions
if($_SERVER['REQUEST_METHOD'] === 'POST'){
   $action = $_POST['action'] ?? '';

   // --- STEP 1: REQUEST OTP ---
   if($action === 'send_otp'){
      $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));

      if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
         $message[] = 'Please enter a valid email address!';
      }else{
         $select_user = $conn->prepare("SELECT id, name FROM `users` WHERE email = ? LIMIT 1");
         $select_user->execute([$email]);
         $user = $select_user->fetch(PDO::FETCH_ASSOC);

         if(!$user){
            $message[] = 'No account found with this email address!';
         }else{
            // Generate secure 6-digit numeric OTP
            $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

            // Delete old codes for this email and insert fresh one
            $del_old = $conn->prepare("DELETE FROM `password_resets` WHERE email = ?");
            $del_old->execute([$email]);

            $ins_otp = $conn->prepare("INSERT INTO `password_resets` (email, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
            $ins_otp->execute([$email, $otp]);

            // Dispatch branded OTP email
            $userName = !empty($user['name']) ? htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') : 'Gizmo Customer';
            $subject = "Your Gizmo Store Verification Code: " . $otp;
            $htmlMsg = "
            <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; max-width: 540px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
               <div style='background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); padding: 32px 24px; text-align: center;'>
                  <div style='display: inline-block; background: #7c3aed; color: #ffffff; font-weight: 700; font-size: 18px; padding: 10px 18px; border-radius: 12px; letter-spacing: 0.5px;'>
                     ⚡ Gizmo Store
                  </div>
                  <h1 style='color: #ffffff; margin: 18px 0 0; font-size: 22px; font-weight: 700;'>Password Reset Verification</h1>
               </div>
               <div style='padding: 32px 28px; color: #334155; font-size: 15px; line-height: 1.6;'>
                  <p style='margin-top: 0;'>Hello <strong>{$userName}</strong>,</p>
                  <p>We received a request to reset your password for your <strong>Gizmo Store</strong> account. Use the 6-digit verification code below to verify your identity:</p>
                  
                  <div style='background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 22px; text-align: center; margin: 24px 0;'>
                     <div style='font-size: 12px; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 1.2px; margin-bottom: 8px;'>Your Verification Code</div>
                     <div style='font-size: 38px; font-weight: 800; letter-spacing: 8px; color: #7c3aed; font-family: monospace;'>{$otp}</div>
                     <div style='font-size: 13px; color: #94a3b8; margin-top: 8px;'>Valid for <strong>15 minutes</strong></div>
                  </div>
                  
                  <p style='font-size: 14px; color: #64748b; margin-bottom: 0;'>If you did not request a password reset, you can safely ignore this email. Your account credentials remain fully protected.</p>
               </div>
               <div style='background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 28px; font-size: 12px; color: #94a3b8; text-align: center;'>
                  &copy; " . date('Y') . " Gizmo Store. All rights reserved.
               </div>
            </div>";

            $mailResult = send_mail($email, $subject, $htmlMsg);

            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_step'] = 2;
            $_SESSION['reset_verified'] = false;

            if(!empty($mailResult['simulated'])){
               $_SESSION['reset_simulated_otp'] = $otp;
            }else{
               unset($_SESSION['reset_simulated_otp']);
            }

            $current_step = 2;
            $message[] = 'Verification code sent! Please check your email inbox.';
         }
      }
   }

   // --- STEP 2 RESEND: RESEND OTP ---
   elseif($action === 'resend_otp' && !empty($_SESSION['reset_email'])){
      $email = $_SESSION['reset_email'];
      $otp = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

      $del_old = $conn->prepare("DELETE FROM `password_resets` WHERE email = ?");
      $del_old->execute([$email]);

      $ins_otp = $conn->prepare("INSERT INTO `password_resets` (email, otp, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
      $ins_otp->execute([$email, $otp]);

      $subject = "Your New Gizmo Store Verification Code: " . $otp;
      $htmlMsg = "
      <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; max-width: 540px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
         <div style='background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); padding: 32px 24px; text-align: center;'>
            <div style='display: inline-block; background: #7c3aed; color: #ffffff; font-weight: 700; font-size: 18px; padding: 10px 18px; border-radius: 12px; letter-spacing: 0.5px;'>
               ⚡ Gizmo Store
            </div>
            <h1 style='color: #ffffff; margin: 18px 0 0; font-size: 22px; font-weight: 700;'>New Verification Code</h1>
         </div>
         <div style='padding: 32px 28px; color: #334155; font-size: 15px; line-height: 1.6;'>
            <p style='margin-top: 0;'>Hello,</p>
            <p>Here is your new 6-digit verification code to reset your Gizmo Store password:</p>
            
            <div style='background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 22px; text-align: center; margin: 24px 0;'>
               <div style='font-size: 12px; text-transform: uppercase; color: #64748b; font-weight: 700; letter-spacing: 1.2px; margin-bottom: 8px;'>Your New Code</div>
               <div style='font-size: 38px; font-weight: 800; letter-spacing: 8px; color: #7c3aed; font-family: monospace;'>{$otp}</div>
               <div style='font-size: 13px; color: #94a3b8; margin-top: 8px;'>Valid for <strong>15 minutes</strong></div>
            </div>
         </div>
         <div style='background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 28px; font-size: 12px; color: #94a3b8; text-align: center;'>
            &copy; " . date('Y') . " Gizmo Store. All rights reserved.
         </div>
      </div>";

      $mailResult = send_mail($email, $subject, $htmlMsg);

      if(!empty($mailResult['simulated'])){
         $_SESSION['reset_simulated_otp'] = $otp;
      }else{
         unset($_SESSION['reset_simulated_otp']);
      }

      $current_step = 2;
      $message[] = 'A fresh verification code has been dispatched to your email.';
   }

   // --- STEP 2: VERIFY OTP ---
   elseif($action === 'verify_otp' && !empty($_SESSION['reset_email'])){
      $email = $_SESSION['reset_email'];
      $otp = preg_replace('/\D/', '', $_POST['otp'] ?? '');

      if(strlen($otp) !== 6){
         $message[] = 'Please enter a 6-digit verification code!';
         $current_step = 2;
      }else{
         $check_otp = $conn->prepare("SELECT * FROM `password_resets` WHERE email = ? AND otp = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
         $check_otp->execute([$email, $otp]);

         if($check_otp->rowCount() > 0){
            $_SESSION['reset_step'] = 3;
            $_SESSION['reset_verified'] = true;
            unset($_SESSION['reset_simulated_otp']);
            $current_step = 3;
            $message[] = 'Code verified successfully! Now choose your new password.';
         }else{
            $message[] = 'Invalid or expired verification code. Please try again.';
            $current_step = 2;
         }
      }
   }

   // --- STEP 3: UPDATE PASSWORD ---
   elseif($action === 'update_password' && !empty($_SESSION['reset_email']) && !empty($_SESSION['reset_verified'])){
      $email = $_SESSION['reset_email'];
      $new_pass = $_POST['new_pass'] ?? '';
      $cpass = $_POST['cpass'] ?? '';

      if(strlen($new_pass) < 8){
         $message[] = 'Password must be at least 8 characters long!';
         $current_step = 3;
      }elseif($new_pass !== $cpass){
         $message[] = 'Confirm password does not match!';
         $current_step = 3;
      }else{
         $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
         $update_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE email = ?");
         $update_pass->execute([$hashed_pass, $email]);

         // Clean up reset records
         $del_resets = $conn->prepare("DELETE FROM `password_resets` WHERE email = ?");
         $del_resets->execute([$email]);

         // Send confirmation notice
         $subject = "Your Gizmo Store password has been successfully reset";
         $htmlMsg = "
         <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; max-width: 540px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
            <div style='background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); padding: 32px 24px; text-align: center;'>
               <div style='display: inline-block; background: #10b981; color: #ffffff; font-weight: 700; font-size: 18px; padding: 10px 18px; border-radius: 12px;'>
                  ✓ Password Updated
               </div>
               <h1 style='color: #ffffff; margin: 18px 0 0; font-size: 22px; font-weight: 700;'>Gizmo Store Security Alert</h1>
            </div>
            <div style='padding: 32px 28px; color: #334155; font-size: 15px; line-height: 1.6;'>
               <p style='margin-top: 0;'>Hello,</p>
               <p>Your password for your <strong>Gizmo Store</strong> account (<strong>" . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "</strong>) has just been successfully updated.</p>
               <p>If you made this change, no further action is required.</p>
               <p style='color: #dc2626; font-size: 14px;'>If you did NOT make this change, please contact Gizmo support immediately.</p>
            </div>
            <div style='background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 28px; font-size: 12px; color: #94a3b8; text-align: center;'>
               &copy; " . date('Y') . " Gizmo Store. All rights reserved.
            </div>
         </div>";
         send_mail($email, $subject, $htmlMsg);

         // Clean session
         unset($_SESSION['reset_email'], $_SESSION['reset_step'], $_SESSION['reset_verified'], $_SESSION['reset_simulated_otp']);
         $current_step = 4;
         $reset_success = true;
         $message[] = 'Password reset successfully! Please sign in with your new password.';
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
   <title>Reset Password - Gizmo Store</title>
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
   <!-- Left column: forgot password form -->
   <section class="signin-left-col">
      <div class="signin-form-box">
         <div class="signin-brand-badge animate-element animate-delay-100">
            <img src="images/gizmo-logo.svg" alt="Gizmo Logo" class="brand-logo-img">
            <span class="brand-logo-name">Gizmo Store</span>
         </div>

         <!-- Multi-step Indicator -->
         <div class="auth-steps-row animate-element animate-delay-100">
            <div class="auth-step-pill <?= ($current_step === 1 ? 'active' : ($current_step > 1 ? 'completed' : '')) ?>">
               <i class="fas <?= ($current_step > 1 ? 'fa-check' : 'fa-envelope') ?>"></i>
               <span>1. Email</span>
            </div>
            <div class="auth-step-connector"></div>
            <div class="auth-step-pill <?= ($current_step === 2 ? 'active' : ($current_step > 2 ? 'completed' : '')) ?>">
               <i class="fas <?= ($current_step > 2 ? 'fa-check' : 'fa-key') ?>"></i>
               <span>2. OTP Code</span>
            </div>
            <div class="auth-step-connector"></div>
            <div class="auth-step-pill <?= ($current_step === 3 ? 'active' : ($current_step >= 4 ? 'completed' : '')) ?>">
               <i class="fas <?= ($current_step >= 4 ? 'fa-check' : 'fa-lock') ?>"></i>
               <span>3. New Password</span>
            </div>
         </div>

         <div class="animate-element animate-delay-200">
            <h1 class="signin-title">
               <?php if($current_step === 1): ?>
                  <span class="welcome-light">Account Recovery</span>
                  Reset Password
               <?php elseif($current_step === 2): ?>
                  <span class="welcome-light">Verification</span>
                  Enter OTP Code
               <?php elseif($current_step === 3): ?>
                  <span class="welcome-light">Almost Done</span>
                  Set New Password
               <?php else: ?>
                  <span class="welcome-light">All Done!</span>
                  Password Updated
               <?php endif; ?>
            </h1>
         </div>

         <p class="animate-element animate-delay-200 signin-subtitle">
            <?php if($current_step === 1): ?>
               Enter your registered email address to receive a secure 6-digit OTP verification code.
            <?php elseif($current_step === 2): ?>
               Enter the 6-digit verification code dispatched to your inbox.
            <?php elseif($current_step === 3): ?>
               Identity verified! Please set a strong new password with at least 8 characters.
            <?php else: ?>
               Your password has been changed successfully. You can now log into Gizmo.
            <?php endif; ?>
         </p>

         <!-- STEP 4: SUCCESS DISPLAY -->
         <?php if($current_step >= 4 || $reset_success): ?>
            <div class="animate-element animate-delay-300" style="background: rgba(16, 185, 129, 0.1); border: 1.5px solid #10b981; border-radius: 1.4rem; padding: 2.2rem; color: #065f46; font-size: 1.45rem; line-height: 1.6;">
               <strong style="display: flex; align-items: center; gap: 0.8rem; font-size: 1.7rem; margin-bottom: 0.6rem; color: #047857;">
                  <i class="fas fa-check-circle" style="font-size: 2.2rem;"></i> Password Reset Successfully!
               </strong>
               <p style="margin: 0.8rem 0 1.8rem; color: #065f46;">
                  Your password has been updated. You can now log in to your Gizmo account with your new credentials.
               </p>
               <a href="user_login.php" class="signin-submit-btn" style="display: block; text-align: center; text-decoration: none;">
                  <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i> Proceed to Sign In
               </a>
            </div>

         <!-- STEP 1: ENTER EMAIL -->
         <?php elseif($current_step === 1): ?>
            <form action="" method="post" class="signin-form">
               <?= csrf_field(); ?>
               <input type="hidden" name="action" value="send_otp">

               <div class="animate-element animate-delay-300 signin-input-group">
                  <label class="signin-label" for="reset-email">Registered Email Address</label>
                  <div class="glass-input-wrapper">
                     <input id="reset-email" name="email" type="email" required placeholder="Enter your registered email" maxlength="60" autocomplete="email" oninput="this.value = this.value.replace(/\s/g, '')" autofocus>
                  </div>
               </div>

               <button type="submit" class="animate-element animate-delay-400 signin-submit-btn">
                  <i class="fas fa-paper-plane" style="margin-right: 8px;"></i> Send Verification Code
               </button>
            </form>

         <!-- STEP 2: VERIFY OTP -->
         <?php elseif($current_step === 2): ?>
            <div class="auth-info-card animate-element animate-delay-300">
               <div>
                  <i class="fas fa-envelope-open-text" style="color: #7c3aed; margin-right: 6px;"></i>
                  Verification code dispatched to: <strong><?= htmlspecialchars($_SESSION['reset_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
               </div>
               <div style="font-size: 1.25rem; color: #64748b;">
                  Please check your inbox and spam folder. Code is valid for 15 minutes.
               </div>
               <?php if(!empty($_SESSION['reset_simulated_otp'])): ?>
                  <div class="auth-dev-badge">
                     <i class="fas fa-info-circle"></i>
                     <span>Test Mode OTP: <strong><?= htmlspecialchars($_SESSION['reset_simulated_otp'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                  </div>
               <?php endif; ?>
            </div>

            <form action="" method="post" class="signin-form">
               <?= csrf_field(); ?>
               <input type="hidden" name="action" value="verify_otp">

               <div class="animate-element animate-delay-400 signin-input-group">
                  <label class="signin-label" for="reset-otp">6-Digit Verification Code</label>
                  <div class="glass-input-wrapper">
                     <input id="reset-otp" name="otp" type="text" class="otp-code-input" required maxlength="6" pattern="[0-9]{6}" inputmode="numeric" autocomplete="one-time-code" placeholder="······" autofocus oninput="this.value = this.value.replace(/\D/g, '')">
                  </div>
               </div>

               <button type="submit" class="animate-element animate-delay-500 signin-submit-btn">
                  <i class="fas fa-shield-alt" style="margin-right: 8px;"></i> Verify Code
               </button>
            </form>

            <div class="auth-inline-actions animate-element animate-delay-600">
               <form action="" method="post" style="display: inline; margin: 0;">
                  <?= csrf_field(); ?>
                  <input type="hidden" name="action" value="resend_otp">
                  <button type="submit" class="auth-link-btn">
                     <i class="fas fa-redo-alt" style="margin-right: 4px;"></i> Resend Code
                  </button>
               </form>
               <a href="forgot_password.php?action=restart" class="auth-link-btn secondary">
                  <i class="fas fa-arrow-left" style="margin-right: 4px;"></i> Change Email
               </a>
            </div>

         <!-- STEP 3: NEW PASSWORD -->
         <?php elseif($current_step === 3): ?>
            <div class="auth-info-card animate-element animate-delay-300">
               <div>
                  <i class="fas fa-user-check" style="color: #059669; margin-right: 6px;"></i>
                  Identity verified for: <strong><?= htmlspecialchars($_SESSION['reset_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
               </div>
            </div>

            <form action="" method="post" class="signin-form">
               <?= csrf_field(); ?>
               <input type="hidden" name="action" value="update_password">

               <div class="animate-element animate-delay-400 signin-input-group">
                  <label class="signin-label" for="reset-pass">New Password (Min 8 characters)</label>
                  <div class="glass-input-wrapper">
                     <input id="reset-pass" name="new_pass" type="password" required placeholder="Enter your new password" minlength="8" maxlength="50" autocomplete="new-password" oninput="this.value = this.value.replace(/\s/g, '')" autofocus>
                     <button type="button" id="toggle-reset-pass-btn" class="password-toggle-btn" aria-label="Toggle password visibility">
                        <i class="far fa-eye" id="toggle-reset-eye"></i>
                     </button>
                  </div>
               </div>

               <div class="animate-element animate-delay-500 signin-input-group">
                  <label class="signin-label" for="reset-cpass">Confirm New Password</label>
                  <div class="glass-input-wrapper">
                     <input id="reset-cpass" name="cpass" type="password" required placeholder="Confirm your new password" minlength="8" maxlength="50" autocomplete="new-password" oninput="this.value = this.value.replace(/\s/g, '')">
                     <button type="button" id="toggle-reset-cpass-btn" class="password-toggle-btn" aria-label="Toggle confirm password visibility">
                        <i class="far fa-eye" id="toggle-reset-ceye"></i>
                     </button>
                  </div>
               </div>

               <button type="submit" class="animate-element animate-delay-600 signin-submit-btn">
                  <i class="fas fa-lock" style="margin-right: 8px;"></i> Update Password
               </button>
            </form>
         <?php endif; ?>

         <div class="animate-element animate-delay-700 signin-divider-row">
            <span class="divider-line"></span>
            <span class="divider-text">Back to</span>
         </div>

         <p class="animate-element animate-delay-800 signin-footer-text">
            Remember your credentials? <a href="user_login.php">Sign In</a> or <a href="user_register.php">Create Account</a>
         </p>
      </div>
   </section>

   <!-- Right column: clean phone hero theme (no chat bubbles) -->
   <section class="signin-right-col">
      <div class="phone-hero-canvas animate-slide-right animate-delay-300">
         <div class="phone-hero-overlay">
            <div class="phone-hero-badge">
               <i class="fas fa-shield-halved"></i> Secure Account Protection
            </div>

            <div class="phone-showcase-box animate-element animate-delay-500">
               <h2 class="phone-showcase-title">Your Security is Our Priority</h2>
               <p class="phone-showcase-desc">We use industry-standard encryption to protect your personal details, order history, and gadget warranties.</p>
               <div class="phone-pills-row">
                  <span class="phone-pill"><i class="fas fa-lock"></i> 256-bit Encryption</span>
                  <span class="phone-pill"><i class="fas fa-user-shield"></i> Instant Recovery</span>
               </div>
            </div>
         </div>
      </div>
   </section>
</main>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>
<script>
   function setupPasswordToggle(btnId, inputId, iconId) {
      const btn = document.getElementById(btnId);
      const input = document.getElementById(inputId);
      const icon = document.getElementById(iconId);

      if (btn && input && icon) {
         btn.addEventListener('click', function() {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            icon.className = isPassword ? 'far fa-eye-slash' : 'far fa-eye';
         });
      }
   }

   setupPasswordToggle('toggle-reset-pass-btn', 'reset-pass', 'toggle-reset-eye');
   setupPasswordToggle('toggle-reset-cpass-btn', 'reset-cpass', 'toggle-reset-ceye');
</script>

</body>
</html>
