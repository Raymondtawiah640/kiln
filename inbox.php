<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

$user_id = $_SESSION['user_id'];
$error_message = null;

try {
    // Fetch messages with optional linked product info
    $stmt = $pdo->prepare("
        SELECT m.*, p.name AS product_name, p.image AS product_image
        FROM messages m
        LEFT JOIN products p ON m.product_id = p.id
        WHERE m.user_id = :user_id
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = 'Error fetching messages: ' . htmlspecialchars($e->getMessage());
}

// Check for outdated messages
$ten_days_ago = (new DateTime())->modify('-10 days');

// Mark message as read and redirect to checkout step 3
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    $message_id = $_GET['mark_read'];
    try {
        $stmtCheck = $pdo->prepare("SELECT status FROM messages WHERE id = :id AND user_id = :user_id");
        $stmtCheck->execute([':id' => $message_id, ':user_id' => $user_id]);
        $status = $stmtCheck->fetchColumn();

        if ($status === 'unread') {
            $stmt = $pdo->prepare("UPDATE messages SET status = 'read' WHERE id = :id AND user_id = :user_id");
            $stmt->execute([':id' => $message_id, ':user_id' => $user_id]);
        }
        header("Location: checkout.php?step=3");
        exit();
    } catch (PDOException $e) {
        $error_message = 'Error marking message as read: ' . htmlspecialchars($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Inbox - User Messages</title>
    <link rel="stylesheet" href="styles/inbox.css" />
    <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon">
</head>
<body>
    <?php require 'navbar.php'; ?>

    <header>
        <div class="container">
            <h1>Your Inbox (<?= count($messages) ?>)</h1>
            <p>View your messages and notifications.</p>
        </div>
    </header>

    <div class="container">
        <?php if ($error_message): ?>
            <div class="highlight-box">
                <p><?= $error_message ?></p>
            </div>
        <?php endif; ?>

        <?php if (empty($messages)): ?>
            <p>No messages found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="messages-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $message):
                            $status_class = ($message['status'] === 'read') ? 'status-read' : 'status-unread';
                            $order_date = new DateTime($message['created_at']);
                            $is_order_invalid = ($order_date < $ten_days_ago);
                        ?>
                        <tr>
                            <td>
                                <?php if ($message['product_name']): ?>
                                    <img src="<?= htmlspecialchars($message['product_image']) ?>" alt="<?= htmlspecialchars($message['product_name']) ?>" style="width:40px; height:40px; object-fit:cover; border-radius:4px; margin-right:8px; vertical-align:middle;">
                                    <?= htmlspecialchars($message['product_name']) ?>
                                <?php else: ?>
                                    <em>No product linked</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($message['subject']) ?>
                                <?php if ($is_order_invalid): ?>
                                    <span class="invalid-order" title="Order is older than 10 days">[Order ID is invalid]</span>
                                <?php endif; ?>
                            </td>
                            <td class="<?= $status_class ?>"><?= ucfirst(htmlspecialchars($message['status'])) ?></td>
                            <td><?= date('M j, Y', strtotime($message['created_at'])) ?></td>
                            <td>
                                <?php if ($message['status'] === 'unread'): ?>
                                    <a href="inbox.php?mark_read=<?= urlencode($message['id']) ?>" class="btn">Mark as Read</a>
                                <?php else: ?>
                                    <span style="color:#888;">Already Read</span>
                                <?php endif; ?>

                                <?php if (!empty($message['product_id'])): ?>
                                    <a href="confirmed_orders.php?product_id=<?= urlencode($message['product_id']) ?>&message_id=<?= urlencode($message['id']) ?>" class="btn btn-secondary" style="margin-left: 8px;" target="_blank">View Order</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php require 'footer.php'; ?>
</body>
</html>
