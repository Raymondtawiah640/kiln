<?php
session_start();
require 'db_connect.php'; // Database connection

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token is valid
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? LIMIT 1");
    $stmt->execute([$token]);
    $resetRequest = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the token exists and if it has not expired
    if ($resetRequest) { // Check if $resetRequest is not false
        if ($resetRequest['expires'] >= time()) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

                // Update password in users table
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$newPassword, $resetRequest['email']]);
                $userUpdated = $stmt->rowCount();

                if ($userUpdated === 0) {
                    $error_message = "No account found with this email.";
                } else {
                    // Delete the reset request if the update succeeded
                    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
                    $stmt->execute([$token]);
                    $success_message = "Your password has been reset successfully. You can now login.";
                }
            }
        } else {
            $error_message = "This token is invalid or has expired.";
        }
    } else {
        $error_message = "This token is invalid or has expired.";
    }
} else {
    header('Location: admin_login.php'); // Redirect if no token is provided
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Kiln Enterprise</title>
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
        
        .reset-container {
            width: 100%;
            max-width: 420px;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: var(--transition);
        }
        
        .reset-container:hover {
            box-shadow: var(--shadow-lg);
        }
        
        .reset-header {
            background: linear-gradient(135deg, var(--royal-purple) 0%, var(--blue-violet) 100%);
            color: var(--white);
            padding: 1.75rem;
            text-align: center;
        }
        
        .reset-header h2 {
            font-weight: 600;
            font-size: 1.5rem;
            letter-spacing: 0.5px;
        }
        
        .reset-body {
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
        
        .login-footer {
            text-align: center;
            margin-top: 1.75rem;
            font-size: 0.875rem;
            color: var(--text-light);
        }
        
        .login-footer a {
            color: var(--royal-purple);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .login-footer a:hover {
            color: var(--deep-indigo);
            text-decoration: underline;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
            color: var(--text-light);
            font-size: 0.8125rem;
        }
        
        .divider::before, .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid var(--lavender);
        }
        
        .divider::before {
            margin-right: 0.75rem;
        }
        
        .divider::after {
            margin-left: 0.75rem;
        }
        
        @media (max-width: 480px) {
            .reset-container {
                max-width: 95%;
            }
            
            .reset-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h2>Create New Password</h2>
        </div>
        
        <div class="reset-body">
            <?php if (isset($error_message)): ?>
                <div class="alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php elseif (isset($success_message)): ?>
                <div class="alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($resetRequest) && $resetRequest['expires'] >= time()): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <input 
                            type="password" 
                            name="new_password" 
                            class="form-control" 
                            placeholder="Enter new password" 
                            required
                        >
                    </div>
                    
                    <button type="submit" class="btn">Reset Password</button>
                    
                    <div class="login-footer">
                        <div class="divider">or</div>
                        <p>
                            <a href="login.php">Sign in as Customer</a> or 
                            <a href="admin_login.php">Sign in as Admin</a>
                        </p>
                    </div>
                </form>
            <?php else: ?>
                <div class="alert-error">
                    This token is invalid or has expired.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>