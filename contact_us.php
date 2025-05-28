<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$success_message = '';
$error_message = '';
$name = '';
$subject = '';
$message = '';

// Fetch user's email from the database (not from session)
$stmt_user = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt_user->execute([$_SESSION['user_id']]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit();
}

$profile_email = strtolower(trim($user['email'])); // Use this for comparison

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Normalize form email for comparison
    $normalized_form_email = strtolower(trim($email));

    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address!";
    } elseif ($normalized_form_email !== $profile_email) {
        $error_message = "The email does not match the one registered with your account!";
    } else {
        // Save message to database
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $message]);

            $success_message = "Thank you for contacting us! We'll get back to you soon.";
            $name = $subject = $message = '';
        } catch (PDOException $e) {
            $error_message = "Error submitting your message. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        /* New layout structure */
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #faf9ff;
        }

        .page-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Navbar styles - adjust these to match your actual navbar */
        .navbar {
            width: 280px;
            background: var(--deep-indigo);
            position: fixed;
            height: 100vh;
            z-index: 100;
        }

        /* Main content area */
        .main-content {
            flex: 1;
            margin-left: 280px; /* Same as navbar width */
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Content container */
        .content-container {
            flex: 1;
            padding: 2rem;
        }

        /* Footer styles */
        footer {
            background: var(--deep-indigo);
            color: white;
            padding: 1.5rem;
            text-align: center;
            margin-top: auto;
        }

        /* Your existing contact page styles */
        .contact-container {
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
        }

        .contact-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .contact-header h1 {
            color: var(--deep-indigo);
            font-size: 2.8rem;
            margin-bottom: 1rem;
            position: relative;
        }

        .contact-header h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--medium-purple), var(--deep-indigo));
            border-radius: 2px;
        }

        .contact-header p {
            font-size: 1.1rem;
            color: #4a3a6e;
            max-width: 680px;
            margin: 0 auto;
        }

        .contact-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 2.5rem;
        }

        .contact-form, .contact-info {
            background: #fff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(75, 0, 130, 0.08);
        }

        /* Rest of your existing styles... */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--deep-indigo);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--lavender);
            border-radius: 8px;
            font-size: 1rem;
            background-color: #fdfdff;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--blue-violet);
            box-shadow: 0 0 0 3px rgba(138, 43, 226, 0.15);
            outline: none;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 160px;
        }

        .submit-btn {
            background: linear-gradient(to right, var(--blue-violet), var(--dark-orchid));
            color: #fff;
            font-weight: bold;
            padding: 1rem;
            border-radius: 8px;
            border: none;
            font-size: 1rem;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(138, 43, 226, 0.25);
            background: linear-gradient(to right, var(--dark-orchid), var(--blue-violet));
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .info-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--royal-purple), var(--blue-violet));
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .info-content h3 {
            margin: 0 0 0.3rem;
            color: var(--deep-indigo);
            font-size: 1.1rem;
        }

        .info-content p {
            margin: 0;
            color: #4a3a6e;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.12);
            color: #27ae60;
            border-left: 4px solid #2ecc71;
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.12);
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
        }

        @media (max-width: 992px) {
            .navbar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
        }

        @media (max-width: 768px) {
            .contact-header h1 {
                font-size: 2.2rem;
            }

            .contact-header p {
                font-size: 1rem;
            }

            .info-item {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .info-icon {
                margin-bottom: 0.8rem;
            }
        }

        
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<main>
    <div class="contact-container">
        <div class="contact-header">
            <h1>Contact Us</h1>
            <p>Have questions or feedback? We'd love to hear from you! Reach out through the form below or use our contact information.</p>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php elseif ($error_message): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <div class="contact-content">
            <div class="contact-form">
                <form method="POST" action="contact_us.php">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <!-- Make sure the email input matches the session email -->
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($session_email ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" value="<?= htmlspecialchars($subject ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Your Message</label>
                        <textarea id="message" name="message" required><?= htmlspecialchars($message ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>

            <div class="contact-info">
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="info-content">
                        <h3>Our Location</h3>
                        <p>Koforidua Technical University</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-phone-alt"></i></div>
                    <div class="info-content">
                        <h3>Phone Number</h3>
                        <p>+233 20 864 9694<br>+233 25 746 3603</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-envelope"></i></div>
                    <div class="info-content">
                        <h3>Email Address</h3>
                        <p>enterprisekiln@gmail.com</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-clock"></i></div>
                    <div class="info-content">
                        <h3>Working Hours</h3>
                        <p>Mon - Fri: 8:00 AM - 6:00 PM<br>Sat: 9:00 AM - 2:00 PM</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>

