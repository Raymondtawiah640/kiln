<?php
session_start();
require 'db_connect.php';  // Database connection

// Get search term and category_id from the GET request
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$categoryId = isset($_GET['category_id']) ? $_GET['category_id'] : 0;

// Build the SQL query based on search and category filters
$sql = "
    SELECT p.*, c.name AS category_name, p.vendor_username AS vendor_name, 
           p.vendor_telephone, p.vendor_residence 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
";

// If there's a search term, add to WHERE clause
if (!empty($searchTerm)) {
    $sql .= " WHERE p.name LIKE :searchTerm OR c.name LIKE :searchTerm OR p.vendor_username LIKE :searchTerm";
}

// If a category ID is specified, filter by that category
if ($categoryId > 0) {
    $sql .= " AND p.category_id = :categoryId";
}

try {
    $stmt = $pdo->prepare($sql);

    // Bind parameters
    if (!empty($searchTerm)) {
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%', PDO::PARAM_STR);
    }
    if ($categoryId > 0) {
        $stmt->bindValue(':categoryId', $categoryId, PDO::PARAM_INT);
    }

    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return products as a JSON response
    echo json_encode(['products' => $products]);
} catch (PDOException $e) {
    // Handle error
    echo json_encode(['error' => $e->getMessage()]);
}
?>
