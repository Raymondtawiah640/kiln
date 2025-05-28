<?php
// Start session and include database connection
session_start();
require 'db_connect.php'; // Your database connection file
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files (ensure PHPMailer is included properly)
require 'vendor/autoload.php';  // If using Composer, otherwise include the PHPMailer files manually

// Check if the message ID is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $messageId = intval($_GET['id']);

    // Fetch the message details from the database using the ID
    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$messageId]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no message found, redirect to the messages page
    if (!$message) {
        header("Location: view_messages.php");
        exit;
    }

    // Handle the reply form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
        $replyMessage = trim($_POST['reply_message']);
        $userEmail = $message['email'];
        $subject = "Re: " . $message['subject']; // Correct the subject to include "Re:"
        $fromEmail = 'enterprisekiln@gmail.com';

        // Validate the reply message
        if (empty($replyMessage)) {
            $errorMessage = "Please enter a reply message.";
        } else {
            try {
                $mail = new PHPMailer(true);

                // SMTP configuration
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'enterprisekiln@gmail.com';
                $mail->Password = 'crsr vwdb almj xkxn';  // Your app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Set sender and recipient
                $mail->setFrom($fromEmail, 'Kiln Enterprise');
                $mail->addAddress($userEmail);

                // Embed logo
                $mail->AddEmbeddedImage('m1.png', 'kilnlogo');

                // Format the email as HTML
                $mail->isHTML(true);
                $sanitizedReply = nl2br(htmlspecialchars($replyMessage));
                $mail->Subject = $subject;
                $mail->Body = '
    <div style="font-family: Arial, sans-serif; color: #333; text-align: center;">
        <img src="cid:kilnlogo" alt="Kiln Enterprise Logo"
             style="width: 80px; height: 80px; border-radius: 50%; display: block; margin: 0 auto 20px auto;">
        <p style="text-align: left;">' . $sanitizedReply . '</p>
        <br><br>
        <p style="font-size: 12px; color: #999;">Kiln Team</p>
    </div>';

                $mail->AltBody = strip_tags($replyMessage);

                $mail->send();

                $successMessage = "Your reply has been sent successfully!";
            } catch (Exception $e) {
                $errorMessage = "There was an error sending the reply: {$mail->ErrorInfo}";
            }
        }
    }
} else {
    header("Location: view_messages.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Message</title>
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
            max-width: 800px;
            margin: 0 auto;
        }

        .message-details {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .message-details h3 {
            color: var(--blue-violet);
            margin-bottom: 10px;
        }

        .message-details p {
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            resize: vertical;
            height: 150px;
        }

        .form-group button {
            background-color: var(--royal-purple);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .form-group button:hover {
            background-color: var(--dark-orchid);
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .success {
            background-color: #2ecc71;
            color: white;
        }

        .error {
            background-color: #e74c3c;
            color: white;
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
    </style>
</head>
<body>

<header>
    <h1>Reply to Message</h1>
</header>

<div style="margin-top: 20px;">
    <a href="view_messages.php" class="button">Go Back to Dashboard</a>
</div>

<div class="container">
    <!-- Success/Error Messages -->
    <?php if (isset($successMessage)): ?>
        <div class="message success"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>
    <?php if (isset($errorMessage)): ?>
        <div class="message error"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>

    <!-- Message Details -->
    <div class="message-details">
        <h3>Message from: <?= htmlspecialchars($message['name']) ?></h3>
        <p><strong>Email:</strong> <?= htmlspecialchars($message['email']) ?></p>
        <p><strong>Subject:</strong> <?= htmlspecialchars($message['subject']) ?></p>
        <p><strong>Message:</strong> <?= nl2br(htmlspecialchars($message['message'])) ?></p>
        <p><strong>Submitted on:</strong> <?= htmlspecialchars($message['submitted_at']) ?></p>
    </div>

    <!-- Reply Form -->
    <form method="POST" action="reply_message.php?id=<?= $messageId ?>">
        <div class="form-group">
            <label for="reply_message">Your Reply:</label>
            <textarea id="reply_message" name="reply_message" placeholder="Type your reply here..."></textarea>
        </div>
        <div class="form-group">
            <button type="submit" name="reply">Send Reply</button>
        </div>
    </form>
</div>

</body>
</html>
