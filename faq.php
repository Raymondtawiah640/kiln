<?php
session_start();
$loggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQs</title>
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

        .faq-category {
            margin-bottom: 3rem;
        }

        .faq-item {
            background-color: var(--white);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .faq-item:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .faq-question {
            color: var(--blue-violet);
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 0.8rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .faq-question::after {
            content: '+';
            font-size: 1.5rem;
            color: var(--dark-orchid);
        }

        .faq-item.active .faq-question::after {
            content: '-';
        }

        .faq-answer {
            color: var(--deep-indigo);
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .faq-item.active .faq-answer {
            max-height: 500px;
        }

        .search-container {
            margin: 2rem 0;
            position: relative;
        }

        .search-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--soft-violet);
            border-radius: 6px;
            font-size: 1rem;
            background-color: var(--white);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--orchid);
            box-shadow: 0 0 0 3px rgba(218, 112, 214, 0.2);
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
            
            .faq-question {
                font-size: 1.1rem;
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
            
            .faq-item {
                padding: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <?php require 'navbar.php'; ?>
    
    <header>
        <div class="container">
            <h1>Frequently Asked Questions</h1>
            <p>Find answers to common questions about our products and services</p>
        </div>
    </header>

    <div class="container">
        <?php if ($loggedIn): ?>
            <section>
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Search FAQs...">
                </div>

                <div class="faq-category">
                    <h2>General Questions</h2>
                    
                    <div class="faq-item">
                        <div class="faq-question">What services does your company provide?</div>
                        <div class="faq-answer">
                            <p>We provide innovative solutions including web development, mobile applications, cloud services, and digital transformation consulting. Our services are tailored to meet the specific needs of each client.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">How can I contact customer support?</div>
                        <div class="faq-answer">
                            <p>You can reach our customer support team 24/7 through our <a href="contact_us.php">contact form</a>, by email at support@companyx.com, or by phone at (555) 123-4567 during business hours (9am-5pm EST).</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">Where is your company located?</div>
                        <div class="faq-answer">
                            <p>Our headquarters is located at 123 Tech Avenue, Silicon Valley, CA. We also have satellite offices in New York, London, and Tokyo to serve our global clients.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-category">
                    <h2>Account & Billing</h2>
                    
                    <div class="faq-item">
                        <div class="faq-question">How do I reset my password?</div>
                        <div class="faq-answer">
                            <p>To reset your password, go to the login page and click "Forgot Password." You'll receive an email with instructions to create a new password. If you don't see the email, please check your spam folder.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">What payment methods do you accept?</div>
                        <div class="faq-answer">
                            <p>We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and bank transfers. For enterprise clients, we also offer invoice-based billing with net-30 terms.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">Can I upgrade or downgrade my plan?</div>
                        <div class="faq-answer">
                            <p>Yes, you can change your plan at any time from your account dashboard. Upgrades take effect immediately, while downgrades will apply at your next billing cycle.</p>
                        </div>
                    </div>
                </div>

                <div class="faq-category">
                    <h2>Technical Support</h2>
                    
                    <div class="faq-item">
                        <div class="faq-question">What browsers are supported?</div>
                        <div class="faq-answer">
                            <p>Our platform supports the latest versions of Chrome, Firefox, Safari, Edge, and Opera. For optimal performance, we recommend using Chrome or Firefox.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">How do I access API documentation?</div>
                        <div class="faq-answer">
                            <p>Our comprehensive API documentation is available in the Developer section of your account dashboard. You'll need developer privileges to access this resource.</p>
                        </div>
                    </div>
                    
                    <div class="faq-item">
                        <div class="faq-question">What's your uptime guarantee?</div>
                        <div class="faq-answer">
                            <p>We guarantee 99.9% uptime for all our services. You can view our current status and historical uptime at status.companyx.com.</p>
                        </div>
                    </div>
                </div>

                <section style="text-align: center; margin-top: 3rem;">
                    <h2>Still have questions?</h2>
                    <p>Can't find what you're looking for? Our team is happy to help.</p>
                    <a href="contact_us.php" class="btn">Contact Support</a>
                </section>
            </section>
        <?php else: ?>
            <!-- Message for non-logged in users -->
            <section class="login-required">
                <h2>Please Log In</h2>
                <p>To view our FAQs, please log in to your account.</p>
                <a href="login.php" class="btn">Login</a>
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </section>
        <?php endif; ?>
    </div>

    <script>
        // FAQ toggle functionality
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const item = question.parentElement;
                item.classList.toggle('active');
                
                // Close other open FAQs in the same category
                const category = item.parentElement;
                category.querySelectorAll('.faq-item').forEach(otherItem => {
                    if(otherItem !== item) {
                        otherItem.classList.remove('active');
                    }
                });
            });
        });

        // Simple search functionality
        const searchInput = document.querySelector('.search-input');
        if(searchInput) {
            searchInput.addEventListener('input', (e) => {
                const searchTerm = e.target.value.toLowerCase();
                document.querySelectorAll('.faq-item').forEach(item => {
                    const question = item.querySelector('.faq-question').textContent.toLowerCase();
                    const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                    if(question.includes(searchTerm) || answer.includes(searchTerm)) {
                        item.style.display = 'block';
                        item.classList.add('active'); // Show answer if question matches
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        }
    </script>
</body>
</html>