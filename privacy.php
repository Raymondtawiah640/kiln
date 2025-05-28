<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Our Company</title>
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
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-lavender);
            color: var(--deep-indigo);
            line-height: 1.7;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin-left: 200px;
        }

        /* Header Styles */
        header {
            background: linear-gradient(135deg, var(--deep-indigo), var(--royal-purple));
            color: white;
            padding: 6rem 0 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 75% 30%, rgba(218, 112, 214, 0.2), transparent 70%);
        }

        header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--orchid), var(--blue-violet));
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            flex: 1;
        }

        .login-required {
            text-align: center;
            padding: 2rem;
            background-color: var(--lavender);
            border-radius: 12px;
            margin: 2rem auto;
            max-width: 600px;
        }

        h1 {
            font-size: 2.8rem;
            margin-bottom: 1rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            position: relative;
            display: inline-block;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--orchid);
            border-radius: 2px;
        }

        h2 {
            color: var(--royal-purple);
            margin: 2.5rem 0 1.5rem;
            font-size: 2rem;
            border-bottom: 3px solid var(--soft-violet);
            padding-bottom: 0.5rem;
            font-weight: 600;
        }

        h3 {
            color: var(--blue-violet);
            margin: 2rem 0 1rem;
            font-size: 1.5rem;
        }

        p {
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            color: var(--deep-indigo);
            line-height: 1.8;
        }

        ul, ol {
            margin-bottom: 1.5rem;
            padding-left: 2rem;
        }

        li {
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
        }

        .policy-section {
            background-color: var(--lavender);
            padding: 2rem;
            border-radius: 12px;
            margin: 2rem 0;
            box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.05);
        }

        .highlight-box {
            background-color: var(--white);
            border-left: 4px solid var(--orchid);
            padding: 1.5rem;
            margin: 1.5rem 0;
            border-radius: 0 8px 8px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn {
            display: inline-block;
            background-color: var(--orchid);
            color: white;
            padding: 1rem 2rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
            font-size: 1.1rem;
            box-shadow: 0 4px 12px rgba(218, 112, 214, 0.3);
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: var(--dark-orchid);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(218, 112, 214, 0.4);
        }

        a{
            text-decoration: none;
        }
        a:hover{
            text-decoration: underline;
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        section {
            animation: fadeIn 0.6s ease-out forwards;
        }

        /* Responsive Media Queries */
        @media (max-width: 1024px) {
            body {
                margin-left: 0 !important;
            }
            
            .container {
                padding: 2rem;
            }
            
            h1 {
                font-size: 2.4rem;
            }
            
            h2 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 768px) {
            header {
                padding: 5rem 1rem 2.5rem;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            h2 {
                font-size: 1.6rem;
                margin: 2rem 0 1.2rem;
            }
            
            .policy-section {
                padding: 1.5rem;
            }
            
            p, li {
                font-size: 1.05rem;
            }
        }

        @media (max-width: 480px) {
            header {
                padding: 4rem 1rem 2rem;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            h2 {
                font-size: 1.4rem;
                margin: 1.8rem 0 1rem;
            }
            
            .container {
                padding: 1.5rem;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
            
            ul, ol {
                padding-left: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php require 'navbar.php'; ?>
    
    <header>
        <div class="container">
            <h1>Privacy Policy</h1>
            <p>Last updated: <?php echo date('F j, Y'); ?></p>
        </div>
    </header>

    <div class="container">
        <?php if ($loggedIn): ?>
            <section>
                <p>At Kiln Enterprise, we are committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website.</p>
                
                <div class="highlight-box">
                    <p><strong>Note:</strong> By using our website, you consent to our Privacy Policy and agree to its terms.</p>
                </div>
            </section>

            <section class="policy-section">
                <h2>Information We Collect</h2>
                <p>We may collect personal information that you voluntarily provide to us when you:</p>
                <ul>
                    <li>Register on our website</li>
                    <li>Place an order</li>
                    <li>Subscribe to our newsletter</li>
                    <li>Contact us through our forms</li>
                </ul>
                
                <h3>Automatically Collected Information</h3>
                <p>When you visit our website, we may automatically collect certain information including:</p>
                <ul>
                    <li>IP address and browser type</li>
                    <li>Pages you visited and time spent</li>
                    <li>Device characteristics and operating system</li>
                </ul>
            </section>

            <section class="policy-section">
                <h2>How We Use Your Information</h2>
                <p>We may use the information we collect for various purposes, including to:</p>
                <ul>
                    <li>Provide, operate, and maintain our website</li>
                    <li>Improve, personalize, and expand our website</li>
                    <li>Understand and analyze how you use our website</li>
                    <li>Develop new products, services, features, and functionality</li>
                    <li>Send you emails and other communications</li>
                    <li>Find and prevent fraud</li>
                </ul>
            </section>

            <section class="policy-section">
                <h2>Data Security</h2>
                <p>We implement appropriate technical and organizational measures to protect the security of your personal information. However, please remember that no method of transmission over the Internet or method of electronic storage is 100% secure.</p>
                
                <h3>Your Data Protection Rights</h3>
                <p>Depending on your location, you may have certain rights regarding your personal information, including:</p>
                <ul>
                    <li>The right to access, update, or delete your information</li>
                    <li>The right to rectification if your information is inaccurate</li>
                    <li>The right to object to our processing of your data</li>
                    <li>The right to request restriction of processing</li>
                    <li>The right to data portability</li>
                </ul>
            </section>

            <section>
                <h2>Changes to This Privacy Policy</h2>
                <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date.</p>
                <p>You are advised to review this Privacy Policy periodically for any changes.</p>
                
                <div class="highlight-box">
                    <p><strong>Contact Us:</strong> If you have any questions about this Privacy Policy, please contact us at <a href="mailto:enterpriseprise@gmail.com"> enterpriseprise@gmail.com</a>.</p>
                </div>
            </section>
        <?php else: ?>
            <!-- Message for non-logged in users -->
            <section class="login-required">
                <h2>Please Log In</h2>
                <p>To view our privacy policy, please log in to your account.</p>
                <a href="login.php" class="btn">Login</a>
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </section>
        <?php endif; ?>
    </div>
</body>
</html>