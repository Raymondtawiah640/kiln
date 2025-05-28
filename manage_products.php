<?php
session_start();
require 'db_connect.php';
require 'csrf.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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


$userId = $_SESSION['user_id'];
$successMessage = null;
$errorMessage = null;

// Handle Add Product (still exists in case you re-enable the form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errorMessage = "Invalid CSRF token.";
    } else {
        $productName = trim($_POST['product_name']);
        $productDescription = trim($_POST['product_description']);
        $productPrice = floatval($_POST['product_price']);
        $productStock = intval($_POST['product_stock']);
        $productCategory = intval($_POST['product_category']);

        $productImage = null;
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            $imageTmpPath = $_FILES['product_image']['tmp_name'];
            $imageExtension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));

            if (in_array($imageExtension, $allowedExtensions)) {
                $imageName = uniqid('product_', true) . '.' . $imageExtension;
                $uploadPath = 'uploads/' . $imageName;

                if (move_uploaded_file($imageTmpPath, $uploadPath)) {
                    $productImage = $imageName;
                } else {
                    $errorMessage = "Failed to upload the image.";
                }
            } else {
                $errorMessage = "Invalid image format.";
            }
        } else {
            $errorMessage = "Product image is required.";
        }

        if (!$errorMessage && $productName && $productPrice > 0 && $productStock >= 0 && $productImage) {
            $stmt = $pdo->prepare("INSERT INTO products (user_id, name, description, price, stock, category_id, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $productName, $productDescription, $productPrice, $productStock, $productCategory, $productImage]);
            $successMessage = "Product added successfully!";
        } else {
            $errorMessage = $errorMessage ?: "Please fill all fields correctly.";
        }
    }
}

// Handle Delete Product
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
    $stmt->execute([$deleteId, $userId]);
    $successMessage = "Product deleted successfully!";
}

// Fetch Products
$stmt = $pdo->prepare("
    SELECT p.id, p.name, p.description, p.price, p.stock, 
           COALESCE(c.name, 'Uncategorized') AS category_name 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.user_id = ?
    ORDER BY p.id DESC
");
$stmt->execute([$userId]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Categories (still used in backend logic)
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// CSRF Token
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Manage Products</title>
  <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
      background: var(--light-lavender);
      margin: 0;
      padding: 0;
    }

    header {
      background: var(--royal-purple);
      color: white;
      padding: 20px;
      text-align: center;
      font-size: 1.5rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .container {
      max-width: 1100px;
      margin: auto;
      padding: 20px;
      background: #fff;
      border-radius: 10px;
      margin-top: 30px;
      box-shadow: 0 5px 15px rgba(106,13,173,0.1);
      overflow-x: auto;
    }

    .message {
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 6px;
      font-weight: bold;
      text-align: center;
      transition: opacity 0.5s ease-in-out;
    }

    .success { background: var(--soft-violet); color: #fff; }
    .error { background: var(--orchid); color: #fff; }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 900px;
    }

    th, td {
      padding: 14px;
      border: 1px solid var(--lavender);
      text-align: left;
    }

    th {
      background: var(--royal-purple);
      color: white;
      text-transform: uppercase;
      font-size: 0.9rem;
    }

    tr:nth-child(even) {
      background: #f9f9f9;
    }

    .button-link {
      background: var(--dark-orchid);
      color: white;
      padding: 8px 14px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 0.9rem;
      display: inline-block;
      transition: background 0.3s ease, transform 0.2s ease;
    }

    .button-link:hover {
      background: var(--blue-violet);
      transform: translateY(-1px);
    }

    .back-button {
      background: var(--medium-purple);
      color: white;
      padding: 10px 16px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 1rem;
      display: inline-block;
      margin-top: 20px;
      transition: background 0.3s ease, transform 0.2s ease;
    }

    .back-button:hover {
      background: var(--blue-violet);
      transform: translateY(-1px);
    }

    @media (max-width: 768px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }

      thead tr {
        display: none;
      }

      td {
        position: relative;
        padding-left: 50%;
      }

      td::before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        top: 12px;
        font-weight: bold;
        white-space: nowrap;
      }
    }
  </style>
</head>
<body>

<header>
  Manage Your Products
</header>

<div class="container">
  <?php if ($successMessage): ?>
    <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
  <?php endif; ?>

  <?php if ($errorMessage): ?>
    <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Description</th>
        <th>Price (GHS)</th>
        <th>Stock</th>
        <th>Category</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$products): ?>
        <tr><td colspan="7" style="text-align: center;">No products found.</td></tr>
      <?php else: ?>
        <?php foreach ($products as $prod): ?>
          <tr>
            <td data-label="ID"><?= $prod['id'] ?></td>
            <td data-label="Name"><?= htmlspecialchars($prod['name']) ?></td>
            <td data-label="Description"><?= htmlspecialchars($prod['description']) ?></td>
            <td data-label="Price">GH₵<?= number_format($prod['price'], 2) ?></td>
            <td data-label="Stock"><?= $prod['stock'] ?></td>
            <td data-label="Category"><?= htmlspecialchars($prod['category_name']) ?></td>
            <td data-label="Action">
              <a href="?delete_id=<?= $prod['id'] ?>" class="button-link" onclick="return confirm('Delete this product?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Back to Dashboard Button -->
  <a href="admin_panel.php" class="back-button">Back to Dashboard</a>
</div>
   
<script>
  setTimeout(() => {
    document.querySelectorAll('.message').forEach(msg => {
      msg.style.opacity = '0';
      setTimeout(() => msg.remove(), 500);
    });
  }, 2500);
</script>
</body>

</html>
