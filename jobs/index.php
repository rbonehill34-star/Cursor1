<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and has appropriate access
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit;
}

$account_type = $_SESSION['account_type'] ?? 'Basic';
if (!in_array($account_type, ['Manager', 'Administrator'])) {
    header('Location: ../practice');
    exit;
}

$message = '';
$messageType = '';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = 'Job deleted successfully.';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Failed to delete job.';
        $messageType = 'error';
    }
}

// Handle archive action
if (isset($_GET['archive']) && is_numeric($_GET['archive'])) {
    try {
        $stmt = $pdo->prepare("UPDATE jobs SET state_id = (SELECT id FROM state WHERE state_name = 'Archived'), archived_date = NOW() WHERE id = ?");
        $stmt->execute([$_GET['archive']]);
        $message = 'Job archived successfully.';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Failed to archive job.';
        $messageType = 'error';
    }
}

// Get all non-archived jobs with related data
try {
    $stmt = $pdo->query("
        SELECT j.*, 
               c.name as client_name, 
               c.reference as client_reference,
               t.task_name,
               s.state_name,
               p.username as partner_name,
               m.username as manager_name,
               pr.username as preparer_name
        FROM jobs j
        LEFT JOIN clients c ON j.client_id = c.id
        LEFT JOIN tasks t ON j.task_id = t.id
        LEFT JOIN state s ON j.state_id = s.id
        LEFT JOIN login p ON j.partner_id = p.id
        LEFT JOIN login m ON j.manager_id = m.id
        LEFT JOIN login pr ON j.preparer_id = pr.id
        WHERE s.state_name != 'Archived'
        ORDER BY j.created_at DESC
    ");
    $jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    $jobs = [];
    $message = 'Failed to load jobs.';
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Jobs - Cursor1</title>
    <link rel="icon" type="image/png" href="../assets/images/RJA-icon Blue.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-briefcase"></i>
                <span>Jobs</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../practice" class="nav-link">Practice Portal</a>
                </li>
                <li class="nav-item">
                    <a href="../login?logout=1" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <div class="admin-section">
            <div class="container">
                <div class="page-header">
                    <h1 class="page-title">Job Management</h1>
                    <div class="page-actions">
                        <a href="add" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            New Job
                        </a>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($jobs)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h3>No jobs yet</h3>
                        <p>Get started by creating your first job.</p>
                        <a href="add" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Create First Job
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Task</th>
                                    <th>Description</th>
                                    <th>State</th>
                                    <th>Budget Hours</th>
                                    <th>Urgent</th>
                                    <th>Deadline</th>
                                    <th>Partner</th>
                                    <th>Manager</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jobs as $job): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($job['client_name']); ?></strong>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($job['client_reference']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($job['task_name']); ?></td>
                                        <td><?php echo htmlspecialchars($job['description'] ?? '-'); ?></td>
                                        <td>
                                            <span class="state-badge state-<?php echo strtolower(str_replace(' ', '-', $job['state_name'])); ?>">
                                                <?php echo htmlspecialchars($job['state_name']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $job['budget_hours'] ? $job['budget_hours'] . 'h' : '-'; ?></td>
                                        <td>
                                            <?php if ($job['urgent']): ?>
                                                <span class="urgent-badge">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    Urgent
                                                </span>
                                            <?php else: ?>
                                                <span class="no-data">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($job['deadline_date']): ?>
                                                <?php 
                                                $deadline = strtotime($job['deadline_date']);
                                                $today = time();
                                                $days_left = ceil(($deadline - $today) / (60 * 60 * 24));
                                                
                                                if ($days_left < 0) {
                                                    echo '<span class="overdue">' . date('M j, Y', $deadline) . '</span>';
                                                } elseif ($days_left <= 3) {
                                                    echo '<span class="due-soon">' . date('M j, Y', $deadline) . '</span>';
                                                } else {
                                                    echo date('M j, Y', $deadline);
                                                }
                                                ?>
                                            <?php else: ?>
                                                <span class="no-data">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($job['partner_name'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($job['manager_name'] ?? '-'); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($job['created_at'])); ?></td>
                                        <td>
                                            <a href="edit?id=<?php echo $job['id']; ?>" class="btn btn-action btn-edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?archive=<?php echo $job['id']; ?>" 
                                               class="btn btn-action btn-archive"
                                               onclick="return confirm('Are you sure you want to archive this job?')">
                                                <i class="fas fa-archive"></i>
                                            </a>
                                            <a href="?delete=<?php echo $job['id']; ?>" 
                                               class="btn btn-action btn-delete"
                                               onclick="return confirm('Are you sure you want to delete this job?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Cursor1. All rights reserved.</p>
        </div>
    </footer>

    <style>
        .state-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .state-outstanding { background: #fff3cd; color: #856404; }
        .state-received { background: #d1ecf1; color: #0c5460; }
        .state-prepare { background: #d4edda; color: #155724; }
        .state-returned { background: #f8d7da; color: #721c24; }
        .state-review { background: #e2e3e5; color: #383d41; }
        .state-with-client { background: #cce5ff; color: #004085; }
        .state-paid-not-approved { background: #fff3cd; color: #856404; }
        .state-approved-not-paid { background: #d1ecf1; color: #0c5460; }
        .state-submit { background: #d4edda; color: #155724; }
        .state-completed { background: #d4edda; color: #155724; }
        .state-other { background: #e2e3e5; color: #383d41; }
        
        .urgent-badge {
            background: #f8d7da;
            color: #721c24;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .overdue {
            color: #dc3545;
            font-weight: bold;
        }
        
        .due-soon {
            color: #fd7e14;
            font-weight: bold;
        }
        
        .text-muted {
            color: #6c757d;
            font-size: 12px;
        }
    </style>
</body>
</html>




