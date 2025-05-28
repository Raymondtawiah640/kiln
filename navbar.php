<!-- nav.php -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="styles/navbar.css">

<button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation menu" aria-expanded="false">
    <i class="fas fa-bars"></i>
</button>

<div class="overlay" id="overlay"></div>

<nav id="sideNav">
    <div class="nav-header">
        <i class="fas fa-store"></i>
        <span>Kiln Store</span>
    </div>
    <ul>
        <li><a href="landing_dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'landing_dashboard.php' ? 'active' : '' ?>"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="products.php" class="<?= basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : '' ?>"><i class="fas fa-box-open"></i> Products</a></li>
        <li><a href="categories.php" class="<?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : '' ?>"><i class="fas fa-list"></i> Categories</a></li>
        <li><a href="about_us.php" class="<?= basename($_SERVER['PHP_SELF']) === 'about_us.php' ? 'active' : '' ?>"><i class="fas fa-info-circle"></i> About Us</a></li>
        <li><a href="contact_us.php" class="<?= basename($_SERVER['PHP_SELF']) === 'contact_us.php' ? 'active' : '' ?>"><i class="fas fa-envelope"></i> Contact Us</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="vendor_dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) === 'vendor_dashboard.php' ? 'active' : '' ?>"><i class="fas fa-store-alt"></i> Sell on Kiln</a></li>
            <li><a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>"><i class="fas fa-user"></i> Profile</a></li>
            <li><a href="inbox.php" class="<?= basename($_SERVER['PHP_SELF']) === 'inbox.php' ? 'active' : '' ?>"><i class="fas fa-inbox"></i> Inbox</a></li> 
            <li><a href="returns.php" class="<?= basename($_SERVER['PHP_SELF']) === 'returns.php' ? 'active' : '' ?>"><i class="fas fa-exchange-alt"></i> Returns</a></li>
            <li><a href="wishlist.php" class="<?= basename($_SERVER['PHP_SELF']) === 'wishlist.php' ? 'active' : '' ?>"><i class="fas fa-heart"></i> Wishlist</a></li>
            <li><a href="order_history.php" class="<?= basename($_SERVER['PHP_SELF']) === 'order_history.php' ? 'active' : '' ?>"><i class="fas fa-history"></i> Order History</a></li>
            <li>
                <a href="cart.php" class="cart-icon <?= basename($_SERVER['PHP_SELF']) === 'cart.php' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count"><?= $_SESSION['cart_count'] ?? 0 ?></span>
                    Cart
                </a>
            </li>
            <li><a href="admin_login.php" class="<?= basename($_SERVER['PHP_SELF']) === 'admin_login.php' ? 'active' : '' ?>"><i class="fas fa-user-shield"></i> Admin Dashboard</a></li>
            
        <?php else: ?>
            <li><a href="login.php" class="<?= basename($_SERVER['PHP_SELF']) === 'login.php' ? 'active' : '' ?>"><i class="fas fa-sign-in-alt"></i> Sign In</a></li>
            <li><a href="register.php" class="<?= basename($_SERVER['PHP_SELF']) === 'register.php' ? 'active' : '' ?>"><i class="fas fa-user-plus"></i> Sign Up</a></li>
        <?php endif; ?>
    </ul>
</nav>

<script>
   document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const sideNav = document.getElementById('sideNav');
    const overlay = document.getElementById('overlay');
    const mainContent = document.getElementById('mainContent');

    // Function to toggle the menu
    function toggleMenu() {
        const isOpen = sideNav.classList.toggle('show');
        overlay.classList.toggle('show');
        mainContent?.classList?.toggle('shifted');

        const icon = menuToggle.querySelector('i');
        icon.classList.toggle('fa-bars', !isOpen);
        icon.classList.toggle('fa-times', isOpen);

        menuToggle.setAttribute('aria-expanded', isOpen);
        document.body.style.overflow = isOpen ? 'hidden' : '';
    }

    // Toggle menu functionality
    menuToggle.addEventListener('click', toggleMenu);
    overlay.addEventListener('click', toggleMenu);

    document.querySelectorAll('#sideNav a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth < 768 && sideNav.classList.contains('show')) {
                toggleMenu();
            }
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sideNav.classList.contains('show')) {
            toggleMenu();
        }
    });

    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            sideNav.classList.remove('show');
            overlay.classList.remove('show');
            mainContent?.classList?.remove('shifted');
            const icon = menuToggle.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
            menuToggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }
    });
});

</script>