<?php
require_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Services - RJ Accountancy Limited</title>
    <meta name="description" content="Professional accountancy services including company accounts, self-assessment, VAT returns, PAYE, and tax planning from RJ Accountancy Limited.">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="../assets/images/RJA-icon Blue.png" alt="RJ Accountancy Logo" style="height: 30px; margin-right: 10px;">
                <span>RJ Accountancy</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../home" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="../about" class="nav-link">About Us</a>
                </li>
                <li class="nav-item">
                    <a href="../services" class="nav-link active">Services</a>
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
        <div class="contact-section">
            <div class="container">
                <div class="contact-header">
                    <h1 class="contact-title">Our Services</h1>
                    <p class="contact-subtitle">Professional chartered accountancy services to help your business thrive</p>
                </div>

                <section id="main-services" class="features-section">
                    <div class="container">
                        <div class="features-grid">
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-building"></i>
                                </div>
                                <h3>Company Accounts</h3>
                                <p>Complete preparation and filing of company accounts with Companies House and Corporation Tax returns with HMRC. We handle everything from basic accounts to complex group structures.</p>
                                <ul style="text-align: left; margin-top: 15px; color: rgba(255, 255, 255, 0.8);">
                                    <li>Annual accounts preparation</li>
                                    <li>Corporation Tax returns (CT600)</li>
                                    <li>Companies House filings</li>
                                    <li>Tax efficiency reviews</li>
                                </ul>
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <h3>Self Assessment Returns</h3>
                                <p>Expert preparation of individual self-assessment tax returns for employees, sole traders, and partnerships. From simple PAYE income to complex business accounts.</p>
                                <ul style="text-align: left; margin-top: 15px; color: rgba(255, 255, 255, 0.8);">
                                    <li>Individual tax returns</li>
                                    <li>Sole trader accounts</li>
                                    <li>Partnership returns</li>
                                    <li>Property rental income</li>
                                </ul>
                            </div>
                            <div class="feature-card">
                                <div class="feature-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h3>VAT Services</h3>
                                <p>Comprehensive VAT return preparation and submission services. We ensure accurate calculations and timely submissions to avoid penalties.</p>
                                <ul style="text-align: left; margin-top: 15px; color: rgba(255, 255, 255, 0.8);">
                                    <li>VAT return preparation</li>
                                    <li>Quarterly submissions</li>
                                    <li>VAT registration advice</li>
                                    <li>VAT planning</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <div class="contact-content" style="margin-top: 60px;">
                    <div class="contact-info">
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3>PAYE Services</h3>
                            <p>Complete payroll management including monthly submissions, auto-enrolment pension reporting, and employee record keeping.</p>
                        </div>
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-cloud"></i>
                            </div>
                            <h3>Cloud Accounting</h3>
                            <p>Modern cloud-based bookkeeping services using industry-leading software like QuickBooks and Xero for real-time financial insights.</p>
                        </div>
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-lightbulb"></i>
                            </div>
                            <h3>Tax Planning</h3>
                            <p>Strategic tax planning to help minimize your tax liability while ensuring full compliance with HMRC requirements.</p>
                        </div>
                    </div>

                    <div class="contact-form-container">
                        <h3>Additional Services</h3>
                        <div style="text-align: left; margin-top: 20px;">
                            <h4>Record Keeping</h4>
                            <p>We provide guidance on maintaining proper business records and can help organize existing records for accurate reporting.</p>
                            
                            <h4>Confirmation Statements</h4>
                            <p>Simple and straightforward confirmation statement filing for your company's annual return to Companies House.</p>
                            
                            <h4>Business Advice</h4>
                            <p>Ongoing support and advice to help your business make informed financial decisions and plan for growth.</p>
                            
                            <h4>Specialist Work</h4>
                            <p>For work outside our standard services, we can provide fixed quotes or hourly rates as appropriate.</p>
                        </div>
                        
                        <div style="margin-top: 30px; text-align: center;">
                            <a href="../fees" class="btn btn-primary">
                                <i class="fas fa-pound-sign"></i>
                                View Our Pricing
                            </a>
                            <a href="../contact" class="btn btn-secondary" style="margin-left: 15px;">
                                <i class="fas fa-envelope"></i>
                                Get a Quote
                            </a>
                        </div>
                    </div>
                </div>

                <div class="contact-content" style="margin-top: 60px; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); padding: 40px; border-radius: 20px;">
                    <h3 style="text-align: center; color: white; margin-bottom: 30px;">Why Choose Our Services?</h3>
                    <div class="features-grid">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3>Timely Service</h3>
                            <p>We understand the importance of meeting deadlines. All returns are prepared and submitted on time to avoid penalties.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3>Professional Standards</h3>
                            <p>As chartered accountants, we maintain the highest professional standards and stay current with tax legislation.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <h3>Personal Approach</h3>
                            <p>We take time to understand your business and provide tailored advice that meets your specific needs.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 RJ Accountancy Limited. All rights reserved.</p>
            <p>Director: Rob Bonehill (FCA)</p>
        </div>
    </footer>
</body>
</html>
