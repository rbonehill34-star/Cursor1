<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit;
}

$message = '';
$messageType = '';

if ($_POST) {
    $task_name = trim($_POST['task_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validation
    if (empty($task_name)) {
        $message = 'Task name is required.';
        $messageType = 'error';
    } else {
        try {
            // Check if task name already exists
            $stmt = $pdo->prepare("SELECT id FROM tasks WHERE task_name = ?");
            $stmt->execute([$task_name]);
            
            if ($stmt->fetch()) {
                $message = 'Task name already exists. Please choose a different one.';
                $messageType = 'error';
            } else {
                // Insert new task
                $stmt = $pdo->prepare("INSERT INTO tasks (task_name, description, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$task_name, $description ?: null]);
                
                $message = 'Task added successfully!';
                $messageType = 'success';
                
                // Clear form data
                $task_name = $description = '';
            }
        } catch (PDOException $e) {
            $message = 'Failed to add task. Please try again.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Add Task - Cursor1</title>
    <link rel="icon" type="image/png" href="../assets/images/RJA-icon Blue.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-plus"></i>
                <span>Add Task</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../practice" class="nav-link">Practice Portal</a>
                </li>
                <li class="nav-item">
                    <a href="index" class="nav-link">Back to Tasks</a>
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
                    <h1 class="page-title">Add New Task</h1>
                    <div class="page-actions">
                        <a href="index" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Tasks
                        </a>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div class="form-section">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="task_name" class="form-label">
                                <i class="fas fa-tasks"></i>
                                Task Name *
                            </label>
                            <input type="text" id="task_name" name="task_name" class="form-input" 
                                   value="<?php echo htmlspecialchars($task_name ?? ''); ?>" 
                                   placeholder="e.g., Bookkeeping, VAT Returns, Payroll" required>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left"></i>
                                Description
                            </label>
                            <textarea id="description" name="description" class="form-textarea" rows="4" 
                                      placeholder="Optional description of the task"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Add Task
                            </button>
                            <a href="index" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancel
                            </a>
                        </div>
                    </form>
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
