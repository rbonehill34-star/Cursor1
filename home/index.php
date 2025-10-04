<?php
require_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Cursor1 - Database Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-database"></i>
                <span>Cursor1</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../home" class="nav-link active">Home</a>
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
                    <i class="fas fa-database"></i>
                </div>
                <h1 class="hero-title">Welcome to Cursor1</h1>
                <p class="hero-subtitle">This is my new database</p>
                <div class="hero-description">
                    <p>A modern, efficient database management system built with PHP and MySQL. 
                    Experience seamless data handling with our intuitive interface.</p>
                </div>
                <div class="hero-actions">
                    <a href="../contact" class="btn btn-primary">
                        <i class="fas fa-envelope"></i>
                        Get in Touch
                    </a>
                    <a href="../login" class="btn btn-secondary">
                        <i class="fas fa-sign-in-alt"></i>
                        Admin Login
                    </a>
                </div>
            </div>
        </div>

        <section id="features" class="features-section">
            <div class="container">
                <h2 class="section-title">Database Features</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3>Secure</h3>
                        <p>Advanced security measures to protect your data with encrypted connections and secure authentication.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h3>Fast</h3>
                        <p>Optimized queries and efficient data structures ensure lightning-fast response times.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h3>Reliable</h3>
                        <p>Built with reliability in mind, ensuring your data is always available when you need it.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Cursor1. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
