<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'db_connect.php';

// Handle status updates and notifications
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $return_id = filter_var($_POST['return_id'], FILTER_SANITIZE_NUMBER_INT);
    $status = htmlspecialchars($_POST['status']);
    $admin_notes = htmlspecialchars($_POST['admin_notes']);
    $admin_feedback = htmlspecialchars($_POST['admin_feedback']); // correct column name for feedback

    try {
        $pdo->beginTransaction();

        // 1. Fetch customer email, order info, and notification status
        $stmt = $pdo->prepare("SELECT u.email, r.order_id, r.notification_sent 
                               FROM returns r 
                               JOIN users u ON r.user_id = u.id 
                               WHERE r.return_id = :return_id");
        $stmt->execute([':return_id' => $return_id]);
        $return_info = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$return_info) {
            throw new Exception("Return not found.");
        }

        // 2. Update return record
        $stmt = $pdo->prepare("UPDATE returns SET 
                                status = :status,
                                admin_notes = :admin_notes,
                                admin_feedback = :admin_feedback,
                                processed_at = IF(:status = 'processed', NOW(), processed_at), 
                                updated_at = NOW()
                                WHERE return_id = :return_id");
        $stmt->execute([
            ':status' => $status,
            ':admin_notes' => $admin_notes,
            ':admin_feedback' => $admin_feedback,
            ':return_id' => $return_id
        ]);

        // 3. Send email notification if not already sent
        if ($return_info['notification_sent'] == 0 && $return_info['email']) {
            $to = $return_info['email'];
            $subject = "Your Return #$return_id Status Update";

            $message = "
            <html>
            <head>
                <title>Return Status Update</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #4b0082; color: white; padding: 10px; text-align: center; }
                    .content { padding: 20px; }
                    .footer { margin-top: 20px; font-size: 0.9em; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Return Status Update</h2>
                    </div>
                    <div class='content'>
                        <p>Dear Customer,</p>
                        <p>The status of your return <strong>#$return_id</strong> for order <strong>#{$return_info['order_id']}</strong> has been updated:</p>
                        <p><strong>New Status:</strong> ".ucfirst($status)."</p>
                        <p><strong>Our Response:</strong></p>
                        <p>".nl2br($admin_feedback)."</p>
                        <p>If you have any questions, please reply to this email.</p>
                    </div>
                    <div class='footer'>
                        <p>Thank you for shopping with us,</p>
                        <p>Customer Service Team</p>
                    </div>
                </div>
            </body>
            </html>
            ";

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: returns@yourstore.com\r\n";
            $headers .= "Reply-To: noreply@yourstore.com\r\n";

            if (mail($to, $subject, $message, $headers)) {
                // Mark notification as sent and set notification date
                $stmt = $pdo->prepare("UPDATE returns SET notification_sent = 1, notification_date = NOW() WHERE return_id = :return_id");
                $stmt->execute([':return_id' => $return_id]);
            }
        }

        $pdo->commit();
        
        // Instead of redirecting, show success message in the modal
        echo "<script>
            window.onload = function() { 
                showPopup('Return #$return_id updated and customer notified!');
            }
        </script>";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error processing return: " . $e->getMessage();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<style>
    /* Modal Styles */
    .modal {
        display: none; /* Hidden by default */
        position: fixed;
        z-index: 1; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }

    .modal-content {
        background-color: #fff;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%; /* Could be more specific */
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
</style>

<!-- Success Message Modal -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closePopup()">&times;</span>
        <p id="successMessage"></p>
    </div>
</div>

<script>
    function showPopup(message) {
        document.getElementById('successMessage').textContent = message;
        document.getElementById('successModal').style.display = 'block';
    }

    function closePopup() {
        document.getElementById('successModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('successModal');
        if (event.target === modal) {
            closePopup();
        }
    }
</script>
