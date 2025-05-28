<?php
require 'db_connect.php';

if(session_start() === PHP_SESSION_NONE) {
    session_start();
}

function getStockColor($stock) {
    if ($stock <= 1) return '#e74c3c';      // Red
    elseif ($stock <= 5) return '#f1c40f';  // Yellow
    return '#2ecc71';                       // Green
}

// Fetch flash sale products (low stock)
$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.description, p.price, p.stock, p.image,
           COALESCE(c.name, 'Uncategorized') AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.stock <= 5 AND p.stock > 0
    ORDER BY p.stock ASC
");
$stmt->execute();
$flashProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Flash Sales - Kiln Enterprise</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="flash_sales_dashboard.css">
    <link rel="shortcut icon" href="logo1.ico" type="image/x-icon" />

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background-color: #f9f9f9;
        }

         .container {
  max-width: 7000px; /* too wide, might cause layout issues */
  margin: 0 auto;
  padding: 2rem;
  min-height: 100vh;
}


        .content-wrapper {
            flex: 1;
            padding: 20px;
            margin-left: 240px; /* Space for sidebar/navbar */
            box-sizing: border-box;
        }

        header {
    background: url('m5.png') no-repeat center center;
    background-size: cover;
    color: white;
    padding: 3rem 5rem;
    text-align: center;
    box-shadow: 0 4px 12px rgba(75, 0, 130, 0.2);
    position: relative;
    z-index: 10;
}

header::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(75, 0, 130, 0.4); /* purple overlay */
    z-index: 0;
}
header h1 {
    position: relative;
    color: white;
    z-index: 1;
}


        header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5rem;
        }

        .flash-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .product-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: transform 0.3s ease;
            text-align: center;
            padding: 0.5rem;
        }

        .product-card:hover {
            transform: scale(1.02);
        }

        .product-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .product-card h4 {
            margin: 10px 0;
            color: #333;
            font-size: 1.1rem;
        }

        .product-card a {
            text-decoration: none;
            color: inherit;
        }

        .price {
            color: #6a1b9a;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stock-indicator {
            padding: 5px;
            color: white;
            font-size: 0.9em;
        }

        .no-products {
            font-size: 1.1rem;
            color: #777;
        }

        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .content-wrapper {
                margin-left: 200px;
            }
        }

        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
                padding: 15px;
            }

            h1 {
                font-size: 1.5rem;
            }
              .flash-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
           .container{
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .product-card img {
                height: 150px;
            }

            .product-card h4 {
                font-size: 1rem;
            }

            .price {
                font-size: 0.95rem;
            }

            .stock-indicator {
                font-size: 0.85rem;
            }

             .flash-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 992px) {
            body {
                margin-left: 0;
                padding-top: 80px;
            }
            
            header {
                width: 100%;
                position: fixed;
                top: 0;
                left: 0;
                z-index: 100;
            }
        }
    </style>
</head>
<body>

<?php require 'navbar.php'; ?>

<div class="container">
    <div class="content-wrapper">
        <header>
             <h1>Flash Sales</h1>
        </header>
        <br>
        <br>
        <?php if (count($flashProducts) > 0): ?>
            <div class="flash-grid">
                <?php foreach ($flashProducts as $product): ?>
                    <div class="product-card">
                        <a href="product_details.php?product_id=<?= htmlspecialchars($product['id']) ?>">
                            <img src="<?= htmlspecialchars($product['image'] ?: 'default_product.jpg') ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            <h4><?= htmlspecialchars($product['name']) ?></h4>
                            <div class="price">GH₵<?= htmlspecialchars($product['price']) ?></div>
                            <div class="stock-indicator" style="background-color: <?= getStockColor($product['stock']) ?>;">
                                <?= $product['stock'] > 1 ? $product['stock'] . ' in stock' : 'Only 1 left!' ?>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-products">No flash sale products at the moment. Check back soon!</p>
        <?php endif; ?>
    </div>
</div>
<?php require 'footer.php'?>
</body>
</html>
