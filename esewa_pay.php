<?php

include 'components/connect.php';
require_once 'components/esewa.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('location:user_login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    header('location:checkout.php');
    exit;
}

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM `orders` WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header('location:checkout.php');
    exit;
}

$config = get_esewa_config();

$amount = (string)$order['total_price'];
// Ensure unique transaction UUID per payment attempt
$transaction_uuid = "GIZMO-{$order['id']}-" . time();
$_SESSION['esewa_tx_uuid_' . $order['id']] = $transaction_uuid;

// Generate HMAC-SHA256 signature for eSewa ePay 2.0
$signature = generate_esewa_signature($amount, $transaction_uuid, $config['product_code'], $config['secret_key']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connecting to eSewa - Gizmo Store</title>
    <!-- favicon -->
    <link rel="icon" type="image/svg+xml" href="images/favicon.svg">
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .esewa-bridge-container {
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.5rem;
            background: #f8fafc;
        }
        .esewa-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 2rem;
            padding: 3.5rem 3rem;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }
        .esewa-logos-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.6rem;
            margin-bottom: 2.4rem;
        }
        .bridge-logo-img {
            height: 4.2rem;
            object-fit: contain;
        }
        .bridge-pulse-dots {
            color: #60bb46;
            font-size: 1.8rem;
            display: inline-flex;
            gap: 0.4rem;
        }
        .esewa-title {
            font-size: 2.4rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.8rem;
        }
        .esewa-subtitle {
            font-size: 1.45rem;
            color: #64748b;
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        .esewa-order-pill {
            background: #f1f5f9;
            border-radius: 1.2rem;
            padding: 1.4rem 1.8rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 1.5rem;
            color: #334155;
            margin-bottom: 2.2rem;
        }
        .esewa-order-pill strong {
            color: #0f172a;
            font-size: 1.8rem;
        }
        .sandbox-hint-box {
            background: #f0fdf4;
            border: 1.5px dashed #86efac;
            border-radius: 1.4rem;
            padding: 1.6rem 1.8rem;
            text-align: left;
            margin-bottom: 2.4rem;
            font-size: 1.35rem;
            color: #166534;
        }
        .sandbox-hint-box h4 {
            font-size: 1.45rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            color: #15803d;
        }
        .sandbox-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.8rem 1.2rem;
            margin-top: 0.6rem;
        }
        .sandbox-item strong {
            font-family: ui-monospace, monospace;
            background: #dcfce7;
            padding: 0.2rem 0.5rem;
            border-radius: 0.4rem;
            color: #14532d;
        }
        .esewa-submit-btn {
            background: #60bb46;
            color: #ffffff;
            border: none;
            border-radius: 1.2rem;
            padding: 1.4rem 2.4rem;
            font-size: 1.6rem;
            font-weight: 700;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(96, 187, 70, 0.35);
        }
        .esewa-submit-btn:hover {
            background: #52a33b;
            transform: translateY(-1px);
        }
        .spinner {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #ffffff;
            animation: spin 0.8s linear infinite;
            margin-right: 0.8rem;
            vertical-align: middle;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<?php include 'components/user_header.php'; ?>

<main class="esewa-bridge-container">
    <div class="esewa-card">
        <div class="esewa-logos-row">
            <img src="images/gizmo-logo.svg" alt="Gizmo Logo" class="bridge-logo-img">
            <i class="fas fa-arrow-right" style="color: #cbd5e1; font-size: 1.8rem;"></i>
            <img src="https://esewa.com.np/common/images/esewa_logo.png" alt="eSewa Logo" class="bridge-logo-img" style="height: 3.6rem;">
        </div>

        <h2 class="esewa-title">Redirecting to eSewa</h2>
        <p class="esewa-subtitle">
            Connecting to official eSewa Sandbox Gateway to complete your order safely.
        </p>

        <div class="esewa-order-pill">
            <span>Order Total:</span>
            <strong>Rs. <?= number_format($order['total_price'], 2); ?></strong>
        </div>

        <?php if ($config['is_sandbox']): ?>
        <div class="sandbox-hint-box">
            <h4><i class="fas fa-flask"></i> eSewa Sandbox Credentials</h4>
            <div class="sandbox-grid">
                <div class="sandbox-item">ID: <strong>9806800001</strong></div>
                <div class="sandbox-item">Pass: <strong>Nepal@123</strong></div>
                <div class="sandbox-item">OTP: <strong>123456</strong></div>
                <div class="sandbox-item">MPIN: <strong>1122</strong></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Official eSewa ePay 2.0 Form -->
        <form id="esewa-auto-form" action="<?= htmlspecialchars($config['endpoint'], ENT_QUOTES, 'UTF-8'); ?>" method="POST">
            <input type="hidden" name="amount" value="<?= htmlspecialchars($amount, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="tax_amount" value="0">
            <input type="hidden" name="total_amount" value="<?= htmlspecialchars($amount, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="transaction_uuid" value="<?= htmlspecialchars($transaction_uuid, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="product_code" value="<?= htmlspecialchars($config['product_code'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="product_service_charge" value="0">
            <input type="hidden" name="product_delivery_charge" value="0">
            <input type="hidden" name="success_url" value="<?= htmlspecialchars($config['success_url'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="failure_url" value="<?= htmlspecialchars($config['failure_url'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
            <input type="hidden" name="signature" value="<?= htmlspecialchars($signature, ENT_QUOTES, 'UTF-8'); ?>">

            <button type="submit" class="esewa-submit-btn">
                <span class="spinner"></span> Proceed to eSewa Pay
            </button>
        </form>
    </div>
</main>

<?php include 'components/footer.php'; ?>

<script>
    // Automatically submit form to eSewa after a brief pause so user sees credentials
    setTimeout(function() {
        document.getElementById('esewa-auto-form').submit();
    }, 1200);
</script>

</body>
</html>

