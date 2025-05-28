<?php
session_start();
require 'db_connect.php'; // Database connection
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOrUsername = $_POST['email_or_username'];

    // Generate token
    $token = bin2hex(random_bytes(50));
    $expires = time() + 3600; // 1 hour expiration

    // Search for the user in the users table
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$emailOrUsername, $emailOrUsername]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Insert into password_resets table
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
        $stmt->execute([$user['email'], $token, $expires]);

        // Send email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'enterprisekiln@gmail.com'; // Your email address
            $mail->Password = 'crsr vwdb almj xkxn'; // Your app password or email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use TLS
            $mail->Port = 587; // TCP port to connect to
        
            $mail->setFrom('enterprisekiln@gmail.com', 'Kiln Enterprise');
            $mail->addAddress($user['email']); // Add a recipient
            $mail->isHTML(true);
            $mail->Subject = "Password Reset Request";
            $mail->Body = "Reset your password by clicking this link: <a href='http://localhost/github/kilnmart/reset_password.php?token=$token' style='color: blue; text-decoration: underline;'>Reset Password</a>
            .<br> 
            This is a password reset request. Please follow the instructions in the email and we will like to you to keep
            it confidential.";
        
            $mail->send();
            $success_message = "Password reset link sent to your email.";
        } catch (Exception $e) {
            $error_message = "Error sending email: " . $mail->ErrorInfo;
        }
    } else {
        $error_message = "No account found with those details.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Kiln Enterprise</title>
    <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
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
            --white: #ffffff;
            --light-gray: #f0f0f5;
            --text-dark: #2d1b4e;
            --text-medium: #4a3a6e;
            --text-light: #6d5b8a;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f72585;
            --border-radius: 12px;
            --shadow-sm: 0 2px 8px rgba(75, 0, 130, 0.1);
            --shadow-md: 0 6px 18px rgba(75, 0, 130, 0.15);
            --shadow-lg: 0 12px 24px rgba(75, 0, 130, 0.2);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-lavender);
            color: var(--text-dark);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 1rem;
            background-image: radial-gradient(circle at 10% 20%, var(--lavender) 0%, var(--light-lavender) 90%);
        }
        
        .password-reset-container {
            width: 100%;
            max-width: 420px;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: var(--transition);
        }
        
        .password-reset-container:hover {
            box-shadow: var(--shadow-lg);
        }
        
        .password-reset-header {
            background: linear-gradient(135deg, var(--royal-purple) 0%, var(--blue-violet) 100%);
            color: var(--white);
            padding: 1.75rem;
            text-align: center;
        }
        
        .password-reset-header h2 {
            font-weight: 600;
            font-size: 1.5rem;
            letter-spacing: 0.5px;
        }
        
        .password-reset-body {
            padding: 2rem;
        }
        
        .alert-error {
            padding: 0.875rem 1rem;
            border-radius: calc(var(--border-radius) - 4px);
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
            text-align: center;
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }
        
        .alert-success {
            padding: 0.875rem 1rem;
            border-radius: calc(var(--border-radius) - 4px);
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
            text-align: center;
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.875rem 1.25rem;
            border: 1px solid var(--lavender);
            border-radius: calc(var(--border-radius) - 4px);
            font-size: 0.9375rem;
            background-color: var(--light-lavender);
            color: var(--text-dark);
            transition: var(--transition);
        }
        
        .form-control::placeholder {
            color: var(--text-light);
            opacity: 0.7;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--soft-violet);
            box-shadow: 0 0 0 3px rgba(138, 43, 226, 0.15);
            background-color: var(--white);
        }
        
        .btn {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, var(--royal-purple) 0%, var(--blue-violet) 100%);
            color: var(--white);
            border: none;
            border-radius: calc(var(--border-radius) - 4px);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 0.5rem;
            letter-spacing: 0.5px;
        }
        
        .btn:hover {
            background: linear-gradient(135deg, var(--deep-indigo) 0%, var(--dark-orchid) 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        
        .login-link {
            text-align: center;
            margin-top: 1.75rem;
            font-size: 0.875rem;
            color: var(--text-light);
        }
        
        .login-link a {
            color: var(--royal-purple);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .login-link a:hover {
            color: var(--deep-indigo);
            text-decoration: underline;
        }
        
        @media (max-width: 480px) {
            .password-reset-container {
                max-width: 95%;
            }
            
            .password-reset-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="password-reset-container">
        <div class="password-reset-header">
            <h2>Reset Your Password</h2>
        </div>
        
        <div class="password-reset-body">
            <?php if (isset($error_message)): ?>
                <div class="alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php elseif (isset($success_message)): ?>
                <div class="alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <input 
                        type="text" 
                        name="email_or_username" 
                        class="form-control" 
                        placeholder="Enter your email or username" 
                        required
                    >
                </div>
                
                <button type="submit" class="btn">Send Reset Link</button>
            </form>

            <div class="login-link">
                <p>Remember your password? <a href="login.php">Sign in here</a></p>
            </div>
        </div>
    </div>
</body>
</html>