<?php
session_start();
require 'db_connect.php'; // Database connection

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_messages = [];
$error_messages = [];

// Fetch user info
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit();
}

// Fetch orders
$stmt_orders = $pdo->prepare("SELECT order_id, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt_orders->execute([$user_id]);
$orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);

// Check for 10-day validity of orders
$current_date = new DateTime();
$ten_days_ago = $current_date->modify('-10 days');

// Handle form POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($current_password || $new_password || $confirm_password) {
        if (!$current_password || !$new_password || !$confirm_password) {
            $error_messages[] = "All password fields are required.";
        } elseif ($new_password !== $confirm_password) {
            $error_messages[] = "New password and confirm password do not match.";
        } else {
            // Verify old password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $stored = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($current_password, $stored['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
                $success_messages[] = "Password updated successfully.";
            } else {
                $error_messages[] = "Current password is incorrect.";
            }
        }

        // Refresh user data
        $stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: landing_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="shortcut icon" href="logo1.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/profile.css">
    <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container">
    <h2><i class="fas fa-user-circle"></i> Your Profile</h2>

    <?php foreach ($success_messages as $msg): ?>
        <div class="message message-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
        </div>
    <?php endforeach; ?>

    <?php foreach ($error_messages as $msg): ?>
        <div class="message message-error">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($msg) ?>
        </div>
    <?php endforeach; ?>

    <div class="profile-info">
        <div class="info-row">
            <div class="info-label"><i class="fas fa-user"></i> Username:</div>
            <div class="info-value"><?= htmlspecialchars($user['username']) ?></div>
        </div>

        <div class="info-row">
            <div class="info-label"><i class="fas fa-envelope"></i> Email:</div>
            <div class="info-value"><?= htmlspecialchars($user['email']) ?></div>
        </div>
    </div>

    <div class="profile-info">
        <h3><i class="fas fa-box"></i> Your Orders</h3>
        <?php if ($orders): ?>
            <ul>
                <?php foreach ($orders as $order): 
                    $order_date = new DateTime($order['created_at']);
                    $is_order_invalid = ($order_date < $ten_days_ago);
                ?>
                    <li>
                        <strong>Order #<?= htmlspecialchars($order['order_id']) ?></strong> –
                        <?= htmlspecialchars(date("F j, Y", strtotime($order['created_at']))) ?>
                        <?php if ($is_order_invalid): ?>
                            <span class="invalid-order">[Order ID is invalid]</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>You haven’t placed any orders yet.</p>
        <?php endif; ?>
    </div>

    <form method="POST" action="">
        <h3><i class="fas fa-lock"></i> Change Password</h3>

        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
        </div>

        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
        </div>

        <button type="submit" class="btn">
            <i class="fas fa-save"></i> Save Changes
        </button>
    </form>

    <div class="action-buttons">
        <a href="landing_dashboard.php" class="btn btn-secondary">
            <i class="fas fa-tachometer-alt"></i> Go to Dashboard
        </a>
        <a href="?logout=true" class="btn btn-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
