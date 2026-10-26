<?php

include 'components/connect.php';

session_start();
csrf_verify_or_die();

if(isset($_SESSION['user_id'])){
   $user_id = (int)$_SESSION['user_id'];
}else{
   $user_id = 0;
};

if(isset($_POST['send'])){

   $name = trim(htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8'));
   $email = trim(htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8'));
   $number = trim(htmlspecialchars($_POST['number'], ENT_QUOTES, 'UTF-8'));
   $topic = trim(htmlspecialchars($_POST['topic'] ?? 'General', ENT_QUOTES, 'UTF-8'));
   $raw_msg = trim(htmlspecialchars($_POST['msg'], ENT_QUOTES, 'UTF-8'));

   $msg = "[Topic: {$topic}] " . $raw_msg;

   $select_message = $conn->prepare("SELECT * FROM `messages` WHERE name = ? AND email = ? AND number = ? AND message = ?");
   $select_message->execute([$name, $email, $number, $msg]);

   if($select_message->rowCount() > 0){
      $message[] = 'You have already sent this message!';
   }else{

      $insert_message = $conn->prepare("INSERT INTO `messages`(user_id, name, email, number, message) VALUES(?,?,?,?,?)");
      $insert_message->execute([$user_id, $name, $email, $number, $msg]);

      $message[] = 'Message sent successfully! Our support team will get back to you shortly.';

      // 1. Notify Admin via SMTP using .env credentials
      require_once __DIR__ . '/components/mailer.php';
      $adminEmail = env('ADMIN_EMAIL', 'sohandahal77@gmail.com');
      $contactSubject = "New Gizmo Customer Inquiry: [{$topic}] from {$name}";
      $contactHtml = "
         <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; max-width: 580px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 16px; background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
            <div style='background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); padding: 20px 24px; border-radius: 12px; margin-bottom: 20px;'>
               <span style='background: #7c3aed; color: #ffffff; font-size: 11px; font-weight: bold; text-transform: uppercase; padding: 4px 10px; border-radius: 9999px;'>
                  New Customer Inquiry
               </span>
               <h2 style='color: #ffffff; margin: 12px 0 0; font-size: 20px;'>Gizmo Store Contact Notification</h2>
            </div>
            <p style='color: #334155; font-size: 14px; margin: 6px 0;'><strong>Customer Name:</strong> " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "</p>
            <p style='color: #334155; font-size: 14px; margin: 6px 0;'><strong>Email Address:</strong> <a href='mailto:" . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "' style='color: #7c3aed; font-weight: 600;'>" . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "</a></p>
            <p style='color: #334155; font-size: 14px; margin: 6px 0;'><strong>Phone Number:</strong> " . htmlspecialchars($number, ENT_QUOTES, 'UTF-8') . "</p>
            <p style='color: #334155; font-size: 14px; margin: 6px 0;'><strong>Inquiry Topic:</strong> <span style='background: #ede9fe; color: #6d28d9; padding: 2px 8px; border-radius: 6px; font-weight: 600;'>" . htmlspecialchars($topic, ENT_QUOTES, 'UTF-8') . "</span></p>
            
            <div style='margin-top: 18px; padding: 16px; border-left: 4px solid #7c3aed; background: #f8fafc; border-radius: 8px;'>
               <strong style='color: #0f172a; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;'>Message:</strong>
               <p style='color: #475569; font-size: 14px; margin: 8px 0 0; line-height: 1.6;'>" . nl2br(htmlspecialchars($raw_msg, ENT_QUOTES, 'UTF-8')) . "</p>
            </div>
            <div style='margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center;'>
               &copy; " . date('Y') . " Gizmo Store Admin System
            </div>
         </div>
      ";
      send_mail($adminEmail, $contactSubject, $contactHtml);

      // 2. Send customer confirmation receipt
      $customerSubject = "We received your inquiry: [{$topic}] - Gizmo Store";
      $customerHtml = "
         <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; max-width: 580px; margin: 0 auto; padding: 24px; border: 1px solid #e2e8f0; border-radius: 16px; background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
            <div style='background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); padding: 20px 24px; border-radius: 12px; text-align: center; margin-bottom: 20px;'>
               <div style='display: inline-block; background: #7c3aed; color: #ffffff; font-weight: bold; font-size: 18px; padding: 8px 16px; border-radius: 10px;'>
                  ⚡ Gizmo Store Support
               </div>
               <h2 style='color: #ffffff; margin: 12px 0 0; font-size: 20px;'>Thank You For Contacting Us</h2>
            </div>
            <p style='color: #334155; font-size: 15px; line-height: 1.6;'>Hello <strong>" . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p style='color: #475569; font-size: 14px; line-height: 1.6;'>We received your inquiry regarding <strong>" . htmlspecialchars($topic, ENT_QUOTES, 'UTF-8') . "</strong>. Our support team is reviewing it and will respond to you shortly.</p>
            <div style='background: #f8fafc; border-left: 4px solid #7c3aed; padding: 14px; border-radius: 8px; margin: 18px 0;'>
               <div style='font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;'>Summary of Your Message</div>
               <p style='color: #334155; font-size: 14px; margin: 0; line-height: 1.6;'>" . nl2br(htmlspecialchars($raw_msg, ENT_QUOTES, 'UTF-8')) . "</p>
            </div>
            <p style='color: #64748b; font-size: 13px;'>If you have immediate questions, you can also reach us via phone at <strong>" . htmlspecialchars(env('SUPPORT_PHONE', '+977 9840000300'), ENT_QUOTES, 'UTF-8') . "</strong>.</p>
            <div style='margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center;'>
               &copy; " . date('Y') . " Gizmo Store. All rights reserved.
            </div>
         </div>
      ";
      send_mail($email, $customerSubject, $customerHtml);

   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Contact Us - Gizmo Store</title>
   <!-- favicon -->
   <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="shortcut icon" href="favicon.ico">
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom base css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <!-- modern contact-01 ecommerce theme css -->
   <link rel="stylesheet" href="css/contact-theme.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<main class="contact-page-wrapper">
   <div class="contact-grid">
      <!-- Left Column: Contact Info (Ecommerce Customer Support) -->
      <section class="contact-info-col">
         <div>
            <div class="contact-badge-pill">
               <i class="fas fa-headset"></i> Customer Care &amp; Support
            </div>
            <h1 class="contact-heading-main" style="margin-top: 1.2rem;">
               We're here to help your gadget journey
            </h1>
            <p class="contact-desc-text" style="margin-top: 0.8rem;">
               Have questions about an order, flagship smartphone specifications, warranty coverage, or delivery in Nepal? Reach out to our dedicated Gizmo team.
            </p>
         </div>

         <div class="contact-hero-banner">
            <img src="https://images.unsplash.com/photo-1534536281715-e28d76689b4d?q=80&w=1000&auto=format&fit=crop" alt="Gizmo Customer Service">
            <div class="contact-hero-overlay-tag">
               <span><i class="fas fa-shield-alt"></i> 100% Genuine Products &amp; Dedicated Tech Support</span>
            </div>
         </div>

         <div class="contact-cards-grid">
            <div class="contact-mini-card">
               <div class="contact-mini-card-header">
                  <i class="fas fa-phone-alt"></i> Phone Support
               </div>
               <p>+977 9840000300</p>
               <p>+977 9800000300</p>
            </div>

            <div class="contact-mini-card">
               <div class="contact-mini-card-header">
                  <i class="fas fa-envelope"></i> Email Inquiries
               </div>
               <p>gizmostore@gmail.com</p>
               <p>support@gizmo.com</p>
            </div>

            <div class="contact-mini-card">
               <div class="contact-mini-card-header">
                  <i class="fas fa-map-marker-alt"></i> Main Store
               </div>
               <p>Kathmandu 44600, Nepal</p>
               <p>New Road Tech Plaza</p>
            </div>

            <div class="contact-mini-card">
               <div class="contact-mini-card-header">
                  <i class="fas fa-clock"></i> Opening Hours
               </div>
               <p>Mon - Sat: 9:00 AM - 7:00 PM</p>
               <p>Sunday: 10:00 AM - 4:00 PM</p>
            </div>
         </div>
      </section>

      <!-- Right Column: Contact Form (shadcn Card design) -->
      <section class="contact-form-card">
         <h2 class="contact-card-title">Get in Touch</h2>
         <p class="contact-card-desc">
            Fill out this form and our support team will respond within 24 hours.
         </p>

         <form action="" method="post" class="contact-form-elements">
            <?= csrf_field(); ?>

            <div class="form-group-item">
               <label for="contact-name">Full Name</label>
               <input id="contact-name" type="text" name="name" required placeholder="e.g. John Doe" maxlength="40" class="contact-input-styled">
            </div>

            <div class="form-group-grid-2">
               <div class="form-group-item">
                  <label for="contact-email">Email Address</label>
                  <input id="contact-email" type="email" name="email" required placeholder="john@example.com" maxlength="50" class="contact-input-styled" oninput="this.value = this.value.replace(/\s/g, '')">
               </div>

               <div class="form-group-item">
                  <label for="contact-phone">Phone Number</label>
                  <input id="contact-phone" type="number" name="number" min="0" max="9999999999" required placeholder="9840000300" class="contact-input-styled" onkeypress="if(this.value.length == 10) return false;">
               </div>
            </div>

            <div class="form-group-item">
               <label for="contact-topic">Inquiry Topic</label>
               <select id="contact-topic" name="topic" class="contact-select-styled">
                  <option value="Order Status & Tracking">Order Status &amp; Tracking</option>
                  <option value="Product Specs & Availability">Product Specs &amp; Availability</option>
                  <option value="Warranty & Repairs">Warranty &amp; Repairs</option>
                  <option value="Returns & Refunds">Returns &amp; Refunds</option>
                  <option value="General Feedback">General Feedback</option>
               </select>
            </div>

            <div class="form-group-item">
               <label for="contact-msg">Your Message</label>
               <textarea id="contact-msg" name="msg" required placeholder="Tell us what you need assistance with..." rows="5" class="contact-textarea-styled"></textarea>
            </div>

            <button type="submit" name="send" class="contact-submit-btn-styled">
               <i class="fas fa-paper-plane"></i> Send Message
            </button>
         </form>
      </section>
   </div>
</main>

<?php include 'components/footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>