<?php
require_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>RJ Accountancy Limited - Chartered Accountants</title>
    <meta name="description" content="Professional chartered accountancy services with fixed fee pricing. Company accounts, self-assessment, VAT returns, and tax planning.">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="../assets/images/RJA-icon Blue.png" alt="RJ Accountancy Logo" style="height: 30px; margin-right: 10px;">
                <span>RJ Accountancy Limited</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../home" class="nav-link active">Home</a>
                </li>
                <li class="nav-item">
                    <a href="../about" class="nav-link">About Us</a>
                </li>
                <li class="nav-item">
                    <a href="../fees" class="nav-link">Fees</a>
                </li>
                <li class="nav-item">
                    <a href="../contact" class="nav-link">Contact</a>
                </li>
                <li class="nav-item">
                    <a href="../login" class="nav-link">Login</a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <div class="hero-section">
            <div class="hero-content">
                <div class="hero-icon">
                    <img src="../assets/images/RJA-icon white.png" alt="RJ Accountancy Logo" style="height: 80px; opacity: 0.9;">
                </div>
                <h1 class="hero-title">RJ Accountancy Limited</h1>
                <p class="hero-subtitle">Chartered Accountants</p>
                <div class="hero-description">
                    <p>Professional chartered accountancy services with transparent, fixed fee pricing. 
                    We provide company accounts, self-assessment returns, VAT services, and expert tax planning 
                    to help your business grow and thrive.</p>
                </div>
                <div class="hero-actions">
                    <a href="../contact" class="btn btn-primary">
                        <i class="fas fa-envelope"></i>
                        Get in Touch
                    </a>
                    <a href="../fees" class="btn btn-secondary">
                        <i class="fas fa-pound-sign"></i>
                        View Our Fees
                    </a>
                </div>
            </div>
        </div>

        <section id="services" class="features-section">
            <div class="container">
                <h2 class="section-title">Our Services</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h3>Company Accounts</h3>
                        <p>Professional preparation and filing of company accounts with Companies House and Corporation Tax returns with HMRC. Clear, transparent pricing based on turnover.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3>Self Assessment</h3>
                        <p>Expert self-assessment tax return preparation for individuals, sole traders, and partnerships. From basic returns to complex business accounts.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>VAT & Tax Planning</h3>
                        <p>Comprehensive VAT return services and strategic tax planning to help you save money and stay compliant with HMRC requirements.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="contact-info" class="contact-section">
            <div class="container">
                <div class="contact-header">
                    <h2 class="contact-title">Get Professional Accountancy Help</h2>
                    <p class="contact-subtitle">Clear costs, transparent pricing, and expert service</p>
                </div>
                <div class="contact-content">
                    <div class="contact-info">
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h3>Email Us</h3>
                            <p>info@rjaccountancy.co.uk</p>
                        </div>
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <h3>Call Us</h3>
                            <p>01526 354 687</p>
                        </div>
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-pound-sign"></i>
                            </div>
                            <h3>Fixed Fee Pricing</h3>
                            <p>Transparent costs with no hidden charges</p>
                        </div>
                    </div>
                    <div class="contact-form-container">
                        <h3>Quick Enquiry</h3>
                        <p>Use our contact form for Company Accounts or Self Assessment help</p>
                        <div style="margin-top: 20px;">
                            <a href="../contact" class="btn btn-primary">
                                <i class="fas fa-envelope"></i>
                                Contact Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 RJ Accountancy Limited. All rights reserved.</p>
            <p>Director: Rob Bonehill (FCA)</p>
        </div>
    </footer>
</body>
</html>
