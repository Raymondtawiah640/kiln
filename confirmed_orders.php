<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view your order.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch latest order for the user
$stmt_latest = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt_latest->execute([$user_id]);
$order = $stmt_latest->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    echo "No recent order found.";
    exit();
}

$order_id = $order['order_id'];

// Fetch user info
$stmt_user = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Fetch order items with product and vendor info
$stmt_items = $pdo->prepare("
    SELECT 
        oi.order_id,
        oi.quantity, 
        oi.price, 
        p.name AS product_name, 
        p.image, 
        p.vendor_username, 
        p.vendor_telephone, 
        p.vendor_landmark
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt_items->execute([$order_id]);
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation - <?= htmlspecialchars($order_id) ?></title>
    <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0; padding: 20px;
        }
        .container {
            background: #fff;
            padding: 30px;
            margin: auto;
            border-radius: 10px;
            max-width: 1000px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2, h3 { color: #6a0dad; }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }
        img.product-image {
            height: 60px;
            max-width: 80px;
            object-fit: contain;
        }
        .footer {
            margin-top: 20px;
            color: #555;
            font-size: 14px;
        }
        button#downloadBtn {
            background: #6a0dad;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 15px;
            font-size: 16px;
        }
        button#downloadBtn:hover {
            background: #570aaf;
        }
    </style>
</head>
<body>

<div class="container" id="orderContent">
    <h2>Order Confirmation - <?= htmlspecialchars($order_id) ?></h2>
    <p><strong>Customer:</strong> <?= htmlspecialchars($user['username']) ?></p>
    <p><strong>Total:</strong> GH₵ <?= number_format($order['total'], 2) ?></p>
    <p><strong>Delivery Fee:</strong> GH₵ <?= number_format($order['shipping_fee'], 2) ?></p>
    <p><strong>Payment Method:</strong> <?= ucfirst(htmlspecialchars($order['payment_method'])) ?></p>
    <p><strong>Pickup Location:</strong> Koforidua Technical University</p>
    <p><strong>Order Date:</strong> <?= date('F j, Y, g:i A', strtotime($order['created_at'])) ?></p>

    <h3>Items Ordered</h3>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Product Image</th>
                <th>Name</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
                <th>Vendor Name</th>
                <th>Vendor Phone</th>
                <th>Landmark</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['order_id']) ?></td>
                <td>
                    <?php if (!empty($item['image'])): ?>
                        <img 
                            src="<?= htmlspecialchars($item['image']) ?>" 
                            alt="<?= htmlspecialchars($item['product_name']) ?>" 
                            class="product-image"
                            loading="lazy"
                            onerror="this.outerHTML = `<div style='color: red; font-weight: bold;'><?= htmlspecialchars($item['product_name']) ?> (image not found)</div>`;"
                        >
                    <?php else: ?>
                        <div style="color: #a00;">No Image Available</div>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= (int)$item['quantity'] ?></td>
                <td>GH₵ <?= number_format($item['price'], 2) ?></td>
                <td>GH₵ <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                <td><?= htmlspecialchars($item['vendor_username']) ?></td>
                <td><?= htmlspecialchars($item['vendor_telephone']) ?></td>
                <td><?= htmlspecialchars($item['vendor_landmark']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <button id="downloadBtn">Download Confirmation</button>

    <div class="footer">
        <p>If the confirmation email didn’t reach you, your order is safely recorded here. Thank you for choosing Kiln Enterprise!</p>
    </div>
</div>

<!-- jsPDF CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    const downloadBtn = document.getElementById('downloadBtn');
    downloadBtn.addEventListener('click', () => {
        const { jsPDF } = window.jspdf;

        html2canvas(document.getElementById('orderContent')).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'pt', 'a4');
            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = pdf.internal.pageSize.getWidth();

            const scale = 0.8;
            const scaledWidth = pdfWidth * scale;
            const scaledHeight = (imgProps.height * scaledWidth) / imgProps.width;

            pdf.addImage(imgData, 'PNG', 0, 0, scaledWidth, scaledHeight);
            pdf.save('Order_Confirmation_<?= htmlspecialchars($order_id) ?>.pdf');

            alert('Your order confirmation has been downloaded. Please bring this confirmation when you come to pick up your product.');
        });
    });
</script>
<script>
    document.addEventListener('keydown', function (event) {
        if (event.ctrlKey && event.key.toLowerCase() === 'p') {
            event.preventDefault(); // Prevent browser's default print dialog
            downloadBtn.click();    // Trigger the download
        }
    });
</script>


</body>
</html>
