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
   <title>about</title>
   <!-- favicon -->
   <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
   <link rel="icon" type="image/png" href="images/favicon.png">
   <link rel="shortcut icon" href="favicon.ico">

   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
   
   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'components/user_header.php'; ?>

<section class="about">

   <div class="row">

      <div class="image">
         <img src="images/about-img.svg" alt="Illustration of our team and products">
      </div>

      <div class="content">
         <h3>why choose us?</h3>
         <p>At GIZMO ZONE , our mission is to provide the highest quality products with exceptional customer service.
             We meticulously select each item to ensure it meets our strict standards of excellence.
             Your satisfaction is our top priority, and we are committed to exceeding your expectations every time you shop with us.</p>
         <a href="contact.php" class="btn">contact us</a>
      </div>

   </div>

</section>

<section class="reviews">
   
   <h1 class="heading">client's reviews</h1>

   <div class="swiper reviews-slider">

   <div class="swiper-wrapper">

      <div class="swiper-slide slide">
         <img src="images/pic-1.png" alt="Bruno Fernandes">
         <p>Best phone I’ve ever had! The camera quality is top-notch, and the battery lasts all day even with heavy use.</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Bruno Fernandes</h3>
      </div>

      <div class="swiper-slide slide">
         <img src="images/pic-2.png" alt="Scott Wilson">
         <p>Great TV with excellent picture quality.
             The sound is decent, but I’d recommend getting a soundbar for the best experience</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Scott Wilson </h3>
      </div>

      <div class="swiper-slide slide">
         <img src="images/pic-3.png" alt="Jadon Sancho">
         <p>"This laptop handles everything I throw at it, from gaming to video editing.
             It’s lightweight and the battery life is impressive.</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Jadon Sancho</h3>
      </div>

      <div class="swiper-slide slide">
         <img src="images/pic-4.png" alt="JK Rowling">
         <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Quia tempore distinctio hic, iusto adipisci a rerum nemo perspiciatis fugiat sapiente.</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>JK Rowling</h3>
      </div>

      <div class="swiper-slide slide">
         <img src="images/pic-5.png" alt="Mark Hamilton">
         <p>These headphones have great audio clarity and are easy to pair with my devices.
             Slightly bulky, but worth it for the sound quality</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Mark Hamilton</h3>
      </div>

      <div class="swiper-slide slide">
         <img src="images/pic-6.png" alt="Mary Greenwood">
         <p>We believe in transparency and trust. 
            Our product descriptions are detailed and accurate, and our customer reviews speak for themselves.
            We are dedicated to building a community of satisfied customers who can shop with confidence, knowing they are getting the best value for their money.</p>
         <div class="stars">
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star"></i>
            <i class="fas fa-star-half-alt"></i>
         </div>
         <h3>Mary Greenwood</h3>
      </div>

   </div>

   <div class="swiper-pagination"></div>

   </div>

</section>









<?php include 'components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>

<script src="js/script.js"></script>

<script>

var swiper = new Swiper(".reviews-slider", {
   loop:true,
   spaceBetween: 20,
   pagination: {
      el: ".swiper-pagination",
      clickable:true,
   },
   breakpoints: {
      0: {
        slidesPerView:1,
      },
      768: {
        slidesPerView: 2,
      },
      991: {
        slidesPerView: 3,
      },
   },
});

</script>

</body>
</html>