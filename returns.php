<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

$user_id = $_SESSION['user_id'];
$feedback_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return-submit'])) {
    // Sanitize inputs
    $order_number = trim(htmlspecialchars($_POST['order-number']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $reason = htmlspecialchars($_POST['reason']);
    $details = htmlspecialchars($_POST['details']);

    // Email format validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $feedback_message = '<div class="highlight-box" style="background-color: #ffebee; border-left-color: #f44336;">
            <p style="color: black;">Error: The email format is invalid.</p>
        </div>';
    }
    // Order number validation
    elseif (empty($order_number) || !preg_match('/^[A-Za-z0-9]+$/', $order_number)) {
        $feedback_message = '<div class="highlight-box" style="background-color: #ffebee; border-left-color: #f44336;">
            <p style="color: black;">Error: Invalid order number format.</p>
        </div>';
    } else {
        try {
            // Fetch the user's registered email from the users table
            $stmt_check_user_email = $pdo->prepare("SELECT email FROM users WHERE id = :user_id");
            $stmt_check_user_email->execute([':user_id' => $user_id]);
            $user = $stmt_check_user_email->fetch(PDO::FETCH_ASSOC);

            // Check if the email entered matches the user's registered email
            if (!$user || $user['email'] !== $email) {
                $feedback_message = '<div class="highlight-box" style="background-color: #ffebee; border-left-color: #f44336;">
                    <p style="color: black;">Error: The email does not match the one registered with the account.</p>
                </div>';
            } else {
                // Verify order ownership
                $stmt_check_order = $pdo->prepare("SELECT * FROM orders WHERE user_id = :user_id AND order_id = :order_id");
                $stmt_check_order->execute([
                    ':user_id' => $user_id,
                    ':order_id' => $order_number
                ]);

                $order = $stmt_check_order->fetch(PDO::FETCH_ASSOC);

                if (!$order) {
                    $feedback_message = '<div class="highlight-box" style="background-color: #ffebee; border-left-color: #f44336;">
                        <p style="color: black;">Error: The order ID does not exist or does not belong to you.</p>
                    </div>';
                } else {
                    // Insert return request
                    $stmt = $pdo->prepare("INSERT INTO returns (user_id, order_id, email, reason, details, status) 
                        VALUES (:user_id, :order_id, :email, :reason, :details, 'pending')");
                    $stmt->execute([
                        ':user_id' => $user_id,
                        ':order_id' => $order_number,
                        ':email' => $email,
                        ':reason' => $reason,
                        ':details' => $details
                    ]);

                    $return_id = $pdo->lastInsertId();
                    $feedback_message = '<div class="highlight-box" style="background-color: #e8f5e9; border-left-color: #4caf50;">
                        <p style=" color: black;">Your return request #' . htmlspecialchars($return_id) . ' has been submitted successfully. We will email you the return instructions shortly.</p>
                    </div>';
                }
            }
        } catch (PDOException $e) {
            // Generalize the error message for better user experience
            $feedback_message = '<div class="highlight-box" style="background-color: #ffebee; border-left-color: #f44336;">
                <p>Error: Something went wrong while submitting your return request. Please try again later.</p>
            </div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Returns & Refunds - Our Company</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/returns.css">
    <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon">
</head>
<body>
    <?php require 'navbar.php'; ?>

    <header>
        <div class="container">
            <h1>Returns & Refunds</h1>
            <p>Our hassle-free return policy</p>
        </div>
    </header>

    <div class="container">
        <section>
            <p>We want you to be completely satisfied with your purchase. If you're not happy with your order, we'll gladly accept returns within 30 days of delivery.</p>
            <div class="highlight-box">
                <p><strong>Important:</strong> To be eligible for a return, your item must be unused, in the same condition you received it, and in its original packaging.</p>
            </div>
        </section>

        <section class="policy-section">
            <h2>Return Process</h2>
            <p>Follow these simple steps to return an item:</p>
            <div class="process-steps">
                <div class="step"><div class="step-number">1</div><h3>Initiate Return</h3><p>Click the button below or visit your order history.</p></div>
                <div class="step"><div class="step-number">2</div><h3>Package Item</h3><p>Securely pack the item with all original packaging and accessories.</p></div>
                <div class="step"><div class="step-number">3</div><h3>Ship Back</h3><p>Use the prepaid return label or ship to our return center.</p></div>
                <div class="step"><div class="step-number">4</div><h3>Receive Refund</h3><p>We'll process your refund once we receive and inspect the item.</p></div>
            </div>
            <div style="text-align: center;">
                <a href="order_history.php" class="btn btn-outline">View Order History</a>
                <a href="#start-return" class="btn">Start a Return</a>
                <a href="contact_us.php" class="btn btn-outline">Contact Support</a>
            </div>
        </section>

        <section class="policy-section" id="start-return">
            <h2>Start Your Return</h2>
            <p>Please fill out the form below to begin your return process.</p>

            <?= $feedback_message ?>

            <form id="return-form" method="POST" action="#start-return">
                <div class="form-group">
                    <label for="order-number">Order Number*</label>
                    <input type="text" id="order-number" name="order-number" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address*</label>
                    <input type="email" id="email" name="email" required value="<?= isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : '' ?>">
                </div>

                <div class="form-group">
                    <label for="reason">Reason for Return*</label>
                    <select id="reason" name="reason" required>
                        <option value="">Select a reason</option>
                        <option value="wrong-item">Wrong item received</option>
                        <option value="defective">Defective or damaged</option>
                        <option value="no-longer-needed">No longer needed</option>
                        <option value="better-price-found">Found better price</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="details">Additional Details</label>
                    <textarea id="details" name="details" rows="4"></textarea>
                </div>

                <button type="submit" name="return-submit" class="btn">Submit Return Request</button>
            </form>
        </section>

        <section class="policy-section">
            <h2>Your Return History</h2>
            <?php
            if (isset($_SESSION['user_id'])) {
                try {
                    $stmt = $pdo->prepare("SELECT * FROM returns WHERE user_id = :user_id ORDER BY created_at DESC");
                    $stmt->execute([':user_id' => $_SESSION['user_id']]);
                    $returns = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($returns) > 0) {
                        echo '<div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                                <thead>
                                    <tr style="background-color: var(--royal-purple); color: white;">
                                        <th style="padding: 12px; text-align: left;">Return ID</th>
                                        <th style="padding: 12px; text-align: left;">Order ID</th>
                                        <th style="padding: 12px; text-align: left;">Reason</th>
                                        <th style="padding: 12px; text-align: left;">Status</th>
                                        <th style="padding: 12px; text-align: left;">Date</th>
                                        <th style="padding: 12px; text-align: left;">Details</th>
                                    </tr>
                                </thead>
                                <tbody>' . '';

                        foreach ($returns as $return) {
                            $status_color = '';
                            switch($return['status']) {
                                case 'approved':
                                    $status_color = 'color: #4caf50;';
                                    break;
                                case 'rejected':
                                    $status_color = 'color: #f44336;';
                                    break;
                                case 'processed':
                                    $status_color = 'color: #2196f3;';
                                    break;
                                default:
                                    $status_color = 'color: #ff9800;';
                            }

                            echo '<tr style="border-bottom: 1px solid var(--soft-violet);">
                                <td style="padding: 12px;">' . $return['return_id'] . '</td>
                                <td style="padding: 12px;">' . htmlspecialchars($return['order_id']) . '</td>
                                <td style="padding: 12px;">' . ucwords(str_replace('-', ' ', $return['reason'])) . '</td>
                                <td style="padding: 12px; font-weight: bold; ' . $status_color . '">' . ucfirst($return['status']) . '</td>
                                <td style="padding: 12px;">' . date('M j, Y', strtotime($return['created_at'])) . '</td>
                                <td style="padding: 12px;">' . htmlspecialchars($return['details']) . '</td>
                            </tr>';
                        }

                        echo '</tbody></table></div>';
                    } else {
                        echo '<p>No return requests have been made yet.</p>';
                    }
                } catch (PDOException $e) {
                    echo '<p>Error: Unable to fetch your return history. Please try again later.</p>';
                }
            }
            ?>
        </section>
    </div>

    <?php require 'footer.php'; ?>

</body>
</html>
