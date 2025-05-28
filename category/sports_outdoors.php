<?php
require 'db_connect.php';

try {
    // Get the category ID for "Sports & Outdoors"
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute(['Sports & Outdoors']);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        die("Sports & Outdoors category not found.");
    }

    $categoryId = $category['id'];

    // Fetch 6 random products from the category with stock > 0
    $stmt = $pdo->prepare("
        SELECT id, name, price, stock, image 
        FROM products 
        WHERE category_id = ? AND stock > 0 
        ORDER BY RAND() 
        LIMIT 6
    ");
    $stmt->execute([$categoryId]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$products) {
        die("No products found in this category.");
    }

} catch (Exception $e) {
    error_log("Error fetching products: " . $e->getMessage());
    die("Error fetching products.");
}

// Reuse getStockColor function if available
if (!function_exists('getStockColor')) {
    function getStockColor($stock) {
        if ($stock <= 1) return '#e74c3c';      // Only 1 left (Red)
        elseif ($stock <= 5) return '#f1c40f';  // Low stock (Yellow)
        return '#2ecc71';                       // In stock (Green)
    }
}
?>

<link rel="stylesheet" href="flash_sales.css">

<!-- Sports & Outdoors Section -->      
<div class="card" style="margin-top: 2rem;">
    <div class="flash-sales-heading" style="justify-content: space-between;">
        <div><i class="fas fa-basketball-ball"></i> Sports & Outdoors</div>
        <a class="see-all" href="category_products.php?category_id=<?= $categoryId ?>">See All &raquo;</a>
    </div>
    <div class="flash-scroll-wrapper">
        <button class="flash-arrow left" onclick="scrollFlash('left', 'sports-scroll')" aria-label="Scroll left"><i class="fas fa-chevron-left"></i></button>
        <div class="flash-scroll-container" id="sports-scroll">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <a href="product_details.php?product_id=<?= htmlspecialchars($product['id']) ?>">
                        <img src="<?= htmlspecialchars($product['image'] ?: 'default_product.jpg') ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
                        <h4><?= htmlspecialchars($product['name']) ?></h4>
                        <div class="price">GH₵<?= htmlspecialchars($product['price']) ?></div>
                        <div class="stock-indicator" style="background-color: <?= getStockColor($product['stock']) ?>;">
                            <?= $product['stock'] > 1 ? $product['stock'] . ' in stock' : 'Only 1 left!' ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="flash-arrow right" onclick="scrollFlash('right', 'sports-scroll')" aria-label="Scroll right"><i class="fas fa-chevron-right"></i></button>
    </div>
</div>

<script>
// Scroll functionality for multiple scroll containers
function scrollFlash(direction, containerId) {
    const container = document.getElementById(containerId);
    const scrollAmount = container.clientWidth * 0.8;

    if (direction === 'left') {
        container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
        container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
}

// Add touch/mouse drag support for multiple scroll containers
document.addEventListener('DOMContentLoaded', function() {
    const containers = ['flash-scroll', 'jewelry-scroll', 'fashion-scroll', 'sports-scroll'];
    containers.forEach(id => {
        const container = document.getElementById(id);
        if (!container) return;

        let isDown = false;
        let startX;
        let scrollLeft;

        container.addEventListener('mousedown', (e) => {
            isDown = true;
            startX = e.pageX - container.offsetLeft;
            scrollLeft = container.scrollLeft;
        });

        container.addEventListener('mouseleave', () => {
            isDown = false;
        });

        container.addEventListener('mouseup', () => {
            isDown = false;
        });

        container.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - container.offsetLeft;
            const walk = (x - startX) * 2;
            container.scrollLeft = scrollLeft - walk;
        });
    });
});
</script>
