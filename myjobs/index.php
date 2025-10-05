<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit;
}

$account_type = $_SESSION['account_type'] ?? 'Basic';
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';

// Get current tab from URL parameter
$current_tab = $_GET['tab'] ?? 'all';

// Define tabs based on user role
$available_tabs = ['all' => 'All'];

if ($account_type === 'Basic') {
    $available_tabs['prepare'] = 'Prepare';
    $available_tabs['returned'] = 'Returned';
    $available_tabs['other'] = 'Other';
} elseif ($account_type === 'Manager') {
    $available_tabs['prepare'] = 'Prepare';
    $available_tabs['returned'] = 'Returned';
    $available_tabs['review'] = 'Review';
    $available_tabs['other'] = 'Other';
} elseif ($account_type === 'Administrator') {
    $available_tabs['prepare'] = 'Prepare';
    $available_tabs['returned'] = 'Returned';
    $available_tabs['review'] = 'Review';
    $available_tabs['with-client'] = 'With Client';
    $available_tabs['paid-not-approved'] = 'Paid not approved';
    $available_tabs['approved-not-paid'] = 'Approved not paid';
    $available_tabs['submit'] = 'Submit';
    $available_tabs['completed'] = 'Completed';
    $available_tabs['other'] = 'Other';
}

// Validate tab
if (!array_key_exists($current_tab, $available_tabs)) {
    $current_tab = 'all';
}

// Build the SQL query based on current tab and user role
try {
    $where_conditions = [];
    $params = [$user_id];
    
    // Base condition: jobs assigned to the logged-in user
    $where_conditions[] = "(j.preparer_id = ? OR j.manager_id = ? OR j.partner_id = ?)";
    $params = [$user_id, $user_id, $user_id];
    
    // Add tab-specific filtering
    if ($current_tab !== 'all') {
        $where_conditions[] = "s.state_name = ?";
        $params[] = ucwords(str_replace('-', ' ', $current_tab));
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    $stmt = $pdo->prepare("
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
        WHERE $where_clause
        ORDER BY 
            CASE 
                WHEN j.expected_completion_date IS NOT NULL THEN j.expected_completion_date
                WHEN j.deadline_date IS NOT NULL THEN j.deadline_date
                ELSE j.created_at
            END ASC
    ");
    
    $stmt->execute($params);
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
    <title>My Job List - Cursor1</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-briefcase"></i>
                <span>My Job List</span>
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
                    <h1 class="page-title">My Job List</h1>
                    <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($username); ?>! (<?php echo $account_type; ?>)</p>
                </div>

                <!-- Tab Navigation -->
                <div class="tab-navigation">
                    <?php foreach ($available_tabs as $tab_key => $tab_label): ?>
                        <a href="?tab=<?php echo $tab_key; ?>" 
                           class="tab-link <?php echo $current_tab === $tab_key ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($tab_label); ?>
                            <?php 
                            // Show count for each tab
                            if ($tab_key !== 'all') {
                                try {
                                    $count_stmt = $pdo->prepare("
                                        SELECT COUNT(*) as count
                                        FROM jobs j
                                        LEFT JOIN state s ON j.state_id = s.id
                                        WHERE (j.preparer_id = ? OR j.manager_id = ? OR j.partner_id = ?)
                                        AND s.state_name = ?
                                    ");
                                    $count_stmt->execute([$user_id, $user_id, $user_id, ucwords(str_replace('-', ' ', $tab_key))]);
                                    $count = $count_stmt->fetch()['count'];
                                    if ($count > 0) {
                                        echo '<span class="tab-count">' . $count . '</span>';
                                    }
                                } catch (PDOException $e) {
                                    // Ignore count errors
                                }
                            } else {
                                try {
                                    $count_stmt = $pdo->prepare("
                                        SELECT COUNT(*) as count
                                        FROM jobs j
                                        WHERE (j.preparer_id = ? OR j.manager_id = ? OR j.partner_id = ?)
                                    ");
                                    $count_stmt->execute([$user_id, $user_id, $user_id]);
                                    $count = $count_stmt->fetch()['count'];
                                    if ($count > 0) {
                                        echo '<span class="tab-count">' . $count . '</span>';
                                    }
                                } catch (PDOException $e) {
                                    // Ignore count errors
                                }
                            }
                            ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if (isset($message)): ?>
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
                        <h3>No jobs found</h3>
                        <p>You don't have any jobs in the "<?php echo htmlspecialchars($available_tabs[$current_tab]); ?>" category.</p>
                        <a href="../practice" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Practice Portal
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
                                    <th>Status</th>
                                    <th>Budget Hours</th>
                                    <th>Urgent</th>
                                    <th>Expected Completion</th>
                                    <th>Deadline</th>
                                    <th>Your Role</th>
                                    <th>Partner</th>
                                    <th>Manager</th>
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
                                            <?php if ($job['expected_completion_date']): ?>
                                                <?php 
                                                $completion_date = strtotime($job['expected_completion_date']);
                                                $today = time();
                                                $days_left = ceil(($completion_date - $today) / (60 * 60 * 24));
                                                
                                                if ($days_left < 0) {
                                                    echo '<span class="overdue">' . date('M j, Y', $completion_date) . '</span>';
                                                } elseif ($days_left <= 3) {
                                                    echo '<span class="due-soon">' . date('M j, Y', $completion_date) . '</span>';
                                                } else {
                                                    echo date('M j, Y', $completion_date);
                                                }
                                                ?>
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
                                        <td>
                                            <?php 
                                            $roles = [];
                                            if ($job['preparer_id'] == $user_id) $roles[] = 'Preparer';
                                            if ($job['manager_id'] == $user_id) $roles[] = 'Manager';
                                            if ($job['partner_id'] == $user_id) $roles[] = 'Partner';
                                            echo implode(', ', $roles);
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($job['partner_name'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($job['manager_name'] ?? '-'); ?></td>
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
        .tab-navigation {
            display: flex;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 4px;
            margin-bottom: 2rem;
            overflow-x: auto;
        }

        .tab-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 6px;
            text-decoration: none;
            color: #6c757d;
            font-weight: 500;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .tab-link:hover {
            background: #e9ecef;
            color: #495057;
        }

        .tab-link.active {
            background: #007bff;
            color: white;
        }

        .tab-count {
            background: rgba(255, 255, 255, 0.2);
            color: inherit;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .tab-link.active .tab-count {
            background: rgba(255, 255, 255, 0.3);
        }

        .page-subtitle {
            color: #6c757d;
            margin: 0;
            font-size: 1.1rem;
        }

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

        .no-data {
            color: #6c757d;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .tab-navigation {
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .tab-link {
                padding: 10px 16px;
                font-size: 14px;
            }
        }
    </style>
</body>
</html>
