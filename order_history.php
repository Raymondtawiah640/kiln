<?php
session_start();
require 'db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo '<p>You need to log in to view your order history.</p>';
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user orders
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Auto-mark undelivered items as delivered if older than 3 days
function autoMarkDelivered($orders, $pdo) {
    $now = time();
    foreach ($orders as $order) {
        $order_id = $order['order_id'] ?? null;
        $created_at = strtotime($order['created_at'] ?? '');
        if ($order_id && $created_at && ($now - $created_at) > (3 * 24 * 60 * 60)) {
            $updateSql = "UPDATE order_items SET status = 'delivered' 
                          WHERE order_id = ? AND (status IS NULL OR status != 'delivered')";
            $stmtUpdate = $pdo->prepare($updateSql);
            $stmtUpdate->execute([$order_id]);
        }
    }
}
autoMarkDelivered($orders, $pdo);

// Escaping utility
function safeHtml($val) {
    return htmlspecialchars($val ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Order History</title>
    <link rel="shortcut icon" href="logo.ico" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f3f8;
            color: #333;
            margin: 0;
            padding: 0;
        }
        h2 {
            text-align: center;
            margin: 20px 0;
            color: #4b0082;
        }
        .order-container {
            width: 90%;
            max-width: 1000px;
            margin: 20px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .order-header {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 15px;
            color: #444;
        }
        .product-row {
            display: flex;
            border-top: 1px solid #eee;
            padding: 15px 0;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 20px;
            border-radius: 6px;
            border: 1px solid #ccc;
            background: #f0f0f0;
        }
        .product-details {
            flex: 1;
        }
        .product-name {
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 5px;
        }
        .product-price, .product-quantity, .product-vendor {
            font-size: 14px;
            color: #555;
            margin-bottom: 4px;
        }
        .status-badge {
            margin-top: 8px;
            font-size: 13px;
            font-weight: bold;
            color: #fff;
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
        }
        .delivered {
            background-color: #28a745;
        }
        .pending {
            background-color: #dc3545;
        }
        .btn {
            background-color: #6a0dad;
            color: white;
            padding: 6px 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 8px;
        }
        .btn:hover {
            background-color: #5a0ca0;
        }
        .no-orders {
            text-align: center;
            margin: 40px 0;
            color: #888;
        }
        .info-message {
            text-align: center;
            color: green;
            margin: 10px 0 20px;
        }
    </style>
</head>
<body>
<?php require 'navbar.php'; ?>

<h2>Your Order History</h2>

<?php if (isset($_GET['status'], $_GET['message']) && $_GET['status'] === 'success' && !empty($_GET['message'])): ?>
    <p class="info-message"><?= safeHtml($_GET['message']) ?></p>
<?php endif; ?>

<?php if (empty($orders)): ?>
    <p class="no-orders">No orders found.</p>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <?php if (empty($order['order_id'])) continue; ?>

        <div class="order-container">
            <div class="order-header">
                Order #<?= safeHtml($order['order_id']) ?> &nbsp;|&nbsp;
                Date: <?= isset($order['created_at']) ? date('M j, Y H:i', strtotime($order['created_at'])) : 'N/A' ?> &nbsp;|&nbsp;
                Total: GH₵ <?= number_format((float)($order['total'] ?? 0), 2) ?>
            </div>

            <?php
            $stmt_items = $pdo->prepare("
                SELECT oi.*, p.name AS product_name, p.image AS image_url, 
                       p.price AS product_price, p.vendor_username AS vendor
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            $stmt_items->execute([$order['order_id']]);
            $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <?php if (empty($items)): ?>
                <p style="color:#999;">No items found for this order.</p>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <div class="product-row">
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="<?= safeHtml($item['image_url']) ?>" alt="<?= safeHtml($item['product_name']) ?>" class="product-image" />
                        <?php else: ?>
                            <div class="product-image"></div>
                        <?php endif; ?>

                        <div class="product-details">
                            <div class="product-name"><?= safeHtml($item['product_name'] ?? 'Unknown Product') ?></div>
                            <div class="product-price">Price: GH₵ <?= number_format((float)($item['product_price'] ?? 0), 2) ?></div>
                            <div class="product-quantity">Quantity: <?= (int)($item['quantity'] ?? 1) ?></div>
                            <div class="product-vendor">Vendor: <?= safeHtml($item['vendor'] ?? 'Unknown') ?></div>

                            <?php if (($item['status'] ?? '') === 'delivered'): ?>
                                <span class="status-badge delivered">Delivered</span>
                            <?php else: ?>
                                <span class="status-badge pending">Not Delivered</span>
                                <form action="mark_as_delivered.php" method="POST">
                                    <input type="hidden" name="order_item_id" value="<?= (int)($item['id'] ?? 0) ?>">
                                    <button type="submit" class="btn">Mark as Delivered</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
