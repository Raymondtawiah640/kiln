<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="shortcut icon" href="logo.ico" sizes="96x96" type="image/x-icon">
    <style>
    /* Reset and base styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html, body {
        width: 100%;
        overflow-x: hidden; /* Prevent horizontal scroll */
        font-family: Arial, sans-serif;
    }

    body {
        background: #f5f5f5;
        line-height: 1.6;
    }

    img {
        max-width: 100%;
        height: auto;
        display: block;
    }

    /* Optional: Center page content */
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    /* Responsive text sizes */
    h1, h2, h3, p {
        word-wrap: break-word;
    }
</style>

</head>
<body>
    <?php
    require 'landing_dashboard.php';
    ?>
</body>
</html>
