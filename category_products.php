<?php
session_start();
require 'db_connect.php'; // Database connection

// Validate and sanitize category ID from query parameter
if (!isset($_GET['category_id']) || !is_numeric($_GET['category_id'])) {
    die("Invalid category ID.");
}
$categoryId = (int) $_GET['category_id'];

// Fetch category name
try {
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$category) {
        die("Category not found.");
    }
} catch (PDOException $e) {
    die("Error fetching category: " . htmlspecialchars($e->getMessage()));
}

// Fetch products in this category with available stock
// Fetch products in this category with available stock
try {
    $stmt = $pdo->prepare("
        SELECT 
            id, name, description, price, stock, image, 
            COALESCE(vendor_username, 'Vendor') AS vendor_name,
            COALESCE(vendor_landmark, 'Landmark not specified') AS vendor_landmark
        FROM products
        WHERE category_id = ? AND stock > 0
    ");
    $stmt->execute([$categoryId]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching products: " . htmlspecialchars($e->getMessage()));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Products in <?= htmlspecialchars($category['name']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        /* Root colors */
        :root {
            --deep-indigo: #4b0082;
            --royal-purple: #6a0dad;
            --soft-violet: #9370db;
            --lavender: #e6e6fa;
            --light-lavender: #f8f5ff;
            --orchid: #da70d6;
            --dark-orchid: #9932cc;
            --blue-violet: #8a2be2;
            --medium-purple: #9370db;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            background: var(--light-lavender);
            color: #2d1b4e;
            line-height: 1.6;
            margin-left: 280px;
            transition: margin-left 0.3s ease;
        }

        header {
            background: linear-gradient(135deg, var(--deep-indigo) 0%, var(--royal-purple) 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 12px rgba(75, 0, 130, 0.2);
            position: relative;
            z-index: 10;
        }

        header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        main {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            min-height: calc(100vh - 200px);
        }

        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .category-header h2 {
            color: var(--deep-indigo);
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            position: relative;
            padding-bottom: 0.5rem;
        }

        .category-header h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(to right, var(--medium-purple), var(--deep-indigo));
            border-radius: 2px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(75, 0, 130, 0.1);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(75, 0, 130, 0.15);
        }

        .product-image {
            height: 200px;
            background: var(--lavender);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            border-bottom: 1px solid rgba(230, 230, 250, 0.5);
        }

        .product-image img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }

        .product-info {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-info h3 {
            color: var(--deep-indigo);
            margin: 0 0 0.5rem;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .product-description {
            color: #4a3a6e;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            flex-grow: 1;
        }

        .product-vendor {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #4a3a6e;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .product-vendor i {
            color: var(--medium-purple);
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .product-price {
            color: var(--blue-violet);
            font-weight: 700;
            font-size: 1.2rem;
        }

        .product-stock {
            background: rgba(154, 89, 219, 0.1);
            color: var(--royal-purple);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .product-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .view-button {
            flex: 1;
            text-align: center;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            background: linear-gradient(to right, var(--soft-violet), var(--medium-purple));
            color: white;
            text-decoration: none;
        }

        .view-button:hover {
            background: linear-gradient(to right, var(--medium-purple), var(--soft-violet));
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            grid-column: 1 / -1;
        }

        .empty-state p {
            color: #4a3a6e;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
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

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            }
            .category-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 480px) {
            main {
                padding: 1.5rem;
            }
            .product-grid {
                grid-template-columns: 1fr;
            }
            .product-actions {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>

<header>
    <h1><i class="fas fa-tag"></i> <?= htmlspecialchars($category['name']) ?></h1>
</header>

<?php include 'navbar.php'; ?>

<main>
    <div class="category-header">
        <h2>Available Products</h2>
    </div>

    <?php if (empty($products)) : ?>
        <div class="empty-state">
            <i class="fas fa-box-open" style="font-size: 3rem; color: var(--medium-purple); margin-bottom: 1rem;"></i>
            <p>No products found in this category.</p>
            <a href="categories.php" class="view-button" style="max-width: 200px; margin: 0 auto;">
                <i class="fas fa-arrow-left"></i> Browse Categories
            </a>
        </div>
    <?php else : ?>
        <div class="product-grid">
            <?php foreach ($products as $product) : ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if (!empty($product['image'])) : ?>
                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy" />
                        <?php else : ?>
                            <i class="fas fa-image" style="font-size: 3rem; color: var(--medium-purple);"></i>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>

                        <div class="product-vendor">
                            <i class="fas fa-user"></i>
                            <span><?= htmlspecialchars($product['vendor_name']) ?></span>
                        </div>

                        <div class="product-vendor">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= htmlspecialchars($product['vendor_landmark']) ?></span>
                        </div>


                        <div class="product-meta">
                            <span class="product-price">GH₵<?= number_format($product['price'], 2) ?></span>
                            <span class="product-stock"><?= (int)$product['stock'] ?> in stock</span>
                        </div>

                        <a href="product_details.php?product_id=<?= (int)$product['id'] ?>" class="view-button">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
