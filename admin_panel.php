<?php
session_start();
require 'db_connect.php'; // PDO connection file

// Session timeout handling
$timeout_duration = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php");
    exit();
}
$_SESSION['last_activity'] = time();

// Initialize counts
$pending_users = $pending_products = $pending_orders = $return_requests = $unread_messages = 0;

try {
    // Count pending users
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM users WHERE status = ?");
    $stmt->execute(['pending']);
    $pending_users = $stmt->fetchColumn();

    // Count pending products
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM products WHERE status = ?");
    $stmt->execute(['pending']);
    $pending_products = $stmt->fetchColumn();

    // Count pending orders
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM orders WHERE status = ?");
    $stmt->execute(['pending']);
    $pending_orders = $stmt->fetchColumn();

    // Count return requests
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM returns WHERE status = ?");
    $stmt->execute(['requested']);
    $return_requests = $stmt->fetchColumn();

    // Count unread messages
    $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM messages WHERE is_read = 0");
    $stmt->execute();
    $unread_messages = $stmt->fetchColumn();
} catch (PDOException $e) {
    // Handle error gracefully (logging recommended in production)
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
    }

    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        background-color: var(--light-lavender);
        color: var(--deep-indigo);
        display: flex;
        flex-direction: column;
    }

    header {
        background-color: var(--royal-purple);
        color: white;
        padding: 20px;
        text-align: center;
    }

    .container {
        flex: 1;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        padding: 20px;
        max-width: 1200px;
        margin: auto;
    }

    .card {
        background-color: white;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        padding: 25px;
        text-align: center;
        position: relative;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
    }

    .card h3 {
        margin-bottom: 10px;
        color: var(--blue-violet);
    }

    .card p {
        margin-bottom: 15px;
        color: var(--deep-indigo);
    }

    .button {
        display: inline-block;
        padding: 10px 20px;
        background-color: var(--royal-purple);
        color: white;
        text-decoration: none;
        border-radius: 6px;
        transition: background-color 0.3s ease;
    }

    .button:hover {
        background-color: var(--dark-orchid);
    }

    .badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background-color: var(--orchid);
        color: white;
        font-weight: bold;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        box-shadow: 0 0 0 2px white;
    }

    @media (max-width: 768px) {
        .container {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>
<body>

<header>
    <h1>Admin Dashboard</h1>
</header>

<div class="container">
    <div class="card">
        <h3>User Management</h3>
        <p>Manage customers, vendors, and admin accounts.</p>
        <a href="manage_users.php" class="button">Manage Users</a>
        <div class="badge" title="Pending Users"><?php echo $pending_users; ?></div>
    </div>

    <div class="card">
        <h3>Product Management</h3>
        <p>Approve, edit, or delete products submitted by vendors.</p>
        <a href="manage_products.php" class="button">Manage Products</a>
        <div class="badge" title="Pending Products"><?php echo $pending_products; ?></div>
    </div>

    <div class="card">
        <h3>Order Management</h3>
        <p>View and manage customer orders, refunds, and disputes.</p>
        <a href="manage_orders.php" class="button">Manage Orders</a>
        <div class="badge" title="Pending Orders"><?php echo $pending_orders; ?></div>
    </div>

    <div class="card">
        <h3>Category Management</h3>
        <p>Add, edit, or delete product categories.</p>
        <a href="manage_categories.php" class="button">Manage Categories</a>
    </div>

    <div class="card">
        <h3>Returns Management</h3>
        <p>Process return requests, issue refunds, and track returned items.</p>
        <a href="manage_returns.php" class="button">Manage Returns</a>
        <div class="badge" title="Return Requests"><?php echo $return_requests; ?></div>
    </div>

    <div class="card">
        <h3>User Messages</h3>
        <p>View messages sent by users regarding inquiries or support.</p>
        <a href="view_messages.php" class="button">View Messages</a>
        <div class="badge" title="Unread Messages"><?php echo $unread_messages; ?></div>
    </div>

    <div class="card">
        <h3>Reports and Analytics</h3>
        <p>View sales reports, user activity, and product performance.</p>
        <a href="view_reports.php" class="button">View Reports</a>
    </div>

    <div class="card">
        <h3>Logout</h3>
        <p>End your admin session securely.</p>
        <a href="admin_logout.php" class="button">Logout</a>
    </div>
</div>

<?php require 'footer.php'; ?>

</body>
</html>
