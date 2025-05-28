<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* ========== Footer Variables ========== */
    :root {
        --footer-bg: linear-gradient(135deg, var(--deep-indigo) 0%, var(--royal-purple) 100%);
        --footer-text: #f8f5ff;
        --footer-border: rgba(255,255,255,0.1);
        --footer-link: #d8bfd8;
        --footer-link-hover: #ffffff;
        --footer-accent: #da70d6;
    }

    /* ========== Footer Base Styles ========== */
    footer {
        background: var(--footer-bg);
        color: var(--footer-text);
        padding: 3rem 1rem 1.5rem;
        margin-top: 3rem;
        width: 100%;
        font-size: 0.95rem;
        position: relative;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 2rem;
        text-align: left;
    }

    .footer-section h3 {
        color: white;
        margin-bottom: 1.5rem;
        font-size: 1.2rem;
        position: relative;
        padding-bottom: 0.75rem;
        letter-spacing: 0.5px;
    }

    .footer-section h3::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background: var(--footer-accent);
        border-radius: 3px;
    }

    .footer-links {
        list-style: none;
    }

    .footer-links li {
        margin-bottom: 0.8rem;
    }

    .footer-links a {
        color: var(--footer-link);
        text-decoration: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        font-weight: 500;
    }

    p{
        color: white;
    }

    .footer-links a:hover {
        color: var(--footer-link-hover);
        transform: translateX(5px);
    }

    .footer-links a i {
        margin-right: 10px;
        width: 20px;
        text-align: center;
        color: var(--footer-accent);
    }

    .social-links {
        display: flex;
        gap: 1rem;
        margin: 1.5rem 0;
    }

    .social-links a {
        color: white;
        background: rgba(255,255,255,0.15);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        font-size: 1.1rem;
    }

    .social-links a:hover {
        background: var(--footer-accent);
        transform: translateY(-5px) scale(1.1);
        box-shadow: 0 5px 15px rgba(218, 112, 214, 0.4);
    }

    .contact-info {
        margin-top: 1rem;
        line-height: 1.8;
    }

    .contact-info i {
        color: var(--footer-accent);
        margin-right: 10px;
        width: 20px;
        text-align: center;
    }

    .footer-bottom {
        text-align: center;
        margin-top: 3rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--footer-border);
        color: var(--footer-link);
        font-size: 0.9rem;
    }

    .footer-bottom p {
        margin: 0;
    }

    /* ========== Decorative Elements ========== */
    footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 5px;
        background: linear-gradient(to right, var(--medium-purple), var(--blue-violet));
    }

    .disabled-link {
    pointer-events: none;  /* Disables clicking */
    opacity: 0.5;          /* Optional: make it look disabled */
    cursor: default;       /* Optional: change cursor */
}

    /* ========== Responsive Styles ========== */
    @media (max-width: 768px) {
        .footer-container {
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .footer-section {
            text-align: center;
        }
        
        .footer-section h3::after {
            left: 50%;
            transform: translateX(-50%);
        }
        
        .footer-links a:hover {
            transform: none;
        }
        
        .social-links {
            justify-content: center;
        }
    }

    @media (max-width: 576px) {
        .footer-container {
            grid-template-columns: 1fr;
            gap: 2.5rem;
        }
        
        .footer-section {
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--footer-border);
        }
        
        .footer-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
    }
</style>

<footer>
    <div class="footer-container">
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul class="footer-links">
                <li><a href="landing_dashboard.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="products.php"><i class="fas fa-box-open"></i> Products</a></li>
                <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="about_us.php"><i class="fas fa-info-circle"></i> About Us</a></li>
                <li><a href="vendor_dashboard.php"><i class="fas fa-warehouse"></i> Sell on Kiln</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h3>Customer Service</h3>
            <ul class="footer-links">
                <li><a href="contact_us.php"><i class="fas fa-envelope"></i> Contact Us</a></li>
                <li><a href="faq.php"><i class="fas fa-question-circle"></i> FAQs</a></li>
                <li><a href="shipping.php" class="disabled-link"><i  class="fas fa-truck"></i> Shipping</a></li>
                <li><a href="returns.php"><i class="fas fa-exchange-alt"></i> Returns</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h3>Legal</h3>
            <ul class="footer-links">
                <li><a href="privacy.php"><i class="fas fa-lock"></i> Privacy Policy</a></li>
                <li><a href="terms.php"><i class="fas fa-file-contract"></i> Terms</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h3>Connect With Us</h3>
            <div class="social-links">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="https://wa.me/+233208649694" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
            </div>
            <div class="contact-info">
                <p><i class="fas fa-envelope"></i> enterprisekiln@gmail.com</p>
                <p><i class="fas fa-phone"></i> +233 20 864 9694</p>
                <p><i class="fas fa-map-marker-alt"></i> Accra, Ghana</p>
            </div>
        </div>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Only apply this on mobile screens
    if (window.matchMedia('(max-width: 768px)').matches) {
        const footerSections = document.querySelectorAll('.footer-section');
        
        // Add toggle functionality to each section
        footerSections.forEach(section => {
            const heading = section.querySelector('h3');
            const content = section.querySelector('ul, .social-links, .contact-info');
            
            // Create and insert chevron icon
            const chevron = document.createElement('i');
            chevron.className = 'fas fa-chevron-down';
            chevron.style.marginLeft = '10px';
            chevron.style.transition = 'transform 0.3s ease';
            heading.appendChild(chevron);
            
            // Make heading clickable
            heading.style.cursor = 'pointer';
            heading.setAttribute('aria-expanded', 'true');
            heading.setAttribute('role', 'button');
            
            // Toggle function
            const toggleSection = () => {
                const isExpanded = heading.getAttribute('aria-expanded') === 'true';
                
                if (isExpanded) {
                    // Collapse section
                    content.style.maxHeight = '0';
                    content.style.opacity = '0';
                    content.style.overflow = 'hidden';
                    content.style.paddingTop = '0';
                    content.style.paddingBottom = '0';
                    chevron.style.transform = 'rotate(-90deg)';
                    heading.setAttribute('aria-expanded', 'false');
                } else {
                    // Expand section
                    content.style.maxHeight = content.scrollHeight + 'px';
                    content.style.opacity = '1';
                    content.style.paddingTop = '1rem';
                    content.style.paddingBottom = '3rem';
                    chevron.style.transform = 'rotate(0deg)';
                    heading.setAttribute('aria-expanded', 'true');
                }
            };
            
            // Initialize sections as expanded
            content.style.transition = 'all 0.3s ease, opacity 0.2s ease';
            content.style.maxHeight = content.scrollHeight + 'px';
            content.style.opacity = '1';
            
            // Add click event
            heading.addEventListener('click', toggleSection);
            
            // Add keyboard support
            heading.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleSection();
                }
            });
        });
        
        // Collapse all sections except first one by default
        if (footerSections.length > 0) {
            for (let i = 1; i < footerSections.length; i++) {
                const heading = footerSections[i].querySelector('h3');
                const content = footerSections[i].querySelector('ul, .social-links, .contact-info');
                
                content.style.maxHeight = '0';
                content.style.opacity = '0';
                content.style.overflow = 'hidden';
                content.style.paddingTop = '0';
                content.style.paddingBottom = '0';
                heading.querySelector('i').style.transform = 'rotate(-90deg)';
                heading.setAttribute('aria-expanded', 'false');
            }
        }
    }
});
</script>
    <div class="footer-bottom">
        <p>&copy; 2025 Kiln Enterprise. All rights reserved.</p>
    </div>
</footer>