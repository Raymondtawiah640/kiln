<?php
session_start();
require 'db_connect.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['product_id']) && isset($_SESSION['user_id'])) {
        $productId = $_POST['product_id']; // Get product ID from the request
        $userId = $_SESSION['user_id']; // Assuming user ID is stored in session after login

        // Fetch product information to check if it exists
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($product) {
            // Check if the product is already in the cart
            $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$userId, $productId]);
            $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cartItem) {
                // If the product is already in the cart, update the quantity
                $newQuantity = $cartItem['quantity'] + 1; // Increment quantity
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$newQuantity, $cartItem['id']]);
            } else {
                // If the product is not in the cart, insert it
                $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$userId, $productId, 1]); // Start with quantity 1
            }

            header('Location: cart.php'); // Redirect to cart page
            exit();
        } else {
            echo "Product not found. Please check the product ID.";
        }
    } else {
        header("Location: login.php"); // Redirect to the dashboard if no product ID is provided
    }
} else {
    echo "Invalid request method. Please use POST to add a product to the cart.";
}
?>