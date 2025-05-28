<?php
session_start();
require 'db_connect.php';

const LOW_STOCK_THRESHOLD = 5;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login to access the vendor dashboard.");
    exit();
}

$userId = $_SESSION['user_id'];
$products = [];
$errorMessage = null;
$lowStockWarning = false;

if (isset($_GET['remove_id'])) {
    $removeId = (int) $_GET['remove_id'];
    $deleteStmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
    if ($deleteStmt->execute([$removeId, $userId])) {
        header("Location: vendor_dashboard.php?success=Product removed successfully.");
        exit();
    } else {
        $errorMessage = "Error removing product.";
    }
}

try {
    $stmt = $pdo->prepare("SELECT p.*, COUNT(oi.id) AS orders_received
                            FROM products p
                            LEFT JOIN order_items oi ON p.id = oi.product_id
                            WHERE p.user_id = ?
                            GROUP BY p.id
                            ORDER BY p.id DESC");
    $stmt->execute([$userId]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as $product) {
        if ((int) $product['stock'] < LOW_STOCK_THRESHOLD) {
            $lowStockWarning = true;
            break;
        }
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $errorMessage = "Error loading products.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/vendor_dashboard.css">
    <link rel="shortcut icon" href="logo.ico" type="image/x-icon">
</head>
<body>

<?php require 'navbar.php'; ?>

<header>
    <h1><i class="fas fa-store-alt"></i> Vendor Dashboard</h1>
</header>

<main>
    <div class="dashboard-header">
        <h2>Your Products</h2>
        <a href="vendor_upload.php" class="upload-link">
            <i class="fas fa-plus"></i> Upload New Product
        </a>
    </div>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <?php if ($lowStockWarning): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> Some of your products have low stock. Consider restocking soon.
        </div>
    <?php endif; ?>

    <div class="product-grid">
        <?php if (empty($products)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open" style="font-size: 3rem; color: var(--medium-purple); margin-bottom: 1rem;"></i>
                <p>You haven't uploaded any products yet.</p>
                <a href="vendor_upload.php" class="upload-link">
                    <i class="fas fa-plus"></i> Add Your First Product
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <div class="product-image">
                        <?php if (!empty($product['image'])): ?>
                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                        <?php else: ?>
                            <i class="fas fa-image" style="font-size: 3rem; color: var(--medium-purple);"></i>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>

                        <div class="product-meta">
                            <span class="product-price">GH₵<?= number_format($product['price'], 2) ?></span>
                            <span class="product-stock <?= ($product['stock'] < LOW_STOCK_THRESHOLD) ? 'low-stock' : '' ?>">
                                <?= (int) $product['stock'] ?> in stock
                            </span>
                            <span class="product-orders">
                                <?= (int) $product['orders_received'] ?> orders
                            </span>
                        </div>

                        <div class="product-actions">
                            <a href="vendor_edit.php?id=<?= $product['id'] ?>" class="edit-button">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?remove_id=<?= $product['id'] ?>" class="remove-button" onclick="return confirm('Are you sure you want to remove this product?');">
                                <i class="fas fa-trash-alt"></i> Remove
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php require 'footer.php'; ?>

<script>
    document.querySelectorAll('.remove-button').forEach(button => {
        button.addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to remove this product?')) {
                e.preventDefault();
            }
        });
    });
</script>

<style>
    .low-stock {
        color: red;
        font-weight: bold;
    }

    .alert-warning {
        background-color: #ffe5e5;
        color: red;
        border-left: 5px solid red;
        padding: 10px;
        margin-bottom: 15px;
        font-weight: bold;
    }

    .product-orders {
        display: block;
        margin-top: 5px;
        color: gray;
        text-transform: capitalize;
    }
</style>

</body>
</html>
