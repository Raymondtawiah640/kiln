<?php
header('Content-Type: text/html; charset=UTF-8');
require 'db_connect.php';

$order_id = $_GET['order_id'] ?? '';
$confirmed = $_GET['confirm'] ?? '';
$success = false;
$message = '';

function isValidOrderId($id) {
    return preg_match('/^[0-9a-zA-Z_-]+$/', $id);
}

if ($order_id && isValidOrderId($order_id)) {
    try {
        $stmt_check = $pdo->prepare("SELECT status FROM orders WHERE order_id = ?");
        $stmt_check->execute([$order_id]);
        $order = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            if ($order['status'] === 'confirmed') {
                $message = "Your order has already been confirmed.";
                $success = true;
            } elseif ($confirmed === 'yes') {
                // Update order status
                $stmt = $pdo->prepare("UPDATE orders SET status = 'confirmed' WHERE order_id = ?");
                $stmt->execute([$order_id]);

                if ($stmt->rowCount() > 0) {
                    // Update all items in this order to "delivered"
                    $update_items = $pdo->prepare("UPDATE order_items SET status = 'delivered' WHERE order_id = ?");
                    $update_items->execute([$order_id]);

                    $message = "Your order has been successfully confirmed and marked as delivered. Thank you!";
                    $success = true;
                } else {
                    $message = "Order confirmation failed. Please try again.";
                }
            } else {
                $message = "Are you sure you want to confirm this order?";
            }
        } else {
            $message = "Invalid order ID.";
        }
    } catch (PDOException $e) {
        $message = "An error occurred while confirming your order.";
    }
} else {
    $message = "Invalid or missing order ID.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            text-align: center;
            padding: 50px;
        }
        .box {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            display: inline-block;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: <?= $success ? '#8a2be2' : '#c0392b' ?>;
        }
        p {
            font-size: 18px;
            color: #333;
        }
        a {
            color: #8a2be2;
            text-decoration: underline;
        }
        button {
            padding: 10px 20px;
            background: #8a2be2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2><?= htmlspecialchars($success ? 'Success' : 'Notice') ?></h2>
        <p><?= htmlspecialchars($message) ?></p>

        <?php if (!$success && isset($order['status']) && $order['status'] !== 'confirmed' && $confirmed !== 'yes'): ?>
            <form method="get" action="confirm_order.php">
                <input type="hidden" name="order_id" value="<?= htmlspecialchars($order_id) ?>">
                <input type="hidden" name="confirm" value="yes">
                <button type="submit">Yes, Confirm My Order</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
