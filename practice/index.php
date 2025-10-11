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

// Get some basic stats
try {
    $stats = [];
    
    // Get timesheet entries for this user
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM timesheet WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $stats['timesheet_entries'] = $stmt->fetch()['count'];
    
    // Get this week's timesheet entries
    $week_start = date('Y-m-d', strtotime('monday this week'));
    $week_end = date('Y-m-d', strtotime('saturday this week'));
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM timesheet WHERE user_id = ? AND date BETWEEN ? AND ?");
    $stmt->execute([$_SESSION['user_id'], $week_start, $week_end]);
    $stats['this_week_entries'] = $stmt->fetch()['count'];
    
    // Get total clients (only for managers and administrators)
    if (in_array($account_type, ['Manager', 'Administrator'])) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM clients");
        $stats['total_clients'] = $stmt->fetch()['count'];
    }
    
    // Get form responses (only for administrators)
    if ($account_type === 'Administrator') {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM formresponse");
        $stats['form_responses'] = $stmt->fetch()['count'];
    }
    
} catch (PDOException $e) {
    $stats = ['timesheet_entries' => 0, 'this_week_entries' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Practice Portal - Cursor1</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-briefcase"></i>
                <span>Practice Portal</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../home" class="nav-link">Home</a>
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
                    <h1 class="practice-title">Practice Portal Test</h1>
                    <p class="practice-subtitle">Welcome back, <?php echo htmlspecialchars($username); ?>! (<?php echo $account_type; ?>)</p>
                </div>


                <div class="portal-grid">
                    <!-- 1. Timesheet - Available to all users -->
                    <div class="portal-card">
                        <div class="portal-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Timesheet</h3>
                        <p>Record your time and view timesheet entries</p>
                        <a href="../timesheet" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            Go to Timesheet
                        </a>
                    </div>

                    <!-- 2. My Job List - Available to all users -->
                    <div class="portal-card">
                        <div class="portal-icon">
                            <i class="fas fa-list-check"></i>
                        </div>
                        <h3>My Job List</h3>
                        <p>View jobs assigned to you, organized by status</p>
                        <a href="../myjobs" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            View My Jobs
                        </a>
                    </div>

                    <?php if (in_array($account_type, ['Manager', 'Administrator'])): ?>
                    <!-- 3. Jobs - Available to Managers and Administrators -->
                    <div class="portal-card">
                        <div class="portal-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h3>Jobs</h3>
                        <p>Manage jobs and track their progress</p>
                        <a href="../jobs" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            Manage Jobs
                        </a>
                    </div>

                    <!-- 4. Clients - Available to Managers and Administrators -->
                    <div class="portal-card">
                        <div class="portal-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Clients</h3>
                        <p>Manage client information and details</p>
                        <a href="../clients" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            Manage Clients
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- 5. Tasks - Available to all users -->
                    <div class="portal-card">
                        <div class="portal-icon">
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
                    <!-- 6. Contact Form Responses - Administrator only -->
                    <div class="portal-card">
                        <div class="portal-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>Contact Form Responses</h3>
                        <p>View and manage contact form submissions</p>
                        <a href="../admin" class="btn btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            View Responses
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['timesheet_entries']; ?></h3>
                            <p>Total Timesheet Entries</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-week"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['this_week_entries']; ?></h3>
                            <p>This Week's Entries</p>
                        </div>
                    </div>
                    <?php if (in_array($account_type, ['Manager', 'Administrator'])): ?>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['total_clients'] ?? 0; ?></h3>
                            <p>Total Clients</p>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($account_type === 'Administrator'): ?>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['form_responses'] ?? 0; ?></h3>
                            <p>Form Responses</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Cursor1. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
