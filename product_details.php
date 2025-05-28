<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'db_connect.php';

$productId = intval($_GET['product_id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT 
        p.id, p.name, p.description, p.price, p.stock, p.image,
        COALESCE(p.vendor_username, 'Vendor') AS vendor_name,
        COALESCE(p.vendor_telephone, '') AS vendor_telephone,
        COALESCE(p.whatsapp_number, '') AS whatsapp_number,
        NULLIF(TRIM(p.vendor_landmark), '') AS raw_landmark,
        COALESCE(c.name, 'Uncategorized') AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ?
");
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: landing_dashboard.php");
    exit();
}

$product['vendor_landmark'] = $product['raw_landmark'] ?? 'Not specified';
unset($product['raw_landmark']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Product Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="styles/product_details.css">
</head>
<body>

<header>
    <h1><i class="fas fa-shopping-bag"></i> Product Details</h1>
</header>

<?php include 'navbar.php'; ?>

<div class="container">
    <div class="product-detail-card">
        <div class="product-image-container">
            <img src="<?= htmlspecialchars($product['image'] ?: 'default_product.jpg') ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>" 
                 class="product-image" loading="lazy">
        </div>

        <div class="product-details">
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
            <span class="product-category">
                <i class="fas fa-tag"></i> <?= htmlspecialchars($product['category_name']) ?>
            </span>

            <div class="product-price">
                <?= number_format($product['price'], 2) ?>
            </div>

            <?php if ($product['stock'] > 10): ?>
                <span class="stock-badge in-stock">
                    <i class="fas fa-check-circle"></i> In Stock (<?= $product['stock'] ?> available)
                </span>
            <?php elseif ($product['stock'] > 0): ?>
                <span class="stock-badge low-stock">
                    <i class="fas fa-exclamation-circle"></i> Low Stock (<?= $product['stock'] ?> left)
                </span>
            <?php else: ?>
                <span class="stock-badge out-of-stock">
                    <i class="fas fa-times-circle"></i> Out of Stock
                </span>
            <?php endif; ?>

            <p class="product-description">
                <?= nl2br(htmlspecialchars($product['description'])) ?>
            </p>

            <form method="POST" action="add_to_cart.php">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                <div class="quantity-control">
                    <label for="quantity">Quantity:</label>
                    <button type="button" class="quantity-btn" id="decrease-btn">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                    <button type="button" class="quantity-btn" id="increase-btn">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>

                <button type="submit" class="add-to-cart-btn" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                    <i class="fas fa-shopping-cart"></i> Add to Cart
                </button>
            </form>

            <form method="POST" action="wishlist.php" class="wishlist-form">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <button type="submit" class="wishlist-btn">
                    <i class="fas fa-heart"></i> Add to Wishlist
                </button>
            </form>

            <div class="vendor-card">
                <h3 class="vendor-title">
                    <i class="fas fa-store"></i> Vendor Information
                </h3>

                <div class="vendor-detail">
                    <strong>Name:</strong>
                    <span><?= htmlspecialchars($product['vendor_name']) ?></span>
                </div>

                <div class="vendor-detail">
                    <strong>Nearby Landmark:</strong>
                    <span><?= htmlspecialchars($product['vendor_landmark']) ?></span>
                </div>

                <div class="contact-buttons">
                    <?php if (!empty($product['vendor_telephone'])): ?>
                        <a href="tel:<?= htmlspecialchars($product['vendor_telephone']) ?>" class="contact-btn call-btn">
                            <i class="fas fa-phone"></i> Call Vendor
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($product['whatsapp_number'])): ?>
                        <a href="https://wa.me/<?= htmlspecialchars($product['whatsapp_number']) ?>" 
                           target="_blank" class="contact-btn whatsapp-btn">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const quantityInput = document.getElementById('quantity');
    const decreaseBtn = document.getElementById('decrease-btn');
    const increaseBtn = document.getElementById('increase-btn');
    const maxQuantity = parseInt(quantityInput.max) || 999;
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    const wishlistBtn = document.querySelector('.wishlist-btn');

    const updateQuantity = (value) => {
        let qty = parseInt(value);
        if (isNaN(qty)) qty = 1;
        if (qty < 1) qty = 1;
        if (qty > maxQuantity) {
            qty = maxQuantity;
            showToast(`Only ${maxQuantity} item(s) available`);
        }
        quantityInput.value = qty;
    };

    decreaseBtn?.addEventListener('click', () => updateQuantity(quantityInput.value - 1));
    increaseBtn?.addEventListener('click', () => updateQuantity(quantityInput.value + 1));
    quantityInput?.addEventListener('change', () => updateQuantity(quantityInput.value));
    quantityInput?.addEventListener('keydown', e => {
        if (['e', 'E', '+', '-'].includes(e.key)) e.preventDefault();
    });

    if (maxQuantity <= 0) {
        [quantityInput, decreaseBtn, increaseBtn].forEach(el => el.disabled = true);
    }

    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => document.body.removeChild(toast), 300);
        }, 3000);
    }

    addToCartBtn?.addEventListener('click', function () {
        if (!this.disabled) {
            const original = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i> Added to Cart';
            setTimeout(() => this.innerHTML = original, 2000);
        }
    });

    wishlistBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        showToast("Item added to wishlist!");
        setTimeout(() => e.target.closest('form').submit(), 2000);
    });
});
</script>

</body>
</html>
