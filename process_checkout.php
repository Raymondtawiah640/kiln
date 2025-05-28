<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=UTF-8');
session_start();

require 'db_connect.php';
require 'vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$payment_method = $_POST['payment_method'] ?? '';

$valid_payment_methods = ['momo', 'cash'];
if (!in_array($payment_method, $valid_payment_methods, true)) {
    header("Location: checkout.php?status=error&message=" . urlencode("Invalid payment method."));
    exit();
}

// Fetch cart items including vendor details
$stmt = $pdo->prepare("
    SELECT c.*, p.id AS product_id, p.name, p.price, p.stock, p.vendor_telephone, p.vendor_landmark
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cartItems)) {
    header("Location: cart.php");
    exit();
}

// Validate stock
foreach ($cartItems as $item) {
    if ($item['quantity'] > $item['stock']) {
        die("Not enough stock for product: " . htmlspecialchars($item['name']));
    }
}

function generateUniqueOrderId($pdo) {
    do {
        $order_id = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 10));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE order_id = ?");
        $stmt->execute([$order_id]);
    } while ($stmt->fetchColumn() > 0);
    return $order_id;
}

$order_id = generateUniqueOrderId($pdo);

// Totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$delivery_fee = 8.00;
$total = $subtotal + $delivery_fee;

// Fetch user info
$stmt_user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user || empty($user['email'])) {
    die("User information is incomplete or email missing.");
}

$userName = htmlspecialchars($user['username'] ?? 'Customer');
$to = $user['email'];

// Insert order
$stmt_order = $pdo->prepare("
    INSERT INTO orders (order_id, user_id, total, shipping_fee, payment_method, status, created_at)
    VALUES (?, ?, ?, ?, ?, 'pending', NOW())
");
$stmt_order->execute([$order_id, $user_id, $total, $delivery_fee, $payment_method]);

// Insert order items & update stock
$stmt_item = $pdo->prepare("
    INSERT INTO order_items (order_id, product_id, quantity, price)
    VALUES (?, ?, ?, ?)
");
$stmt_stock = $pdo->prepare("
    UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?
");

foreach ($cartItems as $item) {
    $stmt_item->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    $stmt_stock->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
}

// Clear cart
$pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);

// Email
$subject = "Order Confirmation - Order ID: $order_id";
$confirmationLink = "http://localhost/kilnmart/confirm_order.php?order_id=" . urlencode($order_id);
$currentDate = date('F j, Y');

// Email Body
$productDetailsHTML = '';
foreach ($cartItems as $item) {
    $productName = htmlspecialchars($item['name']);
    $quantity = $item['quantity'];
    $totalPrice = number_format($item['price'] * $quantity, 2);
    $vendorPhone = htmlspecialchars($item['vendor_telephone'] ?? 'N/A');
    $vendorLandmark = htmlspecialchars($item['vendor_landmark'] ?? 'N/A');

    $productDetailsHTML .= "
    <p>
        <strong>{$productName}</strong> (Qty: {$quantity}) - GH₵ {$totalPrice}<br>
        Vendor Phone: {$vendorPhone}<br>
        Vendor Landmark: {$vendorLandmark}
    </p>";
}

$emailMessage = <<<HTML
<html>
<head><title>Order Confirmation</title></head>
<body>
<p>Dear {$userName},</p>
<p>Thank you for your order <strong>#$order_id</strong>.</p>
<p>
Total: GH₵ {number_format($total, 2)}<br>
Delivery Fee: GH₵ {number_format($delivery_fee, 2)}<br>
Payment Method: {ucfirst(htmlspecialchars($payment_method))}<br>
Pickup Location: Koforidua Technical University<br>
Order Date: {$currentDate}
</p>
{$productDetailsHTML}
<p>To confirm your order, click <a href="{$confirmationLink}">here</a>.</p>
<p>Best regards,<br>Kiln Team</p>
</body>
</html>
HTML;

// Save inbox messages
$stmt_msg = $pdo->prepare("
    INSERT INTO messages (user_id, product_id, subject, message, status, created_at)
    VALUES (?, ?, ?, ?, 'unread', NOW())
");

foreach ($cartItems as $item) {
    $productName = htmlspecialchars($item['name']);
    $quantity = $item['quantity'];
    $totalPrice = number_format($item['price'] * $quantity, 2);
    $vendorPhone = htmlspecialchars($item['vendor_telephone'] ?? 'N/A');
    $vendorLandmark = htmlspecialchars($item['vendor_landmark'] ?? 'N/A');
    $product_id = $item['product_id'];

    $itemMessage = <<<HTML
<p><strong>{$productName}</strong> (Qty: {$quantity}) - GH₵ {$totalPrice}<br>
Vendor Phone: {$vendorPhone}<br>
Vendor Landmark: {$vendorLandmark}</p>
<p>Order ID: <strong>{$order_id}</strong></p>
<p><a href="{$confirmationLink}">Confirm Order</a></p>
HTML;

    $stmt_msg->execute([$user_id, $product_id, $subject, $itemMessage]);
}

// Send Email
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'enterprisekiln@gmail.com';
    $mail->Password = 'rszr pbcc juxu tenc'; // Use app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('enterprisekiln@gmail.com', 'Kiln Enterprise');
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $emailMessage;
    $mail->AddEmbeddedImage('logo1.png', 'logoimg');

    $mail->send();

    $pdo->prepare("UPDATE orders SET status = 'review' WHERE order_id = ?")->execute([$order_id]);
    $pdo->prepare("UPDATE messages SET status = 'read' WHERE user_id = ? AND subject = ?")
        ->execute([$user_id, $subject]);

    echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Order Placed</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 40px; background-color: #f7f7f7; }
        .box {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 40px;
            display: inline-block;
        }
        h2 { color: #8a2be2; }
        a { color: #8a2be2; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class='box'>
        <h2>Thank you for your order!</h2>
        <p>Your order ID is <strong>" . htmlspecialchars($order_id) . "</strong>.</p>
        <p>A confirmation email has been sent to <strong>" . htmlspecialchars($to) . "</strong>.</p>
        <p><strong>Payment Method:</strong> " . ucfirst(htmlspecialchars($payment_method)) . "</p>
        <p><a href='order_history.php'>View Your Orders</a></p>
    </div>
</body>
</html>";
    exit();

} catch (Exception $e) {
    error_log("PHPMailer Error: {$mail->ErrorInfo}");
    header("Location: inbox.php?status=email_failed&order_id=" . urlencode($order_id));
    exit();
}
?>
