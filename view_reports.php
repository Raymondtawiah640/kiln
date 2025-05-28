<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit();
}

require 'db_connect.php';

// Session timeout
$timeout_duration = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php");
    exit();
}
$_SESSION['last_activity'] = time();

// Fetch order statistics
try {
    $totalOrdersStmt = $pdo->query("SELECT COUNT(*) FROM orders");
    $totalOrders = $totalOrdersStmt->fetchColumn();

    $deliveredStmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Delivered'");
    $deliveredOrders = $deliveredStmt->fetchColumn();

    $pendingStmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status != 'Delivered' AND status != 'Confirmed'");
    $pendingOrders = $pendingStmt->fetchColumn();

    // Fetch confirmed orders
    $confirmedStmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Confirmed'");
    $confirmedOrders = $confirmedStmt->fetchColumn();

    // Fetch total returns
    $totalReturnsStmt = $pdo->query("SELECT COUNT(*) FROM returns");
    $totalReturns = $totalReturnsStmt->fetchColumn();

    $recentOrdersStmt = $pdo->query("
        SELECT orders.order_id, users.username, orders.order_date, orders.status
        FROM orders
        JOIN users ON orders.user_id = users.id
        ORDER BY orders.order_date DESC
        LIMIT 10
    ");
    $recentOrders = $recentOrdersStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching reports: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Reports</title>
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
            background-color: var(--light-lavender);
            color: var(--deep-indigo);
            margin: 0;
            padding: 0;
        }

        header {
            background-color: var(--royal-purple);
            color: white;
            text-align: center;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        h1, h2 {
            color: var(--royal-purple);
        }

        .stats {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            flex: 1;
            min-width: 200px;
            background-color: var(--lavender);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        .card h3 {
            color: var(--blue-violet);
            margin: 0 0 10px;
        }

        .card p {
            font-size: 24px;
            margin: 0;
            color: var(--deep-indigo);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--lavender);
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: var(--royal-purple);
            color: white;
        }

        td span {
            padding: 6px 12px;
            border-radius: 4px;
            background-color: var(--soft-violet);
            color: white;
        }

        .footer-link {
            margin-top: 30px;
            text-align: center;
        }

        a {
            color: var(--royal-purple);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .stats {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>Order Reports</h1>
</header>

<div class="footer-link">
        <a href="admin_panel.php">← Back to Dashboard</a>
    </div>

<main class="container">
    <h2>Summary</h2>
    <div class="stats">
        <div class="card">
            <h3>Total Orders</h3>
            <p><?php echo $totalOrders; ?></p>
        </div>
        <div class="card">
            <h3>Delivered</h3>
            <p><?php echo $deliveredOrders; ?></p>
        </div>
        <div class="card">
            <h3>Pending</h3>
            <p><?php echo $pendingOrders; ?></p>
        </div>
        <div class="card">
            <h3>Confirmed</h3>
            <p><?php echo $confirmedOrders; ?></p>
        </div>
        <!-- New card for Total Returns -->
        <div class="card">
            <h3>Total Returns</h3>
            <p><?php echo $totalReturns; ?></p>
        </div>
    </div>

    <h2>Recent Orders</h2>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($recentOrders)): ?>
            <?php foreach ($recentOrders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                    <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                    <td>
                        <span style="background-color: <?php echo ($order['status'] === 'Delivered') ? 'var(--blue-violet)' : 'var(--orchid)'; ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="4">No recent orders found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

</main>

<?php require 'footer.php'; ?>

</body>
</html>
