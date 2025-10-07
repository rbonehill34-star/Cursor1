<?php
require_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>About Us - RJ Accountancy Limited</title>
    <meta name="description" content="Learn about RJ Accountancy Limited, your trusted chartered accountants providing professional services with transparent pricing.">
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
                    <a href="../about" class="nav-link active">About Us</a>
                </li>
                <li class="nav-item">
                    <a href="../services" class="nav-link">Services</a>
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
                    <h1 class="contact-title">About RJ Accountancy Limited</h1>
                    <p class="contact-subtitle">Your trusted chartered accountants providing professional services with transparent pricing</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 3fr; gap: 30px; margin: 30px 0; align-items: start;">
                    <!-- Picture on the left -->
                    <div style="display: flex; justify-content: center; align-items: center;">
                        <img src="../assets/images/Woodhall-Spa-sign.jpg" alt="Woodhall Spa Sign" style="max-width: 100%; height: auto; border-radius: 10px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">
                    </div>
                    
                    <!-- Content box on the right (3/4 width) -->
                    <div style="background: white; padding: 40px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);">
                        <h3 style="color: #333; margin-bottom: 30px; padding-bottom: 10px; border-bottom: 2px solid #667eea;">About Us</h3>
                        <div style="text-align: left; line-height: 1.8; color: #555;">
                            <p style="margin-bottom: 15px;">RJ Accountancy is a Chartered Accountants practice based in the village of Woodhall Spa. It is run by Rob and Jo who are both ACA qualified accountants who have over 40 years of accounting experience between them covering a wide range of business types and sizes.</p>
                            
                            <p style="margin-bottom: 15px;">The business has been based in Lincolnshire since 2009 and we have grown steadily with increasing numbers of clients and team members. All of our work is performed in Lincolnshire and we do not outsource any work. We have a good number of local clients along with at least as many from outside of the Lincolnshire area. Remote working means that we can easily work with businesses based in London or any other part of the UK. We also work with a number of clients who operate outside of the UK.</p>
                            
                            <p style="margin-bottom: 15px;">Our clients cover a wide range of businesses. These include rental property owners, tradesmen, financial service providers, hairdressers, farmers, race horse trainers, mechanics, charities and retailers. Often we have been able to improve the accuracy and efficiency of record keeping and provide sound advice on tax and other matters.</p>
                            
                            <p style="margin-bottom: 15px;">We use a fixed fee schedule for all of our clients. We find that business owners are keen to have a clear picture of how they will be charged and like the fact that all clients are charged using the same schedule so you do not need to worry that you are being overcharged compared to others.</p>
                            
                            <p style="margin-bottom: 15px;">Our rates compare well with other accountants, partly because we are based in an area where costs are lower but also because we avoid the costs of a large offices and marketing budgets.</p>
                            
                            <p style="margin-bottom: 15px;">Both Rob and Jo have both worked in big 4 practices (PwC and KPMG) and we have experience of how these large firms operate and approach risk. This work provided exposure to the daily issues arising in a business and the resolution of problems along with corporate management processes.</p>
                            
                            <p style="margin-bottom: 15px;">In addition we have both worked in a number of smaller accounting firms and understand the needs of the smaller businesses. This may involve looking at tax efficiency or how to take the next step in your business such as taking on employees.</p>
                            
                            <p style="margin-bottom: 15px;">Working outside of accounting practice we have both had a spell in corporate management, running offices for multinational companies. This involved human resources, information technology, budgeting, marketing and other general activities.</p>
                            
                            <p style="margin-bottom: 15px;">Phil joined us following a career change and has completed his AAT certification. He has a strong understanding of accounting and is currently training for his ACA articles. His role in the business is to manage client workload, perform first level reviews and provide training.</p>
                            
                            <p style="margin-bottom: 15px;">Jess is a trainee accountant working towards his AAT certification and Sam is an accounts assistant. Both have sound accounting and bookkeeping knowledge, good computer skills and have worked on a wide range of clients.</p>
                            
                            <p style="margin-bottom: 15px;">Outside of work Rob is a keen golfer and Jo likes to run, bike ride and walk as much as possible to fill up her Strava feed. Phil and Jess are both weekend rugby players and Sam enjoys walking her dogs.</p>
                            
                            <p style="margin-bottom: 15px;">With the experience we have gathered we hope we can help you get the most out of your business whatever your objective may be.</p>
                            
                            <p style="margin-bottom: 0;">We are currently taking on new clients and have capacity to grow the business whist still maintaining a service level where we can give full attention to each of our clients needs.</p>
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
