<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT c.*, p.id AS product_id, p.name, p.description, p.price, p.stock, p.image
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$delivery_fee = 8.00;
$total = $subtotal + $delivery_fee;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Shopping Cart</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="styles/cart.css">
  <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon">
</head>
<body>

<?php require 'navbar.php' ?>

<<header>
  <div class="header-image">
    <img src="images/kilnmart_logo.png" alt="Shopping Cart Header Image" style="height: 60px; width: auto;">
  </div>
  <h1><i class="fas fa-shopping-cart"></i> Shopping Cart</h1>
</header>

<div class="container">
  <div class="cart-wrapper">
    <div class="cart-items-container">
      <h2>Your Cart Items</h2>
      <?php if (count($cartItems) > 0): ?>
        <?php foreach ($cartItems as $item): ?>
          <div class="cart-item">
            <div class="cart-item-image">
              <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
            </div>
            <div class="cart-item-details">
              <h3><?= htmlspecialchars($item['name']) ?></h3>
              <p><?= htmlspecialchars(substr($item['description'], 0, 100)) ?>...</p>
              <p class="cart-item-price">GH₵ <?= number_format($item['price'], 2) ?></p>
            </div>
            <div class="cart-item-actions">
              <form method="POST" action="update_cart.php" class="quantity-control">
                <input type="hidden" name="cart_id" value="<?= htmlspecialchars($item['id']) ?>">
                <button type="submit" name="action" value="decrement" class="quantity-btn">-</button>
                <input type="number" class="quantity-input" name="quantity" value="<?= htmlspecialchars($item['quantity']) ?>" min="1" max="<?= htmlspecialchars($item['stock']) ?>" readonly>
                <button type="submit" name="action" value="increment" class="quantity-btn">+</button>
              </form>
              <form method="POST" action="remove_from_cart.php">
                <input type="hidden" name="cart_id" value="<?= htmlspecialchars($item['id']) ?>">
                <button type="submit" class="remove-btn"><i class="fas fa-trash-alt"></i> Remove</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-cart">
          <i class="fas fa-shopping-cart"></i>
          <h3>Your cart is empty</h3>
          <p>Start shopping to add items to your cart</p>
          <a href="products.php" class="continue-shopping-btn">
        <i class="fas fa-arrow-left"></i> Continue Shopping
      </a>
        </div>
      <?php endif; ?>
    </div>

    <?php if (count($cartItems) > 0): ?>
    <div class="cart-summary">
      <h2>Order Summary</h2>
      <div class="summary-row">
        <span>Subtotal:</span>
        <span>GH₵ <?= number_format($subtotal, 2) ?></span>
      </div>
      <div class="summary-row">
        <span>Delivery Fee:</span>
        <span>GH₵ <?= number_format($delivery_fee, 2) ?></span>
      </div>
      <div class="summary-row summary-total">
        <strong>Total:</strong>
        <strong>GH₵ <?= number_format($total, 2) ?></strong>
      </div>
      <a href="checkout.php" class="checkout-btn">
        <i class="fas fa-credit-card"></i> Proceed to Checkout
      </a>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require 'footer.php' ?>
</body>
</html>
