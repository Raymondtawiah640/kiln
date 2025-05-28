<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

$timeout_duration = 1800;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

$_SESSION['last_activity'] = time();

$query = "
    SELECT orders.order_id, orders.order_date, orders.status, users.username 
    FROM orders
    JOIN users ON orders.user_id = users.id
    ORDER BY orders.order_date DESC
";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error fetching orders: ' . $e->getMessage();
}

if (isset($_POST['mark_as_delivered'])) {
    $order_id = $_POST['order_id'];

    $update_query = "UPDATE orders SET status='Delivered' WHERE order_id = :order_id";
    try {
        $update_stmt = $pdo->prepare($update_query);
        $update_stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        if ($update_stmt->execute()) {
            $_SESSION['message'] = 'Order marked as delivered';
            header('Location: manage_orders.php');
            exit();
        } else {
            $_SESSION['error'] = 'Error updating order status.';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error updating order status: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
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

        h1 {
            margin: 0;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }

        h2 {
            color: var(--royal-purple);
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: var(--royal-purple);
            color: white;
        }

        td {
            background-color: var(--lavender);
        }

        td button {
            padding: 8px 15px;
            background-color: var(--royal-purple);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        td button:hover {
            background-color: var(--dark-orchid);
        }

        td span {
            padding: 8px 15px;
            background-color: var(--orchid);
            color: white;
            border-radius: 6px;
        }

        .message {
            color: green;
            font-size: 18px;
            margin-bottom: 20px;
        }

        .error {
            color: red;
            font-size: 18px;
            margin-bottom: 20px;
        }

        a {
            color: var(--royal-purple);
            text-decoration: none;
            font-size: 16px;
        }

        a:hover {
            text-decoration: underline;
        }

        footer {
            background-color: var(--deep-indigo);
            color: white;
            padding: 20px 0;
            text-align: center;
        }

        footer a,
        footer li,
        footer p,
        footer span {
            color: white !important;
        }

        footer a:hover {
            color: var(--orchid);
        }

        footer h3, footer h4, footer strong {
            color: white;
        }
    </style>
</head>
<body>

<header>
    <h1>Manage Orders</h1>
</header>

<main class="container">
    <h2>Orders List</h2>
    <a href="admin_panel.php">← Back to Dashboard</a>

    <?php if (isset($_SESSION['message'])): ?>
        <p class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td>
                        <?php if ($row['status'] !== 'Delivered'): ?>
                            <form method="POST" action="manage_orders.php">
                                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($row['order_id']); ?>">
                                <button type="submit" name="mark_as_delivered">Mark as Delivered</button>
                            </form>
                        <?php else: ?>
                            <span>Already Delivered</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php require 'footer.php'; ?>

</body>
</html>
