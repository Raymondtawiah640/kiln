<?php
session_start();
require 'db_connect.php';
require 'vendor/autoload.php'; // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_item_id'])) {
    $order_item_id = filter_input(INPUT_POST, 'order_item_id', FILTER_VALIDATE_INT);

    if (!$order_item_id) {
        header("Location: order_history.php?status=error&message=" . urlencode("Invalid order item ID"));
        exit;
    }

    // Fetch item details including vendor email from the users table
    $sql = "
        SELECT 
            oi.*, 
            o.user_id, 
            o.order_id, 
            p.name AS product_name, 
            p.vendor_username, 
            u.email AS vendor_email
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        JOIN products p ON oi.product_id = p.id
        JOIN users u ON p.vendor_username = u.username
        WHERE oi.id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$order_item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        header("Location: order_history.php?status=error&message=" . urlencode("Item not found"));
        exit;
    }

    // Update status to delivered
    $update_sql = "UPDATE order_items SET status = 'delivered' WHERE id = ?";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->execute([$order_item_id]);

    // Send email to vendor if email exists
    if (!empty($item['vendor_email'])) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'enterprisekiln@gmail.com';
            $mail->Password = getenv('GMAIL_APP_PASSWORD'); // Secure your password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('enterprisekiln@gmail.com', 'Kiln Enterprise');
            $mail->addAddress($item['vendor_email'], $item['vendor_username']);

            $mail->isHTML(true);
            $mail->Subject = "Product Delivered Confirmation";
            $mail->Body = "
                <p>Hello <strong>" . htmlspecialchars($item['vendor_username']) . "</strong>,</p>
                <p>The product <strong>" . htmlspecialchars($item['product_name']) . "</strong> from Order <strong>#" . htmlspecialchars($item['order_id']) . "</strong> has been marked as <strong>delivered</strong> by the customer.</p>
                <p>Thank you for using our platform.</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Email sending failed for order item {$order_item_id}: " . $mail->ErrorInfo);
            // Optionally notify user/admin or retry
        }
    }

    header("Location: order_history.php?status=success&message=" . urlencode("Product marked as delivered"));
    exit;
} else {
    header("Location: order_history.php?status=error&message=" . urlencode("Invalid request"));
    exit;
}
