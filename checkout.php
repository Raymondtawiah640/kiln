<?php
session_start();
require 'db_connect.php';

// Fetch status and message for SweetAlert pop-up
$status = isset($_GET['status']) ? $_GET['status'] : null;
$message = isset($_GET['message']) ? urldecode($_GET['message']) : null;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if user has previous orders
$orderCheck = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$orderCheck->execute([$_SESSION['user_id']]);
$orderCount = $orderCheck->fetchColumn();

if ($orderCount == 0 && !isset($_GET['new_user'])) {
    // Redirect new users to step 1 and show welcome alert
    header("Location: checkout.php?step=1&new_user=1");
    exit();
}

// Fetch cart items for the current user
$stmt = $pdo->prepare("
    SELECT c.*, p.id AS product_id, p.name, p.description, p.price, p.stock, p.image
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subtotal = 0;
$totalQuantity = 0;

foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $totalQuantity += $item['quantity'];
}

$delivery_fee = 8.00;
$totalDeliveryFee = $delivery_fee * $totalQuantity;
$total = $subtotal + $totalDeliveryFee;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Checkout</title>
    <link rel="stylesheet" href="styles/checkout.css">
    <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php require 'navbar.php'; ?>

<div class="checkout-container">

    <!-- Left Column (Checkout Form) -->
    <div class="checkout-left">
        <header class="checkout-header">
            <h1>Checkout</h1>
        </header>

        <div class="checkout-steps">
            <div class="checkout-step active" id="step1">
                <div class="step-number">1</div>
                <br>
                <div class="step-label active">Payment</div>
            </div>
            <div class="checkout-step" id="step2">
                <div class="step-number">2</div>
                <br>
                <div class="step-label">Review</div>
            </div>
            <div class="checkout-step" id="step3">
                <div class="step-number">3</div>
                <br>
                <div class="step-label">Confirm</div>
            </div>
        </div>

        <form class="checkout-form" action="process_checkout.php" method="POST">
            <div class="form-section">
                <h2>Payment Method</h2>

                <label class="payment-method">
                    <input type="radio" name="payment_method" value="momo" checked>
                    <div>
                        <h3>Mobile Money on Delivery</h3>
                        <p>Pay via mobile money when you receive your item</p>
                    </div>
                </label>

                <label class="payment-method">
                    <input type="radio" name="payment_method" value="cash">
                    <div>
                        <h3>Cash on Delivery</h3>
                        <p>Pay with cash when the item is delivered to you</p>
                    </div>
                </label>
            </div>

            <button type="submit" class="btn">Place Order</button>
        </form>
    </div>

    <!-- Right Column (Order Summary) -->
    <aside class="checkout-summary">
        <div class="summary-header">
            <h2>Order Summary</h2>
        </div>
        <div class="summary-items">
            <?php foreach ($cartItems as $item): ?>
                <div class="summary-item">
                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                    <span>GH₵ <?= number_format($item['price'], 2) ?> x <?= $item['quantity'] ?></span>
                    <span>GH₵ <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="summary-item">
                <span>Delivery Fee</span>
                <span>GH₵ <?= number_format($totalDeliveryFee, 2) ?></span>
            </div>
        </div>
        <div class="summary-total">
            <span>Total</span>
            <span>GH₵ <?= number_format($total, 2) ?></span>
        </div>
    </aside>

</div>

<?php if ($status && $message): ?>
<script>
Swal.fire({
    icon: '<?= $status === "success" ? "success" : "error" ?>',
    title: '<?= $status === "success" ? "Success" : "Error" ?>',
    text: "<?= htmlspecialchars($message) ?>",
});
</script>
<?php endif; ?>

<?php if (isset($_GET['new_user'])): ?>
<script>
Swal.fire({
    icon: 'info',
    title: 'Welcome!',
    text: 'Let’s walk you through your first order.',
});
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const steps = document.querySelectorAll('.checkout-step');

    const urlParams = new URLSearchParams(window.location.search);
    const stepParam = parseInt(urlParams.get('step'), 10);

    let currentStep = (!isNaN(stepParam) && stepParam >= 1 && stepParam <= steps.length) ? stepParam : 1;

    function updateSteps(step) {
        steps.forEach((s, index) => {
            s.classList.remove('active');
            if (index + 1 === step) {
                s.classList.add('active');
            }
        });
    }

    updateSteps(currentStep);

    document.querySelector('.checkout-form').addEventListener('submit', function () {
        if (currentStep < steps.length) {
            currentStep++;
            updateSteps(currentStep);
        }
    });
});
</script>

</body>
</html>
