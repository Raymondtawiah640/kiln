<?php
session_start();
require 'db_connect.php';

// Maintenance Mode Check
$maintenanceMode = false;

if ($maintenanceMode) {
    echo '<h1>Site Under Maintenance</h1>';
    echo '<p>We are currently performing maintenance. Please check back later.</p>';
    exit;
}

// Cart count
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $_SESSION['cart_count'] = (int)$stmt->fetchColumn() ?? 0;
} else {
    $_SESSION['cart_count'] = 0;
}

// Function to get stock color
function getStockColor($stock) {
    if ($stock <= 1) return '#e74c3c';
    if ($stock <= 5) return '#f1c40f';
    return '#2ecc71';
}

// Fetch random products
$randomProducts = $pdo->query("
    SELECT p.id, p.name, p.description, p.price, p.stock, p.image,
           COALESCE(p.vendor_username, 'Vendor') AS vendor_name,
           COALESCE(p.vendor_telephone, '') AS vendor_telephone,
           COALESCE(p.whatsapp_number, '') AS whatsapp_number,
           COALESCE(p.vendor_landmark, 'Location not specified') AS vendor_landmark,
           COALESCE(c.name, 'Uncategorized') AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY RAND()
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

// Search query
$searchTerm = trim($_GET['search'] ?? '');

$query = "
    SELECT p.id, p.name, p.description, p.price, p.stock, p.image,
           COALESCE(p.vendor_username, 'Vendor') AS vendor_name,
           COALESCE(p.vendor_telephone, '') AS vendor_telephone,
           COALESCE(p.whatsapp_number, '') AS whatsapp_number,
           COALESCE(p.vendor_landmark, 'Location not specified') AS vendor_landmark,
           COALESCE(c.name, 'Uncategorized') AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
";
if ($searchTerm) {
    $query .= " WHERE c.name LIKE :search OR p.name LIKE :search OR p.description LIKE :search";
}
$stmt = $pdo->prepare($query);
if ($searchTerm) {
    $stmt->bindValue(':search', "%$searchTerm%", PDO::PARAM_STR);
}
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Homepage</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
   <header>
  <div class="logo-container">
    <a href="landing_dashboard.php">
      <img src="images/kilnmart_logo.png" alt="Kiln Store Logo" class="logo">
    </a>
  </div>
</header>

<?php include 'navbar.php'; ?>

<div class="update-message" id="updateMessage" style="display: flex; align-items: center; justify-content: center; gap: 10px;">
  <img id="messageImage" src="" alt="Promo Image" style="max-height: 70px; border-radius: 5px;" />
  <span id="messageText"></span>
</div>

<div class="background" id="backgroundSlider">
    <div class="container">
        <div class="search-bar">
            <form method="GET" action="landing_dashboard.php">
                <input type="text" name="search" placeholder="Search for products or categories..." value="<?= htmlspecialchars($searchTerm) ?>">
                <button type="submit">Search</button>
            </form>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <h1>Welcome to Kiln Store</h1>
        <p>Your online marketplace!</p>
    </div>
</div>

<div class="card sale-card">
    <h3>Available Products</h3>
    <div class="product-grid">
        <?php if (empty($products)): ?>
            <p style="text-align: center; color: #555;">No products found.</p>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <a href="product_details.php?product_id=<?= htmlspecialchars($product['id']) ?>">
                        <img src="<?= htmlspecialchars($product['image'] ?: 'default_product.jpg') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <h4><?= htmlspecialchars($product['name']) ?></h4>
                        <p>GH₵<?= htmlspecialchars($product['price']) ?></p>
                        <div class="stock-indicator" style="background-color: <?= getStockColor($product['stock']) ?>;">
                            <?= $product['stock'] > 1 ? $product['stock'] . ' in stock' : 'Only 1 left!' ?>
                        </div>
                        <!-- Optional: display vendor landmark -->
                        <!-- <small><?= htmlspecialchars($product['vendor_landmark']) ?></small> -->
                    </a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php // include 'category/fashion_and_apparel.php'; ?> 
<?php // include 'category/jewelry_and_accessories.php'; ?>
<?php // include 'category/smart_home_devices.php'; ?>
<?php // include 'category/sports_outdoors.php'; ?>

<?php include 'flash_sales.php'; ?>
<?php require 'footer.php'; ?>

<script>
const messages = [
  { text: "Father's Day is coming! Get gifts for your loved ones!", img: "images/fathers_day.png" },
  { text: "Big sale coming soon! Stay tuned!", img: "images/big_sale.png" },
  { text: "New arrivals! Check out the latest products.", img: "images/new_arrivals.png" },
  { text: "Exclusive offers only for you, visit our store today!", img: "images/exclusive_offers.png" }
];

const messageImage = document.getElementById('messageImage');
const messageText = document.getElementById('messageText');
let currentMessageIndex = 0;

function rotateMessage() {
    const message = messages[currentMessageIndex];
    messageImage.src = message.img;
    messageImage.alt = message.text;
    messageText.textContent = message.text;
    currentMessageIndex = (currentMessageIndex + 1) % messages.length;
    setTimeout(rotateMessage, 5000);
}
rotateMessage();

const backgroundImages = [
    'sale.png', 'm.jpg', 'm1.png', 'm2.png', 'm3.png', 'm4.png', 'm6.png', 'm7.png'
];
let currentImageIndex = 0;
const bgSlider = document.getElementById('backgroundSlider');

function updateBackground() {
    bgSlider.style.backgroundImage = `url('${backgroundImages[currentImageIndex]}')`;
    currentImageIndex = (currentImageIndex + 1) % backgroundImages.length;
}
updateBackground();
setInterval(updateBackground, 5000);
</script>
</body>
</html>
