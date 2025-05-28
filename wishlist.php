<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Handle adding to wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);

    // Prevent duplicate wishlist entries
    $stmt = $pdo->prepare("SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);

    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$userId, $productId]);
    }

    header("Location: product_details.php?product_id=$productId&wishlist=added");
    exit();
}

// Handle removal from wishlist
if (isset($_GET['remove_from_wishlist'])) {
    $removeId = intval($_GET['remove_from_wishlist']);
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $removeId]);

    header("Location: wishlist.php");
    exit();
}

$stmt = $pdo->prepare("
    SELECT 
        p.id AS product_id,
        p.name AS product_name,
        p.description AS product_description,
        p.price,
        p.stock,
        p.image
    FROM products p
    INNER JOIN wishlist w ON p.id = w.product_id
    WHERE w.user_id = ?
");
$stmt->execute([$userId]);
$wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Wishlist</title>
    <link rel="stylesheet" href="styles/wishlist.css">
    <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon">
</head>
<body>

<!-- Include Navbar -->
<?php include 'navbar.php'; ?>
<div class="background">
    <h1>Your Wishlist</h1><br>
    <p>Your favorite picks, all in one place. Ready to shop when you are!</p>
</div>

<?php if ($userId): ?>
    <?php if (count($wishlist_items) > 0): ?>
        <div class="wishlist-container">
            <?php foreach ($wishlist_items as $item): ?>
                <div class="wishlist-item">
                    <a href="product_details.php?product_id=<?= $item['product_id'] ?>" class="product-link">
                        <img src="<?= htmlspecialchars($item['image'] ?: 'default_product.jpg') ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="product-image">
                        <h2 class="product-name"><?= htmlspecialchars($item['product_name']) ?></h2>
                    </a>
                    <p class="product-description"><?= htmlspecialchars($item['product_description']) ?></p>
                    <p class="product-price">Price: $<?= number_format($item['price'], 2) ?></p>
                    <p class="product-stock">Stock: <?= $item['stock'] ?> available</p>
                    <a href="wishlist.php?remove_from_wishlist=<?= $item['product_id'] ?>" class="remove-wishlist">Remove from Wishlist</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Your wishlist is empty.</p>
    <?php endif; ?>
<?php else: ?>
    <p>You need to <a href="login.php">log in</a> to view your wishlist.</p>
<?php endif; ?>

<script>
    const backgroundDiv = document.querySelector('.background');

    const backgroundImages = [
        'm7.png',
        'm1.png',
        'm2.png',
        'm3.png',
        'm4.png',
        'm5.png',
       'm6.png',
       'm.jpg',
    ];

    let currentIndex = 0;

    function changeBackground() {
        backgroundDiv.style.backgroundImage = `url('${backgroundImages[currentIndex]}')`;
        currentIndex = (currentIndex + 1) % backgroundImages.length;
    }

    // Initial load
    changeBackground();

    // Change every 5 seconds
    setInterval(changeBackground, 5000);
</script>

</body>
</html>
