<?php

include '../components/connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
    csrf_verify_or_die();
}

$admin_id = $_SESSION['admin_id'];

if (!isset($admin_id)) {
    header('location:admin_login.php');
}

if (isset($_POST['update'])) {

    $pid = $_POST['pid'];
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $details = trim($_POST['details']);
    $category = trim($_POST['category']);
    $stock = (int) $_POST['stock'];
    $discount = (int) $_POST['discount'];

    $update_product = $conn->prepare("UPDATE `products` SET name = ?, category = ?, details = ?, price = ?, stock = ?, discount = ? WHERE id = ?");
    $update_product->execute([$name, $category, $details, $price, $stock, $discount, $pid]);

    $messages = [];  // Initialize messages as an array

    $old_images = [
        'image_01' => $_POST['old_image_01'],
        'image_02' => $_POST['old_image_02'],
        'image_03' => $_POST['old_image_03']
    ];

    foreach (['image_01', 'image_02', 'image_03'] as $image_key) {
        $image = $_FILES[$image_key]['name'];
        $image = trim($image);
        $image_size = $_FILES[$image_key]['size'];
        $image_tmp_name = $_FILES[$image_key]['tmp_name'];
        $image_folder = '../uploaded_img/' . $image;

        if (!empty($image)) {
            if ($image_size > 2000000) {
                $messages[] = 'Image size is too large!';
            } else {
                $update_image = $conn->prepare("UPDATE `products` SET $image_key = ? WHERE id = ?");
                $update_image->execute([$image, $pid]);
                move_uploaded_file($image_tmp_name, $image_folder);
                if (file_exists('../uploaded_img/' . $old_images[$image_key])) {
                    unlink('../uploaded_img/' . $old_images[$image_key]);
                }
                $messages[] = "$image_key updated successfully!";
            }
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
    <title>Update Product</title>
   <!-- favicon -->
   <link rel="icon" type="image/svg+xml" href="../images/favicon.svg">
   <link rel="icon" type="image/png" href="../images/favicon.png">
   <link rel="shortcut icon" href="../favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="update-product">

    <h1 class="heading">Update Product</h1>

    <?php
    if (isset($_GET['update'])) {
        $update_id = $_GET['update'];
        $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
        $select_products->execute([$update_id]);
        if ($select_products->rowCount() > 0) {
            while ($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)) {
    ?>
    <form action="" method="post" enctype="multipart/form-data">
      <?= csrf_field(); ?>
        <input type="hidden" name="pid" value="<?= htmlspecialchars($fetch_products['id'], ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="old_image_01" value="<?= htmlspecialchars($fetch_products['image_01'], ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="old_image_02" value="<?= htmlspecialchars($fetch_products['image_02'], ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="old_image_03" value="<?= htmlspecialchars($fetch_products['image_03'], ENT_QUOTES, 'UTF-8'); ?>">
        <div class="image-container">
            <div class="main-image">
                <img src="../uploaded_img/<?= htmlspecialchars($fetch_products['image_01'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($fetch_products['name']); ?>">
            </div>
            <div class="sub-image">
                <img src="../uploaded_img/<?= htmlspecialchars($fetch_products['image_01'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($fetch_products['name']); ?> view 1">
                <img src="../uploaded_img/<?= htmlspecialchars($fetch_products['image_02'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($fetch_products['name']); ?> view 2">
                <img src="../uploaded_img/<?= htmlspecialchars($fetch_products['image_03'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($fetch_products['name']); ?> view 3">
            </div>
        </div>
        <span>Update Name</span>
        <input type="text" name="name" required class="box" maxlength="100" placeholder="Enter product name" value="<?= htmlspecialchars($fetch_products['name'], ENT_QUOTES, 'UTF-8'); ?>">
        <span>Update Price</span>
        <input type="number" name="price" required class="box" min="0" max="9999999999" placeholder="Enter product price" onkeypress="if(this.value.length == 10) return false;" value="<?= htmlspecialchars($fetch_products['price'], ENT_QUOTES, 'UTF-8'); ?>">
        <span>Update Details</span>
        <textarea name="details" class="box" required cols="30" rows="10"><?= htmlspecialchars($fetch_products['details'], ENT_QUOTES, 'UTF-8'); ?></textarea>
        <span>Update Category</span>
        <select name="category" class="box" required>
            <?php
            $categories = ['laptop' => 'Laptop', 'tv' => 'TV', 'camera' => 'Camera', 'mouse' => 'Mouse', 'fridge' => 'Refrigerator', 'washing' => 'Washing machine', 'smartphone' => 'Smartphone', 'watch' => 'Watch'];
            foreach($categories as $value => $label){
                $selected = ($fetch_products['category'] === $value) ? 'selected' : '';
                echo "<option value=\"$value\" $selected>$label</option>";
            }
            ?>
        </select>
        <span>Update Stock</span>
        <input type="number" name="stock" required class="box" min="0" max="99999" placeholder="Enter stock quantity" value="<?= (int) $fetch_products['stock']; ?>">
        <span>Update Discount %</span>
        <input type="number" name="discount" class="box" min="0" max="90" placeholder="0" value="<?= (int) $fetch_products['discount']; ?>">
        <span>Update Image 01</span>
        <input type="file" name="image_01" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
        <span>Update Image 02</span>
        <input type="file" name="image_02" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
        <span>Update Image 03</span>
        <input type="file" name="image_03" accept="image/jpg, image/jpeg, image/png, image/webp" class="box">
        <div class="flex-btn">
            <input type="submit" name="update" class="btn" value="Update">
            <a href="products.php" class="option-btn">Go Back</a>
        </div>
    </form>
    <?php
            }
        } else {
            echo '<p class="empty">No product found!</p>';
        }
    } else {
        echo '<p class="empty">No update ID provided!</p>';
    }
    ?>

</section>

<script src="../js/admin_script.js"></script>

</body>
</html>