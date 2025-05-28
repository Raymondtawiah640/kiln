<?php
// Start session and include database connection
session_start();
require 'db_connect.php'; // Your database connection file

// Fetch all messages from the database
$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY submitted_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Messages</title>
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
            padding: 20px;
            text-align: center;
        }

        .container {
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .message {
            background-color: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .message h3 {
            color: var(--blue-violet);
            margin-bottom: 10px;
        }

        .message p {
            margin-bottom: 5px;
        }

        .message .timestamp {
            font-size: 12px;
            color: gray;
        }

        .message .details {
            margin-top: 10px;
        }

        .message .details a {
            color: var(--royal-purple);
            text-decoration: none;
            font-weight: bold;
        }

        .message .details a:hover {
            color: var(--dark-orchid);
        }

             
        /* Button Style for "Go Back to Dashboard" */
      .button {
    display: inline-block;
    background-color: var(--royal-purple);
    color: white;
    padding: 12px 25px;
    font-size: 16px;
    font-weight: bold;
    border-radius: 6px;
    text-decoration: none;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: background-color 0.3s ease;
    margin-left: 20px;
     }


    .button:hover {
    background-color: var(--dark-orchid);
    }

        /* Responsive styles */
        @media (max-width: 768px) {
            header {
                padding: 15px;
            }

            .container {
                padding: 15px;
            }

            .message {
                padding: 12px;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>User Messages</h1>
</header>

<div style="margin-top: 20px;">
    <a href="admin_panel.php" class="button">Go Back to Dashboard</a>
</div>

<div class="container">
    <?php if (empty($messages)): ?>
        <p>No messages found.</p>
    <?php else: ?>
        <?php foreach ($messages as $message): ?>
            <div class="message">
                <h3>Message from: <?= htmlspecialchars($message['name']) ?></h3>
                <p><strong>Email:</strong> <?= htmlspecialchars($message['email']) ?></p>
                <p><strong>Subject:</strong> <?= htmlspecialchars($message['subject']) ?></p>
                <p><strong>Message:</strong></p>
                <p><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                <div class="timestamp">
                    <p>Submitted on: <?= htmlspecialchars($message['submitted_at']) ?></p>
                </div>
                <div class="details">
                    <a href="reply_message.php?id=<?= $message['id'] ?>">Reply to this message</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
