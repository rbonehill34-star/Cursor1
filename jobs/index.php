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

// Handle reminder action
if (isset($_POST['send_reminders'])) {
    error_log("DEBUG - Form submitted with send_reminders");
    if (isset($_POST['selected_jobs'])) {
        error_log("DEBUG - selected_jobs field exists: " . $_POST['selected_jobs']);
    } else {
        error_log("DEBUG - selected_jobs field is missing");
        $message = 'No jobs were selected. Please select at least one job using the checkboxes.';
        $messageType = 'error';
    }
}

if (isset($_POST['send_reminders']) && isset($_POST['selected_jobs'])) {
    // Convert comma-separated string to array if needed
    $selected_jobs = $_POST['selected_jobs'];
    if (is_string($selected_jobs)) {
        $selected_jobs = array_filter(explode(',', $selected_jobs));
    }
    $selected_jobs = array_map('trim', $selected_jobs); // Remove any whitespace
    
    // Debug: Log what we received
    error_log("DEBUG - Selected jobs received: " . print_r($selected_jobs, true));
    
    // Validate that we have job IDs
    if (empty($selected_jobs) || (count($selected_jobs) === 1 && empty($selected_jobs[0]))) {
        $message = 'No jobs were selected. Please select at least one job using the checkboxes.';
        $messageType = 'error';
    } else {
        $reminder_count = 0;
        $errors = [];
        $prepared_emails = [];
    
    foreach ($selected_jobs as $job_id) {
        try {
            // Get job and client details for email
            $stmt = $pdo->prepare("
                SELECT j.*, j.period_end, c.name as client_name, c.email as client_email, c.contact_forename, 
                       CONCAT(COALESCE(c.contact_forename, ''), ' ', COALESCE(c.contact_surname, '')) as client_contact,
                       t.task_name
                FROM jobs j
                LEFT JOIN clients c ON j.client_id = c.id
                LEFT JOIN tasks t ON j.task_id = t.id
                WHERE j.id = ? AND j.state_id = (SELECT id FROM state WHERE state_name = 'Outstanding')
            ");
            $stmt->execute([$job_id]);
            $job = $stmt->fetch();
            
            // Debug: Log job details
            error_log("DEBUG - Processing job ID: $job_id, Found job: " . print_r($job, true));
            
            if ($job && !empty($job['client_email'])) {
                // Get email template based on task name
                $task_name = $job['task_name'] ?? 'Other default';
                
                // Map task names to template names
                $template_mapping = [
                    'Year End' => 'Year End',
                    'VAT returns' => 'VAT returns',
                    'VAT return' => 'VAT returns',
                    'VAT' => 'VAT returns'
                ];
                
                $template_name = $template_mapping[$task_name] ?? 'Other default';
                
                // Get email template
                $stmt = $pdo->prepare("SELECT subject, body FROM email_templates WHERE task_name = ?");
                $stmt->execute([$template_name]);
                $template = $stmt->fetch();
                
                // Use default template if not found
                if (!$template) {
                    $default_templates = [
                        'Year End' => [
                            'subject' => 'Information needed for Accounts for the Period Ended {period_end}',
                            'body' => "Hi {contact_forename}\n\nPlease can you send the data for the accounts as soon as possible.\n\nKind regards\nRob"
                        ],
                        'VAT returns' => [
                            'subject' => 'Information needed for VAT Return for the Period Ended {period_end}',
                            'body' => "Hi {contact_forename}\n\nPlease can you send the data for the VAT return as soon as possible.\n\nKind regards\nRob"
                        ],
                        'Other default' => [
                            'subject' => 'Information needed for {task_name} for the Period Ended {period_end}',
                            'body' => "Hi {contact_forename}\n\nPlease can you send the data for the {task_name} as soon as possible.\n\nKind regards\nRob"
                        ]
                    ];
                    $template = $default_templates[$template_name] ?? $default_templates['Other default'];
                }
                
                // Prepare email content with placeholders
                $period_end_date = $job['period_end'] ? date('d/m/Y', strtotime($job['period_end'])) : 'TBD';
                $contact_forename = $job['contact_forename'] ?: 'there';
                
                // Replace placeholders in subject and body
                $subject = str_replace(
                    ['{period_end}', '{contact_forename}', '{task_name}'],
                    [$period_end_date, $contact_forename, $task_name],
                    $template['subject']
                );
                
                $body = str_replace(
                    ['{period_end}', '{contact_forename}', '{task_name}'],
                    [$period_end_date, $contact_forename, $task_name],
                    $template['body']
                );
                
                // Store email details for display
                $prepared_emails[] = [
                    'client_name' => $job['client_name'],
                    'client_email' => $job['client_email'],
                    'subject' => $subject,
                    'body' => $body
                ];
                
                $reminder_count++;
            } else {
                if (!$job) {
                    $errors[] = "Job ID $job_id not found or not in Outstanding state";
                } elseif (empty($job['client_email'])) {
                    $errors[] = "No email address for client: " . ($job['client_name'] ?? 'Unknown');
                } else {
                    $errors[] = "Unknown error processing job ID: $job_id";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Failed to process job ID: " . $job_id;
        }
    }
    
        if ($reminder_count > 0) {
            // Store prepared emails in session for display
            $_SESSION['prepared_emails'] = $prepared_emails;
            
            $message = $reminder_count . " reminder(s) prepared successfully.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }
            $messageType = 'success';
            $showEmailLink = true;
        } else {
            $message = 'No reminders could be prepared. ' . implode(', ', $errors);
            $messageType = 'error';
        }
    }
}

// Function to get jobs based on tab
function getJobsByTab($pdo, $tab) {
    $base_query = "
        SELECT j.*, 
               c.name as client_name, 
               c.reference as client_reference,
               c.email as client_email,
               CONCAT(COALESCE(c.contact_forename, ''), ' ', COALESCE(c.contact_surname, '')) as client_contact,
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
                        <?php if (isset($showEmailLink) && $showEmailLink): ?>
                            <br><br>
                            <a href="#" onclick="showEmails(); return false;" style="color: #007bff; text-decoration: underline; font-weight: bold;">
                                <i class="fas fa-envelope"></i> Click here to view the prepared emails
                            </a>
                        <?php endif; ?>
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

                    <?php if ($active_tab === 'outstanding'): ?>
                        <!-- Reminder Button (hidden by default) -->
                        <div id="reminderButtonContainer" style="margin-bottom: 20px; display: none;">
                            <form id="reminderForm" method="POST">
                                <button type="submit" name="send_reminders" class="btn btn-warning" id="sendReminderBtn">
                                    <i class="fas fa-envelope"></i>
                                    Send reminder
                                </button>
                                <input type="hidden" name="selected_jobs" id="selectedJobsInput" value="">
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- Email Preview Modal -->
                    <?php if (isset($_SESSION['prepared_emails']) && !empty($_SESSION['prepared_emails'])): ?>
                        <div id="emailPreviewModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 8px; max-width: 800px; max-height: 80%; overflow-y: auto;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                    <h2>Prepared Reminder Emails</h2>
                                    <button type="button" id="closeModalBtn" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
                                </div>
                                
                                <?php foreach ($_SESSION['prepared_emails'] as $index => $email): ?>
                                    <div style="border: 1px solid #ddd; margin-bottom: 20px; padding: 20px; border-radius: 4px;">
                                        <h3>Email <?php echo $index + 1; ?>: <?php echo htmlspecialchars($email['client_name']); ?></h3>
                                        <p><strong>To:</strong> <?php echo htmlspecialchars($email['client_email']); ?></p>
                                        <p><strong>Subject:</strong> <?php echo htmlspecialchars($email['subject']); ?></p>
                                        <div style="margin-top: 15px;">
                                            <strong>Message:</strong>
                                            <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-top: 10px; white-space: pre-line; border-left: 4px solid #007bff;"><?php echo htmlspecialchars($email['body']); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div style="margin-top: 20px; text-align: right;">
                                    <button type="button" id="closeModalBtn2" class="btn btn-primary">Close</button>
                                </div>
                            </div>
                        </div>
                        
                        <?php 
                        // Clear the prepared emails from session after displaying
                        unset($_SESSION['prepared_emails']);
                        ?>
                    <?php endif; ?>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <?php if ($active_tab === 'outstanding'): ?>
                                        <th style="width: 50px;">
                                            <input type="checkbox" id="selectAllCheckbox" title="Select All">
                                        </th>
                                    <?php endif; ?>
                                    <th>Client</th>
                                    <th>Task</th>
                                    <th>Completion</th>
                                </tr>
                            </thead>
                            <tbody id="jobsTableBody">
                                <?php foreach ($jobs as $job): ?>
                                    <?php $jobId = isset($job['id']) ? $job['id'] : 0; ?>
                                    <tr class="job-row" data-job-id="<?php echo $jobId; ?>" style="cursor: pointer;">
                                        <?php if ($active_tab === 'outstanding'): ?>
                                            <td>
                                                <input type="checkbox" class="job-checkbox" data-job-id="<?php echo $jobId; ?>" 
                                                       data-client-email="<?php echo htmlspecialchars($job['client_email'] ?? ''); ?>"
                                                       data-client-contact="<?php echo htmlspecialchars($job['client_contact'] ?? ''); ?>"
                                                       data-deadline-date="<?php echo htmlspecialchars($job['deadline_date'] ?? ''); ?>">
                                            </td>
                                        <?php endif; ?>
                                        <td>
                                            <strong title="<?php echo htmlspecialchars($job['client_name']); ?>">
                                                <?php 
                                                $clientName = $job['client_name'];
                                                echo htmlspecialchars(strlen($clientName) > 20 ? substr($clientName, 0, 20) . '...' : $clientName);
                                                ?>
                                            </strong>
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
                    // Don't navigate if clicking on checkbox
                    if (e.target.type === 'checkbox') {
                        return;
                    }
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
                        // Adjust cell indices based on whether checkboxes are present
                        const cellOffset = document.querySelector('.job-checkbox') ? 1 : 0;
                        const clientName = row.cells[cellOffset].textContent.toLowerCase();
                        const taskName = row.cells[cellOffset + 1].textContent.toLowerCase();
                        
                        if (clientName.includes(searchTerm) || taskName.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Reminder functionality (only for outstanding tab)
            const selectAllCheckbox = document.getElementById('selectAllCheckbox');
            const jobCheckboxes = document.querySelectorAll('.job-checkbox');
            const reminderButtonContainer = document.getElementById('reminderButtonContainer');
            const selectedJobsInput = document.getElementById('selectedJobsInput');
            const reminderForm = document.getElementById('reminderForm');
            
            if (selectAllCheckbox && jobCheckboxes.length > 0) {
                // Select All functionality
                selectAllCheckbox.addEventListener('change', function() {
                    jobCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateReminderButton();
                });
                
                // Individual checkbox functionality
                jobCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        updateReminderButton();
                        updateSelectAllState();
                    });
                });
                
                function updateReminderButton() {
                    const checkedBoxes = document.querySelectorAll('.job-checkbox:checked');
                    const selectedJobIds = Array.from(checkedBoxes).map(cb => cb.dataset.jobId);
                    
                    console.log('DEBUG - Checked boxes:', checkedBoxes.length);
                    console.log('DEBUG - Selected job IDs:', selectedJobIds);
                    
                    if (selectedJobIds.length > 0) {
                        reminderButtonContainer.style.display = 'block';
                        selectedJobsInput.value = selectedJobIds.join(',');
                        console.log('DEBUG - Setting input value to:', selectedJobsInput.value);
                    } else {
                        reminderButtonContainer.style.display = 'none';
                        selectedJobsInput.value = '';
                    }
                }
                
                function updateSelectAllState() {
                    const totalCheckboxes = jobCheckboxes.length;
                    const checkedCheckboxes = document.querySelectorAll('.job-checkbox:checked').length;
                    
                    if (checkedCheckboxes === 0) {
                        selectAllCheckbox.indeterminate = false;
                        selectAllCheckbox.checked = false;
                    } else if (checkedCheckboxes === totalCheckboxes) {
                        selectAllCheckbox.indeterminate = false;
                        selectAllCheckbox.checked = true;
                    } else {
                        selectAllCheckbox.indeterminate = true;
                    }
                }
            }
            
            // Add form submission debugging
            if (reminderForm) {
                reminderForm.addEventListener('submit', function(e) {
                    console.log('DEBUG - Form submitting with selected_jobs value:', selectedJobsInput.value);
                    if (!selectedJobsInput.value || selectedJobsInput.value.trim() === '') {
                        e.preventDefault();
                        alert('No jobs selected. Please select at least one job.');
                        return false;
                    }
                });
            }
        });
        
        // Function to show emails (called from success message link)
        function showEmails() {
            const modal = document.getElementById('emailPreviewModal');
            if (modal) {
                modal.style.display = 'block';
            }
        }
        
        // Modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const emailPreviewModal = document.getElementById('emailPreviewModal');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const closeModalBtn2 = document.getElementById('closeModalBtn2');
            
            if (emailPreviewModal) {
                function closeModal() {
                    emailPreviewModal.style.display = 'none';
                }
                
                if (closeModalBtn) {
                    closeModalBtn.addEventListener('click', closeModal);
                }
                
                if (closeModalBtn2) {
                    closeModalBtn2.addEventListener('click', closeModal);
                }
                
                // Close modal when clicking outside
                emailPreviewModal.addEventListener('click', function(e) {
                    if (e.target === emailPreviewModal) {
                        closeModal();
                    }
                });
                
                // Close modal with Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && emailPreviewModal.style.display === 'block') {
                        closeModal();
                    }
                });
            }
        });
    </script>

    <style>
        /* Reduce gap between header and page title */
        .admin-section {
            padding-top: 20px !important;
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
        
        /* Reminder functionality styles */
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
            color: #212529;
        }
        
        #reminderButtonContainer {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
        }
        
        .job-checkbox {
            cursor: pointer;
        }
        
        .job-checkbox:checked {
            background-color: #007bff;
        }
        
        /* Prevent row click when clicking checkbox */
        .job-row td:first-child {
            cursor: default;
        }
        
        .job-row td:first-child input {
            cursor: pointer;
        }
        
        /* Client column optimization - minimal width for 20-char names */
        .data-table th:nth-child(2),
        .data-table td:nth-child(2) {
            max-width: 120px;
            min-width: 110px;
            width: 110px;
        }
        
        .data-table th:nth-child(3),
        .data-table td:nth-child(3) {
            max-width: 100px;
            min-width: 80px;
            width: 100px;
        }
        
        .data-table th:nth-child(4),
        .data-table td:nth-child(4) {
            max-width: 80px;
            min-width: 70px;
            width: 80px;
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
            
            /* Mobile column optimizations */
            .data-table th:nth-child(2),
            .data-table td:nth-child(2) {
                max-width: 90px;
                min-width: 80px;
                width: 80px;
            }
            
            .data-table th:nth-child(3),
            .data-table td:nth-child(3) {
                max-width: 80px;
                min-width: 60px;
                width: 80px;
            }
            
            .data-table th:nth-child(4),
            .data-table td:nth-child(4) {
                max-width: 60px;
                min-width: 50px;
                width: 60px;
            }
        }
        
        @media (max-width: 480px) {
            /* Extra small mobile optimizations */
            .data-table th:nth-child(2),
            .data-table td:nth-child(2) {
                max-width: 70px;
                min-width: 60px;
                width: 60px;
            }
            
            .data-table th:nth-child(3),
            .data-table td:nth-child(3) {
                max-width: 70px;
                min-width: 50px;
                width: 70px;
            }
            
            .data-table th:nth-child(4),
            .data-table td:nth-child(4) {
                max-width: 50px;
                min-width: 40px;
                width: 50px;
            }
        }
    </style>
</body>
</html>




