<?php
session_start();
require 'db_connect.php'; // Database connection

// Fetch all categories that have products uploaded by vendors
try {
    $stmt = $pdo->query("
        SELECT DISTINCT c.id, c.name 
        FROM categories c
        INNER JOIN products p ON c.id = p.category_id
        WHERE p.stock > 0
        ORDER BY c.name ASC
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon">
    <style>
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
    background: url('m5.png') no-repeat center center;
    background-size: cover;
    color: white;
    padding: 3rem 1.5rem;
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
            gap: 0.75rem;
        }

        main {
            padding: 2rem;
            max-width: 1000px;
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
            position: relative;
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

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .category-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 18px rgba(75, 0, 130, 0.1);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 1.5rem;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(75, 0, 130, 0.15);
        }

        .category-icon {
            width: 80px;
            height: 80px;
            background: var(--lavender);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            color: var(--royal-purple);
            font-size: 2rem;
        }

        .category-name {
            color: var(--deep-indigo);
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .category-link {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background: linear-gradient(to right, var(--blue-violet), var(--dark-orchid));
            color: white;
            border-radius: 50px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .category-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(138, 43, 226, 0.3);
            background: linear-gradient(to right, var(--dark-orchid), var(--blue-violet));
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
            .category-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
            
            main {
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .category-grid {
                grid-template-columns: 1fr;
            }
            
            .category-header {
                flex-direction: column;
                align-items: center;
                position: relative;
                top: 30px
            }
        }
    </style>
</head>
<body>
    <header>
        <h1><i class="fas fa-tags"></i> Product Categories</h1>
    </header>
    
    <?php include 'navbar.php'; ?>
    <br>
    <br>
    <main>
        <div class="category-header">
            <h2>Browse by Category</h2>
        </div>
        
        <div class="category-grid">
            <?php if (empty($categories)): ?>
                <div class="empty-state">
                    <i class="fas fa-box-open" style="font-size: 3rem; color: var(--medium-purple); margin-bottom: 1rem;"></i>
                    <p>No categories available at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <h3 class="category-name"><?= htmlspecialchars($category['name']) ?></h3>
                        <a href="category_products.php?category_id=<?= $category['id'] ?>" class="category-link">
                            View Products
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>