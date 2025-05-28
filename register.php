<?php
session_start();
require 'db_connect.php'; // Database connection

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username     = $_POST['username'];
    $email        = $_POST['email'];
    $raw_password = $_POST['password'];
    $role         = $_POST['role'];

    // Validate password strength
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $raw_password)) {
        $error_message = "Password must be at least 8 characters long and include an uppercase letter, lowercase letter, number, and special character.";
    } else {
        $password = password_hash($raw_password, PASSWORD_DEFAULT); // Hash password

        // Check if username exists
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $checkStmt->execute([$username]);
        $usernameExists = $checkStmt->fetchColumn();

        // Check if email exists
        $emailCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $emailCheckStmt->execute([$email]);
        $emailExists = $emailCheckStmt->fetchColumn();

        if ($usernameExists) {
            $error_message = "Username already exists. Please choose a different username.";
        } elseif ($emailExists) {
            $error_message = "Email already exists. Please choose a different email address.";
        } else {
            // Check number of admins to control auto-approval
            $adminCheckStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
            $adminCheckStmt->execute();
            $adminCount = $adminCheckStmt->fetchColumn();

            if ($role === 'admin') {
                if ($adminCount > 0) {
                    $is_approved = 0;
                    $success_message = "Registration successful! Your account is pending admin approval.";
                } else {
                    $is_approved = 1;
                    $success_message = "Registration successful! You can now log in.";
                }
            } else {
                $is_approved = 1;
                $success_message = "Registration successful! You can now log in.";
            }

            // Insert new user
            try {
                $insertStmt = $pdo->prepare("
                    INSERT INTO users (username, email, password, role, is_approved) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $insertStmt->execute([$username, $email, $password, $role, $is_approved]);

                $_SESSION['success_message'] = $success_message;
                header("Location: register.php");
                exit();
            } catch (PDOException $e) {
                $error_message = "Registration failed: " . $e->getMessage();
            }
        }
    }
}

// Display success message if redirected
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Kiln Enterprise - Registration</title>
    <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="styles/register.css" />
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h2>Create Account</h2>
        </div>

        <div class="register-body">
            <?php if (isset($error_message)): ?>
                <div class="alert-error">
                    <?= htmlspecialchars($error_message); ?>
                </div>
            <?php elseif (isset($message)): ?>
                <div class="alert-success">
                    <?= htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <input 
                        type="text" 
                        name="username" 
                        class="form-control" 
                        placeholder="Username" 
                        required />
                </div>

                <div class="form-group">
                    <input 
                        type="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="Email Address" 
                        required />
                </div>

                <div class="form-group password-wrapper">
                    <div class="password-input-container">
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-control with-icon" 
                            placeholder="Password" 
                            required 
                            pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}" 
                            title="At least 8 characters, including uppercase, lowercase, number, and symbol." />
                        <span class="toggle-password" onclick="togglePassword()">👁️</span>
                    </div>
                    <p class="password-hint">
                        Password must contain at least 8 characters, including uppercase, lowercase, number, and special character.
                    </p>
                </div>

                <div class="form-group">
                    <select name="role" class="form-control" required>
                        <option value="" disabled selected>Select account type</option>
                        <option value="customer">Customer Account</option>
                        <option value="admin">Admin Account</option>
                    </select>
                </div>

                <button type="submit" class="btn">Register Now</button>
            </form>

            <div class="login-footer">
                <div class="divider">Already have an account?</div>
                <p>
                    <a href="login.php">Sign in as Customer</a> or 
                    <a href="admin_login.php">Sign in as Admin</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const pwdField = document.getElementById("password");
            pwdField.type = pwdField.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>
