<?php
session_start();
error_reporting(E_ALL); 
ini_set('display_errors', 1);
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = "Email and password are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                if ($user['is_approved'] == 1) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    header('Location: landing_dashboard.php');
                    exit();
                } else {
                    $error_message = "Your account is currently pending approval.";
                }
            } else {
                $error_message = "Invalid email or password.";
            }
        } else {
            $error_message = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Kiln Enterprise - Customer Login</title>
    <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
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

        .login-container {
            width: 100%;
            max-width: 420px;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }

        .login-container:hover {
            box-shadow: var(--shadow-lg);
        }

        .login-header {
            background: linear-gradient(135deg, var(--royal-purple) 0%, var(--blue-violet) 100%);
            color: var(--white);
            padding: 1.75rem;
            text-align: center;
        }

        .login-header h2 {
            font-weight: 600;
            font-size: 1.5rem;
            letter-spacing: 0.5px;
        }

        .login-body {
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

        .divider::before,
        .divider::after {
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

        .password-input-container {
            position: relative;
            width: 100%;
        }

        .form-control.with-icon {
            padding-right: 2.75rem;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 1rem;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.1rem;
            color: var(--text-light);
            user-select: none;
        }

        @media (max-width: 480px) {
            .login-container {
                max-width: 95%;
            }

            .login-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Customer Login</h2>
        </div>

        <div class="login-body">
            <?php if (isset($error_message)): ?>
                <div class="alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        class="form-control" 
                        placeholder="Email Address" 
                        required />
                </div>

                <div class="form-group">
                    <div class="password-input-container">
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-control with-icon" 
                            placeholder="Password" 
                            required />
                        <span class="toggle-password" onclick="togglePassword()">👁️</span>
                    </div>
                </div>

                <button type="submit" class="btn">Sign In</button>
            </form>

            <div class="login-footer">
                <p>Don't have an account? <a href="register.php">Create one now</a></p>
                <div class="divider">or</div>
                <p><a href="forgot_password.php?role=customer">Forgot your password?</a></p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const pwdInput = document.getElementById("password");
            pwdInput.type = pwdInput.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>
