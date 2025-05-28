<?php 
    session_start();
    require 'db_connect.php'; // Database connection
    // Check if user is logged in
    $loggedIn = isset($_SESSION['user_id']); // Adjust this based on your session variable
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
    <link rel="stylesheet" href="styles/about_us.css">
    <link rel="shortcut icon" href="logo.ico" sizes="96x96"  type="image/x-icon">
</head>
<body>
    <?php 
    require 'navbar.php';
   ?>
    
    <header>
        <div class="container">
            <h1>About Our Company</h1>
            <p>Innovative solutions with a touch of creativity</p>
        </div>
    </header>

    <div class="container">
        <?php if ($loggedIn): ?>
            <!-- Content for logged in users -->
            <section>
                <h2>Our Story</h2>
                <p>Founded in 2024, our company began as a small startup with a big vision. Today, we've grown into a leading provider of innovative solutions, serving clients across the globe. Our journey has been marked by perseverance, creativity, and an unwavering commitment to excellence.</p>
                <p>From our humble beginnings in a garage to our current state-of-the-art facilities, we've never lost sight of our core values and the passion that started it all.</p>
            </section>

            <section class="values">
                <h2>Our Core Values</h2>
                <ul>
                    <li><strong>Innovation:</strong> We constantly push boundaries to deliver cutting-edge solutions</li>
                    <li><strong>Integrity:</strong> We conduct business with honesty and transparency</li>
                    <li><strong>Excellence:</strong> We strive for perfection in everything we do</li>
                    <li><strong>Collaboration:</strong> We believe great things happen when we work together</li>
                    <li><strong>Sustainability:</strong> We're committed to environmentally responsible practices</li>
                </ul>
            </section>

            <section>
                <h2>Meet Our Team</h2>
                <div class="team">
                    <div class="team-member">
                        <h3>Stephen Nii Nai</h3>
                        <p class="position">CEO & Founder</p>
                        <p>With over 15 years of industry experience, Stephen leads our company with vision and passion, driving our strategic direction and company culture.</p>
                    </div>
                    <div class="team-member">
                        <h3>Raymond Kwame Tawiah</h3>
                        <p class="position">Software Engineer</p>
                        <p>Raymond oversees all technical operations and drives our innovation strategy, ensuring we stay at the forefront of technological advancements.</p>
                    </div>
                    <div class="team-member">
                        <h3>Godfred Adjei</h3>
                        <p class="position">Software Engineer</p>
                        <p>Godfred ensures our projects run smoothly and exceed client expectations through meticulous planning and execution.</p>
                    </div>
                    <div class="team-member">
                        <h3>Hamid Aguswin</h3>
                        <p class="position">Software Engineer</p>
                        <p>Hamid is a passionate software engineer who brings strong backend expertise and a collaborative spirit to every project, ensuring scalable and efficient solutions.</p>
                    </div>
                    <div class="team-member">
                        <h3>Samuel Adjei</h3>
                        <p class="position">Software Engineer</p>
                        <p>Samuel is a dedicated software engineer with a focus on frontend development, creating user-friendly interfaces and seamless user experiences.</p>
                    </div>
                    <div class="team-member">
                        <h3>Raymond Kwame Tawiah</h3>
                        <p class="position">Software Engineer</p>
                        <p>Raymond oversees all technical operations and drives our innovation strategy, ensuring we stay at the forefront of technological advancements.</p>
                    </div>
                </div>
            </section>

            <section>
                <h2>Why Choose Us?</h2>
                <p>We combine technical expertise with creative thinking to deliver solutions that not only meet but exceed expectations. Our client-centric approach means we listen carefully to your needs and tailor our services accordingly, ensuring perfect alignment with your goals.</p>
                <p>With a proven track record of successful projects and satisfied clients, we bring both experience and innovation to every engagement.</p>
                <a href="contact_us.php" class="btn">Get in Touch</a>
            </section>
        <?php else: ?>
            <!-- Message for non-logged in users -->
            <section class="login-required">
                <h2>Please Log In</h2>
                <p>To view our company information, please log in to your account.</p>
                <a href="login.php" class="btn">Login</a>
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </section>
        <?php endif; ?>
    </div>
</body>
</html>