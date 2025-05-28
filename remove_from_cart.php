<?php
session_start();
require 'db_connect.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartId = $_POST['cart_id'];

    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
    if ($stmt->execute([$cartId])) {
        header('Location: cart.php'); // Redirect back to cart
        exit();
    } else {
        echo "Error removing item from cart.";
    }
}
?>