<?php
// dark_mode.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Toggle dark mode if requested
if (isset($_GET['toggle_dark_mode'])) {
    $_SESSION['dark_mode'] = !($_SESSION['dark_mode'] ?? false);

    // Default redirect to landing_dashboard.php
    header("Location: landing_dashboard.php");
    exit;
}

// Determine current dark mode status
$darkMode = $_SESSION['dark_mode'] ?? false;
?>
