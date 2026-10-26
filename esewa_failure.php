<?php

include 'components/connect.php';

session_start();

$user_id = $_SESSION['user_id'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled - Gizmo Store</title>
    <!-- favicon -->
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .result-container {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1.5rem;
            background: #f8fafc;
        }
        .result-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 2.2rem;
            padding: 3.5rem 3rem;
            max-width: 540px;
            width: 100%;
            text-align: center;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }
        .result-icon {
            width: 7.2rem;
            height: 7.2rem;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 3.4rem;
            margin-bottom: 2rem;
            background: #fef2f2;
            color: #ef4444;
        }
        .result-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.8rem;
        }
        .result-desc {
            font-size: 1.5rem;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 2.4rem;
        }
        .btn-action {
            display: inline-block;
            background: #60bb46;
            color: #ffffff;
            padding: 1.3rem 2.4rem;
            border-radius: 1.2rem;
            font-size: 1.55rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            margin: 0.4rem;
        }
        .btn-action:hover {
            background: #52a33b;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #334155;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
    </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<main class="result-container">
    <div class="result-card">
        <div class="result-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h1 class="result-title">Payment Cancelled</h1>
        <p class="result-desc">
            Your eSewa payment was not completed or was cancelled. If you intended to pay with eSewa, you can try again below, or check your pending orders.
        </p>

        <div>
            <a href="checkout.php" class="btn-action">
                <i class="fas fa-redo" style="margin-right: 6px;"></i> Return to Checkout
            </a>
            <a href="orders.php" class="btn-action btn-secondary">
                View My Orders
            </a>
        </div>
    </div>
</main>

<?php include 'components/footer.php'; ?>

</body>
</html>

