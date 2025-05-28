<?php
// Database connection
include 'db_connect.php';

// Sanitize and validate category input
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

if (empty($category)) {
    echo json_encode(['error' => 'Category not specified']);
    exit;
}

// Use try-catch block for error handling
try {
    $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE category_name = ?");
    $stmt->execute([$category]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($products)) {
        echo json_encode(['message' => 'No products found for this category']);
    } else {
        echo json_encode($products);
    }
} catch (Exception $e) {
    // Catch any database errors and return an error message
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
