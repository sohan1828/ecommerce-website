<?php

include 'components/connect.php';
require_once 'components/esewa.php';
require_once 'components/mailer.php';

session_start();

$user_id = $_SESSION['user_id'] ?? 0;
$encodedData = $_GET['data'] ?? '';

$success = false;
$error_message = '';
$order = null;
$transaction_code = '';

if (!empty($encodedData)) {
    $decodedJson = base64_decode($encodedData);
    $payload = json_decode($decodedJson, true);

    if (is_array($payload) && isset($payload['status']) && $payload['status'] === 'COMPLETE') {
        $transaction_code = $payload['transaction_code'] ?? '';
        $transaction_uuid = $payload['transaction_uuid'] ?? '';

        // Extract order ID from transaction UUID (e.g. GIZMO-15-171829102)
        $parts = explode('-', $transaction_uuid);
        $order_id = isset($parts[1]) ? (int)$parts[1] : 0;

        if ($order_id > 0) {
            $stmt = $conn->prepare("SELECT * FROM `orders` WHERE id = ? LIMIT 1");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($order) {
                // Update payment status in database
                $update = $conn->prepare("UPDATE `orders` SET payment_status = 'completed', transaction_code = ? WHERE id = ?");
                $update->execute([$transaction_code, $order_id]);

                // Clear the user's cart
                $delCart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
                $delCart->execute([$order['user_id']]);

                $success = true;

                // Send email confirmation using .env SMTP configuration
                $customerEmail = $order['email'];
                $subject = "Order #{$order['id']} Confirmed — Payment via eSewa Successful";
                $htmlBody = "
                <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; max-width: 580px; margin: 0 auto; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
                    <div style='background: linear-gradient(135deg, #166534 0%, #15803d 100%); padding: 28px 24px; text-align: center;'>
                        <div style='display: inline-block; background: #22c55e; color: #ffffff; font-weight: 700; font-size: 14px; padding: 6px 14px; border-radius: 9999px; text-transform: uppercase;'>
                            ✓ eSewa Payment Verified
                        </div>
                        <h1 style='color: #ffffff; margin: 16px 0 0; font-size: 22px;'>Order Confirmation</h1>
                    </div>
                    <div style='padding: 28px 24px; color: #334155;'>
                        <p style='margin-top: 0; font-size: 15px;'>Hello <strong>" . htmlspecialchars($order['name'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
                        <p style='font-size: 14px; color: #475569;'>Thank you for your order! Your payment has been confirmed via <strong>eSewa</strong>.</p>
                        
                        <div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin: 20px 0;'>
                            <div style='display: flex; justify-content: space-between; margin-bottom: 8px;'>
                                <span style='color: #64748b; font-size: 13px;'>Order ID:</span>
                                <strong style='color: #0f172a;'>#{$order['id']}</strong>
                            </div>
                            <div style='display: flex; justify-content: space-between; margin-bottom: 8px;'>
                                <span style='color: #64748b; font-size: 13px;'>eSewa Transaction Code:</span>
                                <strong style='color: #16a34a; font-family: monospace;'>{$transaction_code}</strong>
                            </div>
                            <div style='display: flex; justify-content: space-between; margin-bottom: 8px;'>
                                <span style='color: #64748b; font-size: 13px;'>Payment Method:</span>
                                <strong>eSewa Digital Wallet</strong>
                            </div>
                            <div style='display: flex; justify-content: space-between; border-top: 1px dashed #cbd5e1; padding-top: 8px; margin-top: 8px;'>
                                <span style='color: #0f172a; font-weight: 600;'>Total Amount Paid:</span>
                                <strong style='color: #7c3aed; font-size: 16px;'>Rs. " . number_format($order['total_price'], 2) . "</strong>
                            </div>
                        </div>

                        <div style='font-size: 13px; color: #64748b;'>
                            <strong>Delivery Address:</strong> " . htmlspecialchars($order['address'], ENT_QUOTES, 'UTF-8') . "
                        </div>
                    </div>
                    <div style='background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px 24px; font-size: 12px; color: #94a3b8; text-align: center;'>
                        &copy; " . date('Y') . " Gizmo Store. All rights reserved.
                    </div>
                </div>";
                send_mail($customerEmail, $subject, $htmlBody);

                // Notify admin
                $adminEmail = env('ADMIN_EMAIL', 'sohandahal77@gmail.com');
                send_mail($adminEmail, "New eSewa Order #{$order['id']} from {$order['name']}", $htmlBody);
            } else {
                $error_message = "Order record not found.";
            }
        } else {
            $error_message = "Invalid order reference returned by eSewa.";
        }
    } else {
        $error_message = "Payment status was not completed.";
    }
} else {
    $error_message = "No transaction data received from eSewa.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Result - Gizmo Store</title>
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
            max-width: 560px;
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
        }
        .result-icon.success {
            background: #dcfce7;
            color: #16a34a;
        }
        .result-icon.failed {
            background: #fee2e2;
            color: #dc2626;
        }
        .result-title {
            font-size: 2.6rem;
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
        .receipt-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 1.4rem;
            padding: 1.8rem;
            text-align: left;
            margin-bottom: 2.4rem;
            font-size: 1.4rem;
        }
        .receipt-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.7rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .receipt-row:last-child {
            border-bottom: none;
            padding-top: 1rem;
            font-weight: 700;
            font-size: 1.6rem;
            color: #7c3aed;
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
        <?php if ($success && $order): ?>
            <div class="result-icon success">
                <i class="fas fa-check"></i>
            </div>
            <h1 class="result-title">Payment Successful!</h1>
            <p class="result-desc">
                Your payment was verified by eSewa and your order is now confirmed. We have dispatched a confirmation email to <strong><?= htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8'); ?></strong>.
            </p>

            <div class="receipt-box">
                <div class="receipt-row">
                    <span>Order Reference:</span>
                    <strong>#<?= $order['id']; ?></strong>
                </div>
                <div class="receipt-row">
                    <span>eSewa Code:</span>
                    <strong style="color: #16a34a; font-family: monospace;"><?= htmlspecialchars($transaction_code, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <div class="receipt-row">
                    <span>Payment Method:</span>
                    <span>eSewa Digital Wallet</span>
                </div>
                <div class="receipt-row">
                    <span>Payment Status:</span>
                    <strong style="color: #16a34a;">COMPLETED</strong>
                </div>
                <div class="receipt-row">
                    <span>Total Amount Paid:</span>
                    <span>Rs. <?= number_format($order['total_price'], 2); ?></span>
                </div>
            </div>

            <div>
                <a href="orders.php" class="btn-action">
                    <i class="fas fa-box" style="margin-right: 6px;"></i> View My Orders
                </a>
                <a href="shop.php" class="btn-action btn-secondary">
                    Continue Shopping
                </a>
            </div>

        <?php else: ?>
            <div class="result-icon failed">
                <i class="fas fa-times"></i>
            </div>
            <h1 class="result-title">Payment Verification Failed</h1>
            <p class="result-desc">
                <?= htmlspecialchars($error_message ?: 'We could not verify your eSewa transaction.', ENT_QUOTES, 'UTF-8'); ?>
            </p>

            <div>
                <a href="checkout.php" class="btn-action" style="background: #ef4444;">
                    <i class="fas fa-redo" style="margin-right: 6px;"></i> Retry Checkout
                </a>
                <a href="orders.php" class="btn-action btn-secondary">
                    View Orders
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'components/footer.php'; ?>

</body>
</html>

