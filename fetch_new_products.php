<?php
include 'components/connect.php'; // Include your DB connection

// Get the timestamp of the last time the frontend checked for products
$last_check = isset($_GET['last_check']) ? $_GET['last_check'] : 0;

// Query to fetch products added AFTER the last check
// Assuming you add a `created_at` timestamp column to your `products` table
$select_products = $conn->prepare("SELECT * FROM `products` WHERE created_at > FROM_UNIXTIME(?) ORDER BY id DESC");
$select_products->execute([$last_check]);

$new_products = [];
if($select_products->rowCount() > 0){
   while($fetch_product = $select_products->fetch(PDO::FETCH_ASSOC)){
      $new_products[] = $fetch_product;
   }
}

// Return the data as a JSON response
header('Content-Type: application/json');
echo json_encode([
    'timestamp' => time(), // Send back the current server time
    'products' => $new_products
]);
?>