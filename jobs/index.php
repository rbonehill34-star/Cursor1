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

// Get the active tab from URL parameter, default to 'all'
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';

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

// Function to get jobs based on tab
function getJobsByTab($pdo, $tab) {
    $base_query = "
        SELECT j.*, 
               c.name as client_name, 
               c.reference as client_reference,
               t.task_name,
               s.state_name
        FROM jobs j
        LEFT JOIN clients c ON j.client_id = c.id
        LEFT JOIN tasks t ON j.task_id = t.id
        LEFT JOIN state s ON j.state_id = s.id
        WHERE s.state_name != 'Archived'
    ";
    
    // Add state filter based on tab
    switch ($tab) {
        case 'all':
            // Exclude completed and archived jobs
            $base_query .= " AND s.state_name != 'Completed'";
            break;
        case 'received':
            $base_query .= " AND s.state_name = 'Received'";
            break;
        case 'outstanding':
            $base_query .= " AND s.state_name = 'Outstanding'";
            break;
        default:
            return [];
    }
    
    // Sort by completion date, latest first
    $base_query .= " ORDER BY j.expected_completion_date DESC, j.created_at DESC";
    
    try {
        $stmt = $pdo->query($base_query);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

// Get jobs for the active tab
$jobs = getJobsByTab($pdo, $active_tab);

// Define available tabs
$available_tabs = [
    'all' => 'All Jobs',
    'received' => 'Received',
    'outstanding' => 'Outstanding'
];
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

                <!-- Tab Navigation -->
                <div class="tabs-container">
                    <div class="tabs">
                        <?php foreach ($available_tabs as $tab_key => $tab_label): ?>
                            <a href="?tab=<?php echo $tab_key; ?>" 
                               class="tab <?php echo $active_tab === $tab_key ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars($tab_label); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if (empty($jobs)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <h3>No jobs in this category</h3>
                        <p>You don't have any jobs in the "<?php echo htmlspecialchars($available_tabs[$active_tab]); ?>" category.</p>
                        <a href="add" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Create First Job
                        </a>
                    </div>
                <?php else: ?>
                    <div class="search-container" style="margin-bottom: 20px;">
                        <div class="form-group">
                            <label for="jobSearch" class="form-label">
                                <i class="fas fa-search"></i>
                                Search Jobs
                            </label>
                            <input type="text" id="jobSearch" class="form-input" 
                                   placeholder="Type to search jobs by client name..." 
                                   autocomplete="off">
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Task</th>
                                    <th>Completion</th>
                                </tr>
                            </thead>
                            <tbody id="jobsTableBody">
                                <?php foreach ($jobs as $job): ?>
                                    <?php $jobId = isset($job['id']) ? $job['id'] : 0; ?>
                                    <tr class="job-row" data-job-id="<?php echo $jobId; ?>" style="cursor: pointer;">
                                        <td>
                                            <strong><?php echo htmlspecialchars($job['client_name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($job['task_name']); ?></td>
                                        <td>
                                            <?php if ($job['expected_completion_date']): ?>
                                                <?php 
                                                $completion_date = strtotime($job['expected_completion_date']);
                                                $today = time();
                                                $days_left = ceil(($completion_date - $today) / (60 * 60 * 24));
                                                
                                                if ($days_left < 0) {
                                                    echo '<span class="overdue">' . date('d/m/y', $completion_date) . '</span>';
                                                } elseif ($days_left <= 3) {
                                                    echo '<span class="due-soon">' . date('d/m/y', $completion_date) . '</span>';
                                                } else {
                                                    echo date('d/m/y', $completion_date);
                                                }
                                                ?>
                                            <?php else: ?>
                                                <span class="no-data">-</span>
                                            <?php endif; ?>
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

    <script>
        // Add click handlers to job rows
        document.addEventListener('DOMContentLoaded', function() {
            const jobRows = document.querySelectorAll('.job-row');
            
            jobRows.forEach(function(row) {
                const jobId = row.getAttribute('data-job-id');
                
                row.addEventListener('click', function(e) {
                    if (jobId && jobId !== '0') {
                        window.location.href = 'add.php?id=' + jobId;
                    }
                });
            });

            // Search functionality
            const searchInput = document.getElementById('jobSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const tableBody = document.getElementById('jobsTableBody');
                    const rows = tableBody.getElementsByTagName('tr');

                    Array.from(rows).forEach(row => {
                        const clientName = row.cells[0].textContent.toLowerCase();
                        const taskName = row.cells[1].textContent.toLowerCase();
                        
                        if (clientName.includes(searchTerm) || taskName.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>

    <style>
        /* Reduce gap between header and page title */
        .admin-section {
            padding-top: 40px !important;
        }
        
        .tabs-container {
            margin: 20px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .tabs {
            display: flex;
            gap: 0;
            margin-bottom: -1px;
        }
        
        .tab {
            padding: 12px 20px;
            text-decoration: none;
            color: #6c757d;
            border: 1px solid transparent;
            border-bottom: 1px solid #e9ecef;
            background: #f8f9fa;
            transition: all 0.2s ease;
            font-weight: 500;
            border-radius: 4px 4px 0 0;
            margin-right: 2px;
        }
        
        .tab:hover {
            color: #495057;
            background: #e9ecef;
            text-decoration: none;
        }
        
        .tab.active {
            color: #007bff;
            background: white;
            border-color: #e9ecef;
            border-bottom-color: white;
            font-weight: 600;
        }
        
        .job-row {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        .job-row:hover {
            background-color: #f8f9fa;
        }
        
        .overdue {
            color: #dc3545;
            font-weight: bold;
        }
        
        .due-soon {
            color: #fd7e14;
            font-weight: bold;
        }
        
        .no-data {
            color: #6c757d;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .tabs {
                flex-wrap: wrap;
            }
            
            .tab {
                flex: 1;
                text-align: center;
                min-width: 80px;
            }
        }
    </style>
</body>
</html>




