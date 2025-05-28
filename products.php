<?php
// Start session and DB connection
session_start();
require 'db_connect.php';

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query with JOIN to match category names
$query = "
    SELECT 
        p.id, p.name, p.description, p.price, p.stock, p.image,
        COALESCE(p.vendor_username, 'Vendor') AS vendor_username,
        COALESCE(c.name, 'Uncategorized') AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
";

if (!empty($searchTerm)) {
    $query .= " WHERE p.name LIKE :search OR p.description LIKE :search OR c.name LIKE :search";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':search', '%' . $searchTerm . '%', PDO::PARAM_STR);
} else {
    $query .= " ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($query);
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-lavender);
            margin-left: 200px;
            padding: 0;
        }

        header {
            background-color: var(--deep-indigo);
            color: white;
            padding: 7px 15px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-top: -10px;
        }

        .background {
            position: relative;
            height: 400px;
            background-size: cover;
            background-position: center;
            transition: background-image 2s ease-in-out;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .background::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .search-bar {
            margin: 2rem auto;
            max-width: 800px;
            z-index: 2;
            position: relative;
        }

        .search-bar form {
            display: flex;
            gap: 1rem;
            width: 100%;
        }

        .search-bar input[type="text"] {
            flex: 1;
            padding: 1rem 1.5rem;
            border: none;
            border-radius: 40px;
            font-size: 1rem;
            background: linear-gradient(145deg, var(--light-lavender), var(--soft-violet));
            box-shadow: 5px 5px 10px rgba(105, 30, 135, 0.15),
                        -5px -5px 10px rgba(234, 197, 255, 0.5);
            color: var(--deep-indigo);
            transition: box-shadow 0.3s ease, background 0.3s ease;
        }

        .search-bar input[type="text"]::placeholder {
            color: var(--medium-purple);
            font-style: italic;
        }

        .search-bar input[type="text"]:focus {
            outline: none;
            background: white;
            box-shadow: 0 0 8px 3px var(--blue-violet);
            color: var(--royal-purple);
        }

        .search-bar button {
            padding: 1rem 2.5rem;
            background: linear-gradient(45deg, var(--blue-violet), var(--dark-orchid));
            border: none;
            color: white;
            border-radius: 40px;
            cursor: pointer;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 6px 15px rgba(138, 43, 226, 0.4);
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .search-bar button:hover {
            background: linear-gradient(45deg, var(--dark-orchid), var(--blue-violet));
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(138, 43, 226, 0.6);
        }

        .product-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 25px;
            padding: 40px 5px;
            max-width: 1300px;
            margin-left: 100px;
        }

        .product-card {
            background-color: white;
            border: 2px solid var(--lavender);
            border-radius: 15px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
            border-color: var(--royal-purple);
        }

        .product-card img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .product-info {
            padding: 20px;
        }

        .product-info h3 {
            font-size: 20px;
            margin: 0 0 10px;
            color: var(--blue-violet);
        }

        .product-info p {
            margin: 6px 0;
            color: #444;
            font-size: 15px;
        }

        .product-info .price {
            font-weight: bold;
            font-size: 16px;
            color: var(--dark-orchid);
        }

        @media (max-width: 1024px) {
            body {
                margin-left: 0 !important;
            }

            .product-container {
                margin-left: 0;
                padding: 30px 15px;
                max-width: 100%;
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .product-container {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 20px;
                padding: 20px 15px;
            }

            .product-info h3 {
                font-size: 17px;
            }

            .product-info p {
                font-size: 13px;
            }

            .search-bar {
                padding: 0 15px;
                width: 100%;
            }

            .search-bar form {
                flex-direction: column;
                gap: 10px;
            }

            .search-bar input[type="text"],
            .search-bar button {
                width: 100%;
                border-radius: 30px;
                padding: 0.8rem 1rem;
                font-size: 1rem;
            }

            .search-bar button {
                padding: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .product-container {
                grid-template-columns: 1fr;
                padding: 15px 10px;
            }

            .product-info h3 {
                font-size: 16px;
            }

            .product-info p {
                font-size: 12px;
            }

            .search-bar h1 {
                font-size: 1.4rem;
                text-align: center;
            }

            .background h1 {
                font-size: 1.5rem;
                padding: 0 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<?php require 'navbar.php' ?>
<header>
    <h1>Explore Our Products</h1>
</header>

<div class="background">
    <h1 style="position: absolute; top: 30px; color: white; z-index: 2; font-size: 32px; text-shadow: 2px 2px 4px rgba(0,0,0,0.6); text-align: center; margin-bottom: 20px;">
        Discover Quality Products at Great Prices
    </h1>
    <div class="search-bar">
        <form method="GET" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
            <input type="text" name="search" placeholder="Search for products or categories..." value="<?= htmlspecialchars($searchTerm) ?>">
            <button type="submit">Search</button>
        </form>
    </div>
</div>

<div class="product-container">
    <?php foreach ($products as $product): ?>
        <a href="product_details.php?product_id=<?= $product['id'] ?>" class="product-card">
            <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <div class="product-info">
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p><?= htmlspecialchars($product['description']) ?></p>
                <p class="price">₵<?= number_format($product['price'], 2) ?></p>
                <p><small>Stock: <?= (int)$product['stock'] ?> | Vendor: <?= htmlspecialchars($product['vendor_username']) ?></small></p>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<?php require 'footer.php'; ?>

<script>
    const backgroundDiv = document.querySelector('.background');

    const backgroundImages = [
        'images/pic.png',
        'images/img.png',
        'images/Nes.png',
        'm3.png',
        'm4.png',
        'images/icecream.png',
        'm.jpg',
    ];

    let currentIndex = 0;

    function changeBackground() {
        backgroundDiv.style.backgroundImage = `url('${backgroundImages[currentIndex]}')`;
        currentIndex = (currentIndex + 1) % backgroundImages.length;
    }

    changeBackground();
    setInterval(changeBackground, 5000);
</script>

</body>
</html>
