<?php
session_start();
require 'db_connect.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute the query to check for the admin
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify the password and check if the account is approved
    if ($admin) {
        if (password_verify($password, $admin['password'])) {
            if (isset($admin['is_approved']) && $admin['is_approved'] == 1) {
                $_SESSION['role'] = 'admin';
                $_SESSION['username'] = $admin['username'];
                header('Location: admin_panel.php');
                exit();
            } else {
                $error_message = "Your account is pending approval.";
            }
        } else {
            $error_message = "Invalid email or password.";
        }
    } else {
        $error_message = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login</title>
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

        .login-card {
            width: 100%;
            max-width: 420px;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }

        .login-card:hover {
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            background: linear-gradient(135deg, var(--deep-indigo) 0%, var(--royal-purple) 100%);
            color: var(--white);
            padding: 1.75rem;
            text-align: center;
        }

        .card-header h2 {
            font-weight: 600;
            font-size: 1.5rem;
            letter-spacing: 0.5px;
        }

        .card-body {
            padding: 2rem;
        }

        .alert {
            padding: 0.875rem 1rem;
            border-radius: calc(var(--border-radius) - 4px);
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
            text-align: center;
        }

        .alert-error {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-medium);
            font-size: 0.9375rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1.25rem;
            border: 1px solid var(--lavender);
            border-radius: calc(var(--border-radius) - 4px);
            font-size: 0.9375rem;
            background-color: var(--light-lavender);
            color: var(--text-dark);
            transition: var(--transition);
        }

        .form-input::placeholder {
            color: var(--text-light);
            opacity: 0.7;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--soft-violet);
            box-shadow: 0 0 0 3px rgba(138, 43, 226, 0.15);
            background-color: var(--white);
        }

        .password-input-container {
            position: relative;
            width: 100%;
        }

        .form-input.with-icon {
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

        .card-footer {
            text-align: center;
            margin-top: 1.75rem;
            font-size: 0.875rem;
            color: var(--text-light);
        }

        .card-footer a {
            color: var(--royal-purple);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .card-footer a:hover {
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

        @media (max-width: 480px) {
            .login-card {
                max-width: 95%;
            }

            .card-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="card-header">
            <h2>Admin Portal</h2>
        </div>

        <div class="card-body">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="email" class="form-label">Admin Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="admin@example.com" 
                        required />
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="password-input-container">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input with-icon" 
                            placeholder="••••••••" 
                            required />
                        <span class="toggle-password" onclick="togglePassword()">👁️</span>
                    </div>
                </div>

                <button type="submit" class="btn">Access Dashboard</button>

                <div class="card-footer">
                    <a href="forgot_password.php?role=admin">Forgot your password?</a>
                    <div class="divider">or</div>
                    <p>Request admin access? <a href="register.php">Contact system administrator</a></p>
                    <p>Regular user? <a href="login.php">Switch to user login</a></p>
                </div>
            </form>
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
