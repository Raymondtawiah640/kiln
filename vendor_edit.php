<?php
session_start();
require 'db_connect.php';

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Please login to access the vendor edit page.");
    exit();
}

$userId = $_SESSION['user_id'];
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;
$error_message = null;
$success_message = null;

// Fetch the product details for the logged-in user
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND user_id = ?");
    $stmt->execute([$productId, $userId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header("Location: vendor_dashboard.php?error=Product not found or unauthorized access.");
        exit();
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $error_message = "Error loading product details.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    $vendor_telephone = $_POST['vendor_telephone'];
    $vendor_username = $_POST['vendor_username'];
    $whatsapp_number = $_POST['whatsapp_number'];
    $vendor_landmark = $_POST['vendor_landmark'];

    try {
        $updateStmt = $pdo->prepare("UPDATE products SET 
            name = ?, 
            description = ?, 
            price = ?, 
            stock = ?, 
            category_id = ?,
            vendor_telephone = ?,
            vendor_username = ?,
            whatsapp_number = ?,
            vendor_landmark = ?
            WHERE id = ? AND user_id = ?");

        $updateStmt->execute([
            $name, 
            $description, 
            $price, 
            $stock, 
            $category_id,
            $vendor_telephone,
            $vendor_username,
            $whatsapp_number,
            $vendor_landmark,
            $productId, 
            $userId
        ]);

        $success_message = "Product updated successfully.";
        header("Location: vendor_dashboard.php?success=Product updated successfully.");
        exit();
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $error_message = "Error updating product.";
    }
}

// Fetch categories
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles/vendor_edit.css">
    <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon">
</head>
<body>

<div class="container">
<?php require 'navbar.php'; ?>

    <h1>
        <i class="fas fa-edit"></i> Edit Product
    </h1>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Product Name *</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" required><?= htmlspecialchars($product['description']) ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="price">Price (GH₵) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?= htmlspecialchars($product['price']) ?>" required>
            </div>

            <div class="form-group">
                <label for="stock">Stock Quantity *</label>
                <input type="number" id="stock" name="stock" min="0" value="<?= htmlspecialchars($product['stock']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>Current Image</label>
            <?php if (!empty($product['image'])): ?>
                <img src="<?= htmlspecialchars($product['image']) ?>" alt="Current Product Image" class="image-preview">
            <?php else: ?>
                <div style="padding: 1rem; background: var(--lavender); border-radius: 8px; text-align: center;">
                    <i class="fas fa-image" style="font-size: 2rem; color: var(--medium-purple);"></i>
                    <p>No image available</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="category_id">Category *</label>
            <select id="category_id" name="category_id" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= $product['category_id'] == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <h2 style="color: var(--deep-indigo); margin-top: 2rem; margin-bottom: 1rem; font-size: 1.3rem;">Vendor Information</h2>

        <div class="form-group">
            <label for="vendor_telephone">Telephone</label>
            <input type="text" id="vendor_telephone" name="vendor_telephone" value="<?= htmlspecialchars($product['vendor_telephone']) ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="vendor_username">Username</label>
                <input type="text" id="vendor_username" name="vendor_username" value="<?= htmlspecialchars($product['vendor_username']) ?>">
            </div>

            <div class="form-group">
                <label for="whatsapp_number">WhatsApp Number</label>
                <input type="text" id="whatsapp_number" name="whatsapp_number" value="<?= htmlspecialchars($product['whatsapp_number']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="vendor_landmark">Landmark</label>
            <input type="text" id="vendor_landmark" name="vendor_landmark" value="<?= htmlspecialchars($product['vendor_landmark']) ?>">
        </div>

        <button type="submit" class="btn">
            <i class="fas fa-save"></i> Update Product
        </button>

        <a href="vendor_dashboard.php" class="btn" style="background: linear-gradient(to right, #6c757d, #495057); margin-left: 1rem;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </form>
</div>

<?php require 'footer.php'; ?>
</body>
</html>
