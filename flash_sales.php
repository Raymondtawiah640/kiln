<?php
require 'db_connect.php';

// Function to get stock color
if (!function_exists('getStockColor')) {
    function getStockColor($stock) {
        if ($stock <= 1) return '#e74c3c';      // Out of stock (Red)
        elseif ($stock <= 5) return '#f1c40f';  // Low stock (Yellow)
        return '#2ecc71';                       // In stock (Green)
    }
}

// Fetch flash sale products (e.g., marked by low stock or featured flag)
$flashSalesQuery = "
    SELECT 
        p.id, p.name, p.description, p.price, p.stock, p.image,
        COALESCE(c.name, 'Uncategorized') AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.stock > 0
    ORDER BY RAND()
    LIMIT 6
";

$stmt = $pdo->query($flashSalesQuery);
$flashProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="styles/flash_sales.css">

<!-- Flash Sales Section -->
<div class="card">
    <div class="flash-sales-heading">
        <div><i class="fas fa-bolt"></i> Flash Sales</div>
        <div class="flash-timer">
            <span id="countdown">Ends in 02h : 30m : 00s</span>
            <a class="see-all" href="flash_sales_dashboard.php">See All &raquo;</a>
        </div>
    </div>
    <div class="flash-scroll-wrapper">
        <button class="flash-arrow left" onclick="scrollFlash('left')" aria-label="Scroll left"><i class="fas fa-chevron-left"></i></button>
        <div class="flash-scroll-container" id="flash-scroll">
            <?php foreach ($flashProducts as $product): ?>
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
        <button class="flash-arrow right" onclick="scrollFlash('right')" aria-label="Scroll right"><i class="fas fa-chevron-right"></i></button>
    </div>
</div>

<script>
// Scroll functionality
function scrollFlash(direction) {
    const container = document.getElementById('flash-scroll');
    const scrollAmount = container.clientWidth * 0.8;
    
    if (direction === 'left') {
        container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else {
        container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
}

// Countdown timer
function updateCountdown() {
    const countdownElement = document.getElementById('countdown');
    let hours = 2, minutes = 30, seconds = 0;
    
    const timer = setInterval(() => {
        if (seconds === 0) {
            if (minutes === 0) {
                if (hours === 0) {
                    clearInterval(timer);
                    countdownElement.textContent = "Sale has ended!";
                    return;
                }
                hours--;
                minutes = 59;
            } else {
                minutes--;
            }
            seconds = 59;
        } else {
            seconds--;
        }
        
        countdownElement.textContent = `Ends in ${hours.toString().padStart(2, '0')}h : ${minutes.toString().padStart(2, '0')}m : ${seconds.toString().padStart(2, '0')}s`;
    }, 1000);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateCountdown();
    
    // Touch support for mobile
    const container = document.getElementById('flash-scroll');
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
</script>
