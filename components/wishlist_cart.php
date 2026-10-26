<?php

// This component is always included from pages that have already called
// session_start() (home.php, shop.php, category.php, quick_view.php, wishlist.php).
csrf_verify_or_die();

if(isset($_POST['add_to_wishlist'])){

   if($user_id == ''){
      header('location:user_login.php');
   }else{

      $pid = $_POST['pid'];
      $pid = trim($pid);

      // Never trust price from the client - it's a hidden form field anyone
      // can edit before submitting. Always re-fetch the real price server-side.
      $select_product = $conn->prepare("SELECT name, price, discount, image_01 FROM `products` WHERE id = ?");
      $select_product->execute([$pid]);
      $product_row = $select_product->fetch(PDO::FETCH_ASSOC);

      if(!$product_row){
         $message[] = 'sorry, this product no longer exists!';
      }else{

      $name = $product_row['name'];
      $price = $product_row['discount'] > 0
         ? round($product_row['price'] - ($product_row['price'] * $product_row['discount'] / 100))
         : $product_row['price'];
      $image = $product_row['image_01'];

      $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE pid = ? AND user_id = ?");
      $check_wishlist_numbers->execute([$pid, $user_id]);

      $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE pid = ? AND user_id = ?");
      $check_cart_numbers->execute([$pid, $user_id]);

      if($check_wishlist_numbers->rowCount() > 0){
         $message[] = 'already added to wishlist!';
      }elseif($check_cart_numbers->rowCount() > 0){
         $message[] = 'already added to cart!';
      }else{
         $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, pid, name, price, image) VALUES(?,?,?,?,?)");
         $insert_wishlist->execute([$user_id, $pid, $name, $price, $image]);
         $message[] = 'added to wishlist!';
      }

      }

   }

}

if(isset($_POST['add_to_cart'])){

   if($user_id == ''){
      header('location:user_login.php');
   }else{

      $pid = $_POST['pid'];
      $pid = trim($pid);
      $qty = (int) $_POST['qty'];
      if($qty < 1) $qty = 1;

      $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE pid = ? AND user_id = ?");
      $check_cart_numbers->execute([$pid, $user_id]);

      if($check_cart_numbers->rowCount() > 0){
         $message[] = 'already added to cart!';
      }else{

         // Never trust price/name/image from the client - re-fetch the real,
         // current values (and stock) from the products table server-side.
         $select_product = $conn->prepare("SELECT name, price, discount, stock, image_01 FROM `products` WHERE id = ?");
         $select_product->execute([$pid]);
         $product_row = $select_product->fetch(PDO::FETCH_ASSOC);

         if(!$product_row || $product_row['stock'] <= 0){
            $message[] = 'sorry, this product is out of stock!';
         }elseif($qty > $product_row['stock']){
            $message[] = 'sorry, only '.$product_row['stock'].' left in stock!';
         }else{

         $name = $product_row['name'];
         $price = $product_row['discount'] > 0
            ? round($product_row['price'] - ($product_row['price'] * $product_row['discount'] / 100))
            : $product_row['price'];
         $image = $product_row['image_01'];

         $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE pid = ? AND user_id = ?");
         $check_wishlist_numbers->execute([$pid, $user_id]);

         if($check_wishlist_numbers->rowCount() > 0){
            $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ? AND user_id = ?");
            $delete_wishlist->execute([$pid, $user_id]);
         }

         $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, pid, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
         $insert_cart->execute([$user_id, $pid, $name, $price, $qty, $image]);
         $message[] = 'added to cart!';

         }

      }

   }

}

?>