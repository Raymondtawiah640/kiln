<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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


require 'db_connect.php';
require 'vendor/autoload.php'; // PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Handle status updates and notifications
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $return_id = filter_var($_POST['return_id'], FILTER_SANITIZE_NUMBER_INT);
    $status = htmlspecialchars($_POST['status']);
    $admin_notes = htmlspecialchars($_POST['admin_notes']);
    $admin_feedback = htmlspecialchars($_POST['admin_feedback']);

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
// Inside the email template
$message = "<html>
<head>
    <title>Return Status Update</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .header { background-color: #4b0082; color: white; padding: 15px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; }
        .footer { margin-top: 30px; font-size: 0.9em; color: #666; text-align: center; }
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
            <p><strong>New Status:</strong> " . ucfirst($status) . "</p>
            <p><strong>Our Response:</strong></p>
            <p>" . nl2br(htmlspecialchars($admin_feedback, ENT_QUOTES, 'UTF-8')) . "</p>
            <p><strong>Email Sent On:</strong> {$sent_date}</p>
            <p>If you have any questions, please reply to this email.</p>
        </div>
        <div class='footer'>
            <p>Thank you for shopping with us,</p>
            <p>Customer Service Team</p>
        </div>
    </div>
</body>
</html>";


            // PHPMailer setup
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'enterprisekiln@gmail.com'; // Use the email from which you want to send
            $mail->Password = 'rszr pbcc juxu tenc';     // Ensure this is securely stored, possibly in an environment file
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Sender info
            $mail->setFrom('enterprisekiln@gmail.com', 'Kiln Enterprise');  // From email and name

            // Recipient info
            $mail->addAddress($to); // Recipient email

            $mail->AddEmbeddedImage('logo1.png', 'logoimg');

            // Email content settings
            $mail->isHTML(true);  // Set the email format to HTML
            $mail->Subject = $subject; // Subject line
            $mail->Body = $message; // HTML message content

            if ($mail->send()) {
                // Mark notification as sent and set notification date
                $stmt = $pdo->prepare("UPDATE returns SET notification_sent = 1, notification_date = NOW() WHERE return_id = :return_id");
                $stmt->execute([':return_id' => $return_id]);
            } else {
                throw new Exception("Failed to send email: " . $mail->ErrorInfo);
            }
        }

        $pdo->commit();
        $_SESSION['flash_message'] = "Return #$return_id updated and customer notified!";
        echo json_encode(['success' => true, 'message' => $_SESSION['flash_message']]);
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error processing return: " . $e->getMessage();
        echo json_encode(['success' => false, 'message' => $error]);
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
        echo json_encode(['success' => false, 'message' => $error]);
        exit;
    }
}

// Get all returns with optional filtering
$status_filter = $_GET['status'] ?? 'pending';
$search_query = $_GET['search'] ?? '';

$query = "SELECT r.*, u.username, u.email AS user_email 
          FROM returns r 
          LEFT JOIN users u ON r.user_id = u.id";

$params = [];
$conditions = [];

if (!empty($status_filter)) {
    $conditions[] = "r.status = :status";
    $params[':status'] = $status_filter;
}

if (!empty($search_query)) {
    $conditions[] = "(r.order_id LIKE :search OR u.username LIKE :search OR u.email LIKE :search)";
    $params[':search'] = "%$search_query%";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY r.created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $returns = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Returns Management</title>
    <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon">
    <link rel="stylesheet" href="styles/manage_returns.css">
</head>
<body>
    <header>
        <div class="header-content">
            <h1>Returns Management</h1>
            <a href="http://localhost/kilnmart/admin_panel.php" class="btn btn-outline">Back to Dashboard</a>
        </div>
    </header>

    <div class="container">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="flash-message">
                <?= $_SESSION['flash_message']; ?>
                <?php unset($_SESSION['flash_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="flash-message" style="background-color: var(--danger);">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="filter-section">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="status">Filter by Status</label>
                    <select id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $status_filter === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= $status_filter === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        <option value="processed" <?= $status_filter === 'processed' ? 'selected' : '' ?>>Processed</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="search">Search</label>
                    <input type="text" id="search" name="search" placeholder="Order ID, Username or Email" value="<?= htmlspecialchars($search_query) ?>">
                </div>
                <div class="filter-group" style="align-self: flex-end;">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="admin_returns.php" class="btn btn-outline">Reset</a>
                </div>
            </form>
        </div>

        <table class="returns-table">
            <thead>
                <tr>
                    <th>Return ID</th>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($returns)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No returns found</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($returns as $return): ?>
                        <tr>
                            <td><?= $return['return_id'] ?></td>
                            <td><?= htmlspecialchars($return['order_id']) ?></td>
                            <td>
                                <?= htmlspecialchars($return['username']) ?><br>
                                <small><?= htmlspecialchars($return['user_email']) ?></small>
                            </td>
                            <td><?= ucwords(str_replace('-', ' ', $return['reason'])) ?></td>
                            <td class="status-<?= $return['status'] ?>"><?= ucfirst($return['status']) ?></td>
                            <td><?= date('M j, Y', strtotime($return['created_at'])) ?></td>
                            <td>
                                <button onclick="openModal(
                                    <?= $return['return_id'] ?>, 
                                    '<?= $return['status'] ?>',
                                    `<?= str_replace('`', '\`', $return['details']) ?>` ,
                                    `<?= str_replace('`', '\`', $return['admin_notes'] ?? '') ?>` ,
                                    `<?= str_replace('`', '\`', $return['admin_feedback'] ?? '') ?>`
                                )" class="btn btn-primary">
                                    Manage
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Modal for managing returns -->
        <div id="returnModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Manage Return #<span id="modalReturnId"></span></h2>
                <form id="returnStatusForm">
                    <input type="hidden" name="return_id" id="formReturnId">
                    <input type="hidden" name="update_status" value="1">
                    
                    <div class="form-group">
                        <label><strong>Current Status:</strong> <span id="currentStatus" class="status-pending"></span></label>
                    </div>
                    
                    <div class="form-group">
                        <label for="status"><strong>Update Status:</strong></label>
                        <select name="status" id="modalStatus" required>
                            <option value="approved">Approve Return</option>
                            <option value="rejected">Reject Return</option>
                            <option value="processed">Mark as Processed</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_notes"><strong>Internal Notes (Admin Only):</strong></label>
                        <textarea name="admin_notes" id="modalAdminNotes" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_feedback"><strong>Admin Feedback Message:</strong></label>
                        <textarea name="admin_feedback" id="modalAdminFeedback" rows="4" required
                                  placeholder="Explain the decision to the customer..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Status & Notify Customer</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Open modal and populate fields
    function openModal(returnId, currentStatus, details, adminNotes, adminFeedback) {
        document.getElementById('modalReturnId').textContent = returnId;
        document.getElementById('formReturnId').value = returnId;
        
        const statusElement = document.getElementById('currentStatus');
        statusElement.textContent = currentStatus;
        statusElement.className = 'status-' + currentStatus;
        
        document.getElementById('modalStatus').value = currentStatus === 'pending' ? 'approved' : currentStatus;
        document.getElementById('modalAdminNotes').value = adminNotes || '';
        document.getElementById('modalAdminFeedback').value = adminFeedback || '';  // Added this line for feedback
        
        document.getElementById('returnModal').style.display = 'block';  // Ensure modal shows up
    }

    // Close modal
    function closeModal() {
        document.getElementById('returnModal').style.display = 'none';
    }

    // Handle form submission for updating status
    document.getElementById('returnStatusForm').addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent form from submitting the default way

        const form = new FormData(this);
        
        fetch('manage_returns.php', {
            method: 'POST',
            body: form,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                closeModal(); // Close the modal after success
                location.reload(); // Reload the page to reflect changes
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong.');
        });
    });
</script>
