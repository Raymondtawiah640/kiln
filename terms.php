<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// terms.php - Terms and Conditions Page
$pageTitle = "Terms and Conditions";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" /> <!-- IMPORTANT for responsiveness -->
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon" />
    <style>
    /* Global box-sizing for easier sizing */
    *, *::before, *::after {
        box-sizing: border-box;
    }

    /* Purple Color Scheme Styling */
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

        --primary-accent: var(--royal-purple);
        --secondary-accent: var(--blue-violet);
        --light-bg: var(--light-lavender);
        --dark-text: #2c0230;
        --medium-text: #4a3c5a;
    }

    body {
        font-family: 'Arial', sans-serif;
        line-height: 1.6;
        color: var(--dark-text);
        background-color: var(--light-bg);
        margin: 0 0 0 90px; /* Left margin for sidebar or layout */
        padding: 0;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .terms-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 32px 40px;
        background-color: #fff;
        box-shadow: 0 6px 24px rgba(0, 0, 0, 0.1);
        border-radius: 16px;
        flex: 1;
        border: 1px solid var(--lavender);
        transition: box-shadow 0.3s ease;
    }

    .terms-container:hover {
        box-shadow: 0 10px 40px rgba(106, 13, 173, 0.3);
    }

    .terms-container h1 {
        color: var(--primary-accent);
        text-align: center;
        margin-bottom: 16px;
        font-size: 2.8rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-accent) 0%, var(--secondary-accent) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .last-updated {
        text-align: center;
        color: var(--medium-text);
        margin-bottom: 36px;
        font-size: 1rem;
        font-weight: 500;
    }

    .toggle-btn {
        background-color: var(--primary-accent);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        font-size: 1rem;
        transition: background-color 0.3s ease;
    }

    .toggle-btn:hover {
        background-color: var(--secondary-accent);
    }

    .terms-content {
        padding: 0 10px;
    }

    .terms-section {
        margin-bottom: 40px;
        padding-bottom: 24px;
        border-bottom: 1px dashed var(--lavender);
    }

    .terms-section:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .terms-section h2 {
        color: var(--deep-indigo);
        font-size: 1.75rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 8px;
        border-bottom: 3px solid var(--soft-violet);
    }

    .terms-section p {
        margin-bottom: 18px;
        color: var(--medium-text);
        line-height: 1.75;
        font-size: 1.1rem;
    }

    address {
        font-style: normal;
        background-color: var(--lavender);
        padding: 20px 24px;
        border-radius: 12px;
        margin-top: 24px;
        border-left: 6px solid var(--orchid);
        font-weight: 500;
        color: var(--dark-text);
        box-shadow: 0 2px 8px rgba(218, 112, 214, 0.15);
    }

    /* Footer styling */
    footer {
        background: linear-gradient(135deg, var(--deep-indigo) 0%, var(--primary-accent) 100%);
        color: var(--light-lavender);
        padding: 48px 0;
        border-top: 2px solid var(--medium-purple);
        text-align: center;
    }

    footer a {
        color: var(--soft-violet);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    footer a:hover {
        color: var(--orchid);
        text-decoration: underline;
    }

    footer .footer-accent {
        color: var(--orchid);
        font-weight: 700;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        body {
            margin-left: 20px; /* Reduce left margin on tablets */
        }

        .terms-container {
            margin: 20px 15px;
            padding: 25px 20px;
            border-radius: 12px;
        }

        .terms-content {
            padding: 0;
        }

        .terms-container h1 {
            font-size: 2.2rem;
        }

        .terms-section h2 {
            font-size: 1.4rem;
        }

        .terms-section p {
            font-size: 1rem;
        }
    }

    /* Additional smaller device adjustments */
    @media (max-width: 480px) {
        body {
            margin-left: 0; /* Remove left margin on phones */
        }

        .terms-container {
            margin: 15px 10px;
            padding: 20px 15px;
            border-radius: 10px;
        }

        .terms-container h1 {
            font-size: 1.8rem;
        }

        .terms-section h2 {
            font-size: 1.2rem;
            margin-bottom: 16px;
            padding-bottom: 6px;
        }

        .terms-section p {
            font-size: 0.9rem;
            margin-bottom: 14px;
        }

        address {
            padding: 15px 18px;
            font-size: 0.9rem;
        }

        footer {
            padding: 30px 10px;
            font-size: 0.9rem;
        }
    }
    
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="terms-container">
    <h1>Terms and Conditions</h1>
    <p class="last-updated">Last Updated: <?php echo date('F j, Y'); ?></p>
    
    <div style="text-align:center; margin-bottom: 20px;">
        <button id="toggleTermsBtn" class="toggle-btn">Hide Terms & Conditions</button>
    </div>

    <div class="terms-content">
        <section class="terms-section">
            <h2>1. Introduction</h2>
            <p>Welcome to our website. By accessing and using this website, you accept and agree to be bound by the terms and provisions of this agreement.</p>
        </section>
        
        <section class="terms-section">
            <h2>2. Intellectual Property</h2>
            <p>All content included on this site, such as text, graphics, logos, button icons, images, audio clips, digital downloads, data compilations, and software, is the property of our company or its content suppliers and protected by international copyright laws.</p>
        </section>
        
        <section class="terms-section">
            <h2>3. User Responsibilities</h2>
            <p>You agree not to use the website in a way that may impair the performance, corrupt the content or otherwise reduce the overall functionality of the website.</p>
            <p>You also agree not to compromise the security of the website or attempt to gain access to secured areas or sensitive information.</p>
        </section>
        
        <section class="terms-section">
            <h2>4. Limitation of Liability</h2>
            <p>We will not be liable for any direct, indirect, incidental, special or consequential damages that result from the use of, or the inability to use, the materials on this site, even if we have been advised of the possibility of such damages.</p>
        </section>
        
        <section class="terms-section">
            <h2>5. Changes to Terms</h2>
            <p>We reserve the right to make changes to these terms and conditions at any time. Your continued use of the website following the posting of changes will mean you accept those changes.</p>
        </section>
        
        <section class="terms-section">
            <h2>6. Contact Information</h2>
            <p>If you have any questions about these Terms and Conditions, please contact us at:</p>
            <address>
                Email: enterprisekiln@gmail.com<br>
                Phone: (+233) 53-852-2670<br>
                Address: Koforidua, Eastern Region, Ghana<br>
            </address>
        </section>
    </div>
</div>

<script>
    const toggleBtn = document.getElementById('toggleTermsBtn');
    const termsContent = document.querySelector('.terms-content');

    toggleBtn.addEventListener('click', () => {
        if (termsContent.style.display === 'none') {
            termsContent.style.display = 'block';
            toggleBtn.textContent = 'Hide Terms & Conditions';
        } else {
            termsContent.style.display = 'none';
            toggleBtn.textContent = 'Show Terms & Conditions';
        }
    });
</script>

<?php include 'footer.php'; ?>

</body>
</html>
