<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit;
}

$account_type = $_SESSION['account_type'] ?? 'Basic';
$username = $_SESSION['username'] ?? 'User';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Settings - Cursor1</title>
    <link rel="icon" type="image/png" href="../assets/images/RJA-icon Blue.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../home" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="../practice" class="nav-link">Practice Portal</a>
                </li>
                <li class="nav-item">
                    <a href="../contact" class="nav-link">Contact</a>
                </li>
                <li class="nav-item">
                    <a href="?logout=1" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <div class="practice-section">
            <div class="container">
                <div class="practice-header">
                    <h1 class="practice-title">Settings</h1>
                    <p class="practice-subtitle">Manage system settings and configurations</p>
                </div>

                <!-- Settings buttons -->
                <div class="settings-portal-grid">
                    <!-- Tasks - Available to all users -->
                    <div class="settings-portal-card">
                        <div class="settings-portal-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h3>Tasks</h3>
                        <p>Manage available tasks for timesheet entries</p>
                        <a href="../tasks" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            Manage Tasks
                        </a>
                    </div>

                    <?php if ($account_type === 'Administrator'): ?>
                    <!-- Users - Administrator only -->
                    <div class="settings-portal-card">
                        <div class="settings-portal-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3>Users</h3>
                        <p>Manage system users and accounts</p>
                        <a href="../users" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            Manage Users
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Emails - Available to all users -->
                    <div class="settings-portal-card">
                        <div class="settings-portal-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>Emails</h3>
                        <p>Design and customize email templates for different task types</p>
                        <a href="../emails" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            Manage Emails
                        </a>
                    </div>

                    <!-- Import Data - Available to all users -->
                    <div class="settings-portal-card">
                        <div class="settings-portal-icon">
                            <i class="fas fa-file-import"></i>
                        </div>
                        <h3>Import Data</h3>
                        <p>Import clients and jobs from CSV files</p>
                        <a href="../import" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            Import Data
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Cursor1. All rights reserved.</p>
        </div>
    </footer>

    <style>
        /* Reduce gap between header and page content */
        .practice-section {
            padding-top: 20px !important;
        }
        
        /* Compact settings page styles */
        .settings-portal-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 40px;
            justify-content: center;
        }
        
        .settings-portal-card {
            flex: 0 0 calc(16.666% - 12.5px); /* 1/6 minus gap */
            max-width: calc(16.666% - 12.5px);
            background: white;
            padding: 15px 10px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .settings-portal-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .settings-portal-icon {
            font-size: 1.5rem;
            color: #667eea;
            margin-bottom: 8px;
        }
        
        .settings-portal-card h3 {
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
            line-height: 1.2;
        }
        
        .settings-portal-card p {
            color: #666;
            line-height: 1.3;
            margin-bottom: 10px;
            font-size: 0.75rem;
            flex-grow: 1;
        }
        
        .settings-portal-card .btn {
            width: 100%;
            justify-content: center;
            padding: 8px 10px;
            font-size: 0.75rem;
            margin-top: auto;
        }
        
        /* Mobile responsive adjustments for settings */
        @media (max-width: 1200px) {
            .settings-portal-card {
                flex: 0 0 calc(25% - 11.25px); /* 1/4 minus gap */
                max-width: calc(25% - 11.25px);
            }
        }
        
        @media (max-width: 768px) {
            .settings-portal-card {
                flex: 0 0 calc(33.333% - 10px); /* 1/3 minus gap */
                max-width: calc(33.333% - 10px);
                padding: 12px 8px;
                min-height: 120px;
            }
            
            .settings-portal-icon {
                font-size: 1.3rem;
                margin-bottom: 6px;
            }
            
            .settings-portal-card h3 {
                font-size: 0.8rem;
                margin-bottom: 4px;
            }
            
            .settings-portal-card p {
                font-size: 0.7rem;
                margin-bottom: 8px;
            }
            
            .settings-portal-card .btn {
                padding: 6px 8px;
                font-size: 0.7rem;
            }
        }
        
        @media (max-width: 480px) {
            .settings-portal-card {
                flex: 0 0 calc(50% - 7.5px); /* 1/2 minus gap */
                max-width: calc(50% - 7.5px);
                padding: 8px 4px;
                min-height: 90px;
            }
            
            .settings-portal-icon {
                font-size: 1.1rem;
                margin-bottom: 3px;
            }
            
            .settings-portal-card h3 {
                font-size: 0.7rem;
                margin-bottom: 2px;
                line-height: 1.1;
            }
            
            .settings-portal-card p {
                font-size: 0.6rem;
                margin-bottom: 5px;
                line-height: 1.2;
            }
            
            .settings-portal-card .btn {
                padding: 4px 5px;
                font-size: 0.6rem;
            }
        }
    </style>
</body>
</html>
