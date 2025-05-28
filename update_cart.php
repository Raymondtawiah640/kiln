<?php
session_start();
require 'db_connect.php';

// Enable error reporting for development (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Proceed only if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartId = isset($_POST['cart_id']) ? intval($_POST['cart_id']) : 0;
    $action = $_POST['action'] ?? null;

    // Fetch the cart item and product stock
    $stmt = $pdo->prepare("
        SELECT c.quantity, p.stock
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.id = ? AND c.user_id = ?
    ");
    $stmt->execute([$cartId, $_SESSION['user_id']]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        header("Location: cart.php?error=Item not found or access denied.");
        exit();
    }

    $currentQty = (int)$item['quantity'];
    $stock = (int)$item['stock'];

    if ($action === 'increment') {
        if ($currentQty < $stock) {
            $newQty = $currentQty + 1;
        } else {
            header("Location: cart.php?error=Maximum stock reached.");
            exit();
        }
    } elseif ($action === 'decrement') {
        if ($currentQty > 1) {
            $newQty = $currentQty - 1;
        } else {
            header("Location: cart.php?error=Minimum quantity is 1.");
            exit();
        }
    } else {
        header("Location: cart.php?error=Invalid action.");
        exit();
    }

    // Update the cart quantity
    try {
        $update = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $update->execute([$newQty, $cartId, $_SESSION['user_id']]);
        header("Location: cart.php?success=Cart updated.");
        exit();
    } catch (PDOException $e) {
        error_log("DB Error: " . $e->getMessage());
        header("Location: cart.php?error=Failed to update cart.");
        exit();
    }
}

// Redirect if script accessed via GET
header("Location: cart.php");
exit();
