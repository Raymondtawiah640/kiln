<?php
session_start();
require 'db_connect.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Session timeout
$timeout_duration = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php");
    exit();
}
$_SESSION['last_activity'] = time();


// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $categoryName = trim($_POST['category_name']);
    if (!empty($categoryName)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$categoryName]);
        $successMessage = "Category added successfully!";
    } else {
        $errorMessage = "Category name cannot be empty.";
    }
}

// Handle Delete Category
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$deleteId]);
    $successMessage = "Category deleted successfully!";
}

// Fetch All Categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY id DESC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
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
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light-lavender);
            color: #333;
        }

        header {
            background-color: var(--royal-purple);
            color: white;
            padding: 15px 20px;
            text-align: center;
            font-size: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .container {
            padding: 20px;
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
            transition: opacity 0.5s ease-in-out;
        }

        .success {
            background-color: #2ecc71;
            color: white;
        }

        .error {
            background-color: #e74c3c;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
            font-size: 1rem;
        }

        table th {
            background-color: var(--royal-purple);
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .button {
            padding: 8px 14px;
            background-color: var(--medium-purple);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .button:hover {
            background-color: var(--blue-violet);
            transform: translateY(-1px);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .form-group button {
            padding: 10px 15px;
            background-color: var(--medium-purple);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .form-group button:hover {
            background-color: var(--blue-violet);
        }

        @media (max-width: 768px) {
            table th, table td {
                padding: 10px;
            }

            .container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<header>
    Manage Categories
</header>

<div class="container">
    <!-- Success/Error Messages -->
    <?php if (isset($successMessage)): ?>
        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    <?php if (isset($errorMessage)): ?>
        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <!-- Add Category Form -->
    <form method="POST" action="manage_categories.php">
        <div class="form-group">
            <label for="category_name">Add New Category:</label>
            <input type="text" id="category_name" name="category_name" placeholder="Enter category name" required>
        </div>
        <div class="form-group">
            <button type="submit" name="add_category">Add Category</button>
        </div>
    </form>

    <div style="margin-top: 20px;">
    <a href="admin_panel.php" class="button">Go Back to Dashboard</a>
</div><br>

    <!-- Categories Table -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="3" style="text-align: center;">No categories found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= htmlspecialchars($category['id']) ?></td>
                        <td><?= htmlspecialchars($category['name']) ?></td>
                        <td>
                            <a href="manage_categories.php?delete_id=<?= htmlspecialchars($category['id']) ?>" class="button" onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
