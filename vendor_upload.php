<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db_connect.php';
require 'csrf.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: customer_login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$errors = [];
$success = false;

function getUploadError($errorCode) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
    ];
    return $errors[$errorCode] ?? 'Unknown upload error';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errors[] = "Invalid CSRF token.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $stock = intval($_POST['stock'] ?? 0);
        $vendor_country_code = trim($_POST['vendor_country_code'] ?? '');
        $whatsapp_country_code = trim($_POST['whatsapp_country_code'] ?? '');
        $vendor_telephone = $vendor_country_code . preg_replace('/\D/', '', $_POST['vendor_telephone'] ?? '');
        $vendor_username = trim($_POST['vendor_username'] ?? '');
        $whatsapp_number_raw = preg_replace('/\D/', '', $_POST['whatsapp_number'] ?? '');
        $whatsapp_number = $whatsapp_country_code . $whatsapp_number_raw;
        $vendor_landmark = trim($_POST['landmark'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $new_category = trim($_POST['new_category'] ?? '');

        // Validation
        if (empty($name)) $errors[] = "Product name is required.";
        if (empty($description)) $errors[] = "Description is required.";
        if ($price <= 0) $errors[] = "Price must be greater than 0.";
        if ($stock < 0) $errors[] = "Stock cannot be negative.";
        if (empty($vendor_telephone)) $errors[] = "Vendor telephone is required.";
        if (empty($vendor_username)) $errors[] = "Vendor username is required.";
        if (empty($vendor_landmark)) $errors[] = "Landmark is required.";

        // WhatsApp format validation
        if (empty($whatsapp_number_raw)) {
            $errors[] = "WhatsApp number is required.";
        } elseif (!preg_match('/^\+\d{11,13}$/', $whatsapp_number)) {
            $errors[] = "Invalid WhatsApp number format. Please include correct country code.";
        }

        if (empty($category_id) && !empty($new_category)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
                $stmt->execute([$new_category]);
                $category_id = $pdo->lastInsertId();
            } catch (PDOException $e) {
                $errors[] = "Failed to create new category: " . $e->getMessage();
            }
        } elseif (empty($category_id)) {
            $errors[] = "Please select a category or create a new one.";
        }

        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "Image upload error: " . getUploadError($_FILES['image']['error']);
            } else {
                $check = getimagesize($_FILES['image']['tmp_name']);
                if ($check === false) {
                    $errors[] = "Uploaded file is not a valid image.";
                } else {
                    $uploadDir = 'uploads/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $imageExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                    $validExts = ['jpg', 'jpeg', 'png', 'gif'];
                    if (!in_array($imageExt, $validExts)) {
                        $errors[] = "Only JPG, JPEG, PNG, and GIF images are allowed.";
                    } else {
                        $imageName = 'img_' . uniqid() . '.' . $imageExt;
                        $imagePath = $uploadDir . $imageName;
                        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                            $errors[] = "Failed to save image.";
                        }
                    }
                }
            }
        } else {
            $errors[] = "Product image is required.";
        }

        if (empty($errors)) {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO products 
                    (user_id, name, description, price, stock, image, vendor_telephone, vendor_username, whatsapp_number, vendor_landmark, category_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $userId, $name, $description, $price, $stock, $imagePath,
                    $vendor_telephone, $vendor_username, $whatsapp_number,
                    $vendor_landmark, $category_id
                ]);
                $pdo->commit();
                $success = true;
                header("Location: vendor_dashboard.php?success=Product uploaded successfully");
                exit();
            } catch (PDOException $e) {
                $pdo->rollBack();
                $errors[] = "Database error: " . $e->getMessage();
                if (!empty($imagePath) && file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
        }
    }
}

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$csrf_token = generateCSRFToken();
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Upload Product</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="stylesheet" href="styles/vendor_upload.css"/>
  <link rel="shortcut icon" href="logo.ico" sizes="96x96"  type="image/x-icon">
</head>
<body>

<?php require 'navbar.php'; ?>

<div class="container">
  <h1><i class="fas fa-upload"></i> Upload New Product</h1>

  <?php if ($success): ?>
    <div class="success">
      <i class="fas fa-check-circle"></i> Product uploaded successfully!
    </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="error">
      <i class="fas fa-exclamation-circle"></i> Please fix the following errors:
      <ul>
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form action="vendor_upload.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

    <!-- Product Name -->
    <div class="form-group">
      <label for="name">Product Name <span class="required-star">*</span></label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
    </div>

    <!-- Description -->
    <div class="form-group">
  <label for="description">Description <span class="required-star">*</span></label>
  <textarea id="description" name="description" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
  <button type="button" id="generate-description" class="btn" style="margin-top: 0.5rem;">
    <i class="fas fa-robot"></i> Generate Free Description
  </button>
  <p id="gen-status" style="font-style: italic; color: #666; margin-top: 0.5rem;"></p>
</div>


    <!-- Price & Stock -->
    <div class="form-row">
      <div class="form-group" style="flex: 1;">
        <label for="price">Price (GH₵) <span class="required-star">*</span></label>
        <input type="number" id="price" name="price" step="0.01" min="0" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
      </div>
      <div class="form-group" style="flex: 1;">
        <label for="stock">Stock Quantity <span class="required-star">*</span></label>
        <input type="number" id="stock" name="stock" min="0" value="<?= htmlspecialchars($_POST['stock'] ?? '') ?>" required>
      </div>
    </div>

    <!-- Image Upload -->
    <div class="form-group">
      <label>Product Image <span class="required-star">*</span></label>
      <div class="file-upload" onclick="document.getElementById('file-input').click()">
        <i class="fas fa-cloud-upload-alt"></i>
        <p>Click to upload product image</p>
        <p class="note">(JPG, PNG, or GIF)</p>
        <input type="file" id="file-input" name="image" accept="image/*" required>
        <img id="preview" class="preview-image" alt="Preview" style="display: none;">
      </div>
    </div>

    <!-- Category -->
    <div class="form-group">
      <label for="category_id">Category <span class="required-star">*</span></label>
      <select id="category_id" name="category_id">
        <option value="">-- Select Category --</option>
        <?php foreach ($categories as $category): ?>
          <option value="<?= $category['id'] ?>" <?= ($_POST['category_id'] ?? '') == $category['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($category['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <p>Or create new category:</p>
      <input type="text" name="new_category" id="new_category" placeholder="New category name" value="<?= htmlspecialchars($_POST['new_category'] ?? '') ?>" <?= empty($_POST['category_id']) ? '' : 'disabled' ?> disabled>
    </div>

    <!-- Vendor Section -->
    <h2>Vendor Information</h2>

    <!-- Telephone -->
    <div class="form-row">
      <div class="form-group" style="flex: 1;">
        <label for="vendor_telephone">Telephone <span class="required-star">*</span></label>
        <div style="display: flex; gap: 0.5rem;">
          <select name="vendor_country_code" style="flex: 0.5;">
            <option value="+233" <?= ($_POST['vendor_country_code'] ?? '') === '+233' ? 'selected' : '' ?>>🇬🇭 +233</option>
            <option value="+234" <?= ($_POST['vendor_country_code'] ?? '') === '+234' ? 'selected' : '' ?>>🇳🇬 +234</option>
            <option value="+254" <?= ($_POST['vendor_country_code'] ?? '') === '+254' ? 'selected' : '' ?>>🇰🇪 +254</option>
          </select>
          <input type="text" id="vendor_telephone" name="vendor_telephone" value="<?= htmlspecialchars($_POST['vendor_telephone'] ?? '') ?>" placeholder="XXXXXXXXX" style="flex: 1;" required>
        </div>
      </div>
    </div>

    <!-- Username & WhatsApp -->
    <div class="form-row">
      <div class="form-group" style="flex: 1;">
        <label for="vendor_username">Username <span class="required-star">*</span></label>
        <input type="text" id="vendor_username" name="vendor_username" value="<?= htmlspecialchars($_POST['vendor_username'] ?? '') ?>" required>
      </div>
      <div class="form-group" style="flex: 1;">
        <label for="whatsapp_number">WhatsApp Number <span class="required-star">*</span></label>
        <div style="display: flex; gap: 0.5rem;">
          <select name="whatsapp_country_code" style="flex: 0.5;">
            <option value="+233" <?= ($_POST['whatsapp_country_code'] ?? '') === '+233' ? 'selected' : '' ?>>🇬🇭 +233</option>
            <option value="+234" <?= ($_POST['whatsapp_country_code'] ?? '') === '+234' ? 'selected' : '' ?>>🇳🇬 +234</option>
            <option value="+254" <?= ($_POST['whatsapp_country_code'] ?? '') === '+254' ? 'selected' : '' ?>>🇰🇪 +254</option>
          </select>
          <input type="text" id="whatsapp_number" name="whatsapp_number" value="<?= htmlspecialchars($_POST['whatsapp_number'] ?? '') ?>" placeholder="XXXXXXXXX" style="flex: 1;" required>
        </div>
      </div>
    </div>

    <!-- Landmark -->
    <div class="form-row">
      <div class="form-group" style="flex: 1;">
        <label for="landmark">Nearby Landmark <span class="required-star">*</span></label>
        <input type="text" id="landmark" name="landmark" value="<?= htmlspecialchars($_POST['landmark'] ?? '') ?>" required>
      </div>
    </div>

    <!-- Map Preview (optional enhancement) -->
    <div id="map-preview" style="display: none;">
      <label>Map Preview</label>
      <iframe id="map-iframe" width="100%" height="300" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>

    <button type="submit" class="btn">
      <i class="fas fa-save"></i> Save Product
    </button>
  </form>
</div>

<?php require 'footer.php'; ?>

<!-- Image Preview Script -->
<script>

  document.getElementById('generate-description').addEventListener('click', async function() {
  const productName = document.getElementById('name').value.trim();
  const genStatus = document.getElementById('gen-status');

  if (!productName) {
    alert('Please enter the product name first.');
    return;
  }

  genStatus.textContent = 'Generating description... Please wait.';
  this.disabled = true;

  try {
    const response = await fetch('generate_description.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ product_name: productName })
    });

    if (!response.ok) throw new Error('Network response was not ok');

    const data = await response.json();

    if (data.success && data.description) {
      document.getElementById('description').value = data.description;
      genStatus.textContent = 'Description generated! You can edit it if you want.';
    } else {
      genStatus.textContent = 'Failed to generate description.';
    }
  } catch (error) {
    genStatus.textContent = 'Error: ' + error.message;
  }

  this.disabled = false;
});

document.getElementById('file-input').addEventListener('change', function(e) {
  const preview = document.getElementById('preview');
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
    }
    reader.readAsDataURL(file);
  }
});
</script>

<!-- Optional Dynamic Category Toggle -->
<script>
document.getElementById("category_id").addEventListener("change", function() {
  const newCategoryInput = document.getElementById("new_category");
  newCategoryInput.disabled = this.value !== "";
});
</script>

</body>
</html>

