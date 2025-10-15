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

$message = '';
$messageType = '';

// Handle form submission for updating email templates
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'update_template') {
    $task_name = $_POST['task_name'] ?? '';
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');
    
    if (empty($task_name) || empty($subject) || empty($body)) {
        $message = 'All fields are required.';
        $messageType = 'error';
    } else {
        try {
            // Check if template exists
            $stmt = $pdo->prepare("SELECT id FROM email_templates WHERE task_name = ?");
            $stmt->execute([$task_name]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing template
                $stmt = $pdo->prepare("UPDATE email_templates SET subject = ?, body = ?, updated_at = NOW() WHERE task_name = ?");
                $stmt->execute([$subject, $body, $task_name]);
            } else {
                // Insert new template
                $stmt = $pdo->prepare("INSERT INTO email_templates (task_name, subject, body, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmt->execute([$task_name, $subject, $body]);
            }
            
            $message = 'Email template updated successfully!';
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Failed to update email template. Please try again.';
            $messageType = 'error';
        }
    }
}

// Get selected task name from URL or default to 'Year End'
$selected_task = $_GET['task'] ?? 'Year End';

// Get available task names
$available_tasks = ['Year End', 'VAT returns', 'Other default'];

// Get current template for selected task
$current_template = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM email_templates WHERE task_name = ?");
    $stmt->execute([$selected_task]);
    $current_template = $stmt->fetch();
} catch (PDOException $e) {
    // Template doesn't exist yet, will use defaults
}

// Set default templates if they don't exist
$default_templates = [
    'Year End' => [
        'subject' => 'Information needed for Accounts for the Period Ended {period_end}',
        'body' => "Hi {contact_forename}\n\nPlease can you send the data for the accounts as soon as possible.\n\nThe deadline for submission is {deadline_date}.\n\nKind regards\n{user_signature}\n{username}"
    ],
    'VAT returns' => [
        'subject' => 'Information needed for VAT Return for the Period Ended {period_end}',
        'body' => "Hi {contact_forename}\n\nPlease can you send the data for the VAT return as soon as possible.\n\nThe deadline for submission is {deadline_date}.\n\nKind regards\n{user_signature}\n{username}"
    ],
    'Other default' => [
        'subject' => 'Information needed for {task_name} for the Period Ended {period_end}',
        'body' => "Hi {contact_forename}\n\nPlease can you send the data for the {task_name} as soon as possible.\n\nThe deadline for submission is {deadline_date}.\n\nKind regards\n{user_signature}\n{username}"
    ]
];

// Use current template or default
$template_subject = $current_template['subject'] ?? $default_templates[$selected_task]['subject'];
$template_body = $current_template['body'] ?? $default_templates[$selected_task]['body'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Email Templates - Cursor1</title>
    <link rel="icon" type="image/png" href="../assets/images/RJA-icon Blue.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-envelope"></i>
                <span>Email Templates</span>
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
                    <h1 class="practice-title">Email Templates</h1>
                    <p class="practice-subtitle">Design and customize email templates for different task types</p>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Task Selection Dropdown -->
                <div class="email-template-selector">
                    <div class="form-group">
                        <label for="task-selector">Select Task Type:</label>
                        <select id="task-selector" class="form-control" onchange="changeTask(this.value)">
                            <?php foreach ($available_tasks as $task): ?>
                                <option value="<?php echo htmlspecialchars($task); ?>" <?php echo $selected_task === $task ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($task); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Email Template Editor -->
                <div class="email-template-editor">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_template">
                        <input type="hidden" name="task_name" value="<?php echo htmlspecialchars($selected_task); ?>">
                        
                        <div class="form-group">
                            <label for="subject">Email Subject:</label>
                            <input type="text" id="subject" name="subject" class="form-control" 
                                   value="<?php echo htmlspecialchars($template_subject); ?>" required>
                            <small class="form-text">Use {period_end}, {contact_forename}, {task_name}, {user_signature}, {username}, {deadline_date} as placeholders</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="body">Email Body:</label>
                            <textarea id="body" name="body" class="form-control" rows="8" required><?php echo htmlspecialchars($template_body); ?></textarea>
                            <small class="form-text">Use {period_end}, {contact_forename}, {task_name}, {user_signature}, {username}, {deadline_date} as placeholders</small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Template
                            </button>
                            <a href="../settings" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                Back to Settings
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Template Preview -->
                <div class="email-preview">
                    <h3>Preview</h3>
                    <div class="preview-box">
                        <div class="preview-header">
                            <strong>Subject:</strong> <span id="preview-subject"><?php echo htmlspecialchars($template_subject); ?></span>
                        </div>
                        <div class="preview-body">
                            <pre id="preview-body"><?php echo htmlspecialchars($template_body); ?></pre>
                        </div>
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

    <script>
        function changeTask(taskName) {
            window.location.href = '?task=' + encodeURIComponent(taskName);
        }
        
        // Update preview in real-time
        document.getElementById('subject').addEventListener('input', function() {
            document.getElementById('preview-subject').textContent = this.value;
        });
        
        document.getElementById('body').addEventListener('input', function() {
            document.getElementById('preview-body').textContent = this.value;
        });
    </script>

    <style>
        /* Reduce gap between header and page content */
        .practice-section {
            padding-top: 20px !important;
        }
        
        .email-template-selector {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            margin-bottom: 20px;
        }
        
        .email-template-editor {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            margin-bottom: 20px;
        }
        
        .email-preview {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }
        
        .preview-box {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            background: #f8f9fa;
        }
        
        .preview-header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .preview-body pre {
            margin: 0;
            white-space: pre-wrap;
            font-family: inherit;
            line-height: 1.5;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }
            
            .email-template-selector,
            .email-template-editor,
            .email-preview {
                padding: 15px;
            }
        }
    </style>
</body>
</html>
