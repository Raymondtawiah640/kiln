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


// Handle user approval or rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_user_id'])) {
        $userId = intval($_POST['approve_user_id']);
        $stmt = $pdo->prepare("UPDATE users SET is_approved = 1 WHERE id = ?");
        $stmt->execute([$userId]);
    } elseif (isset($_POST['reject_user_id'])) {
        $userId = intval($_POST['reject_user_id']);
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
    }
}

// Fetch all pending users
$stmt = $pdo->query("SELECT id, username, email, role, is_approved FROM users WHERE is_approved = 0 ORDER BY role, username");
$pendingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Manage Users</title>
  <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-m1lwZbEfBj5WYOu..." crossorigin="anonymous" referrerpolicy="no-referrer" />

  <style>
    :root {
      --deep-indigo: #4b0082;
      --royal-purple: #6a0dad;
      --soft-violet: #9370db;
      --lavender: #e6e6fa;
      --light-lavender: #f8f5ff;
      --dark-orchid: #9932cc;
      --blue-violet: #8a2be2;
    }

    * {
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: var(--light-lavender);
      color: var(--deep-indigo);
    }

    body {
      padding-top: 80px;
      margin-left: 280px;
      transition: margin-left 0.3s ease;
    }

    @media (max-width: 992px) {
      body {
        margin-left: 0;
        padding-top: 100px;
      }
    }

    header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      background: var(--royal-purple);
      color: #fff;
      padding: 20px;
      text-align: center;
      z-index: 1000;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .container {
      max-width: 1200px;
      margin: auto;
      padding: 20px;
    }

    h1 {
      margin: 0;
      font-size: 1.75rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 6px 18px rgba(75,0,130,0.1);
      padding: 2rem;
      margin-bottom: 2rem;
      overflow-x: auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 700px;
    }

    table th, table td {
      padding: 12px 15px;
      border: 1px solid var(--lavender);
      text-align: left;
    }

    table th {
      background: var(--blue-violet);
      color: #fff;
      text-transform: uppercase;
      font-size: 0.95rem;
    }

    table tr:nth-child(even) {
      background: var(--light-lavender);
    }

    .button {
      padding: 8px 14px;
      margin: 2px;
      background: var(--royal-purple);
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 0.9rem;
      cursor: pointer;
      transition: background 0.3s ease, transform 0.2s ease;
    }

    .button:hover {
      background: var(--dark-orchid);
      transform: translateY(-2px);
    }

    .button.danger {
      background: #dc3545;
    }

    .button.danger:hover {
      background: #bd2130;
    }

    .back-link {
      display: inline-block;
      margin-top: 20px;
      text-decoration: none;
    }

    footer {
      background: var(--royal-purple);
      color: #fff;
      text-align: center;
      padding: 15px;
      position: relative;
      bottom: 0;
      width: 100%;
    }
  </style>
</head>
<body>
  <header>
    <h1><i class="fas fa-users-cog"></i> Manage Pending Users</h1>
  </header>

  <div class="container">
    <div class="card">
      <?php if (empty($pendingUsers)): ?>
        <p>No pending users at the moment.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Email</th>
              <th>Role</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($pendingUsers as $user): ?>
              <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= htmlspecialchars($user['role']) ?></td>
                <td>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="approve_user_id" value="<?= $user['id'] ?>">
                    <button type="submit" class="button">Approve</button>
                  </form>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="reject_user_id" value="<?= $user['id'] ?>">
                    <button type="submit" class="button danger">Reject</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <a href="admin_panel.php" class="button back-link">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
      </a>
    </div>
  </div>
</body>
</html>
