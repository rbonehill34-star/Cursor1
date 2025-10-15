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
$task = null;

// Get task ID from URL
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$task_id) {
    header('Location: index');
    exit;
}

// Get existing task data
try {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->execute([$task_id]);
    $task = $stmt->fetch();
    
    if (!$task) {
        header('Location: index');
        exit;
    }
    
    // Check if task is being used in timesheet
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM timesheet WHERE task_id = ?");
    $stmt->execute([$task_id]);
    $usage = $stmt->fetch();
    $can_delete = $usage['count'] == 0;
} catch (PDOException $e) {
    header('Location: index');
    exit;
}

// Handle delete action
if (isset($_POST['delete_task'])) {
    if ($can_delete) {
        try {
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$task_id]);
            header('Location: index?deleted=1');
            exit;
        } catch (PDOException $e) {
            $message = 'Failed to delete task.';
            $messageType = 'error';
        }
    } else {
        $message = 'Cannot delete task - it is being used in timesheet entries.';
        $messageType = 'error';
    }
}

// Initialize form data with existing task data
$task_name = $task['task_name'];
$task_order = $task['task_order'];
$description = $task['description'];

if ($_POST) {
    $task_name = trim($_POST['task_name'] ?? '');
    $task_order = (int)($_POST['task_order'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    
    // Validation
    if (empty($task_name)) {
        $message = 'Task name is required.';
        $messageType = 'error';
    } else {
        try {
            // Check if task name already exists (excluding current task)
            $stmt = $pdo->prepare("SELECT id FROM tasks WHERE task_name = ? AND id != ?");
            $stmt->execute([$task_name, $task_id]);
            
            if ($stmt->fetch()) {
                $message = 'Task name already exists. Please choose a different one.';
                $messageType = 'error';
            } else {
                // Update task
                $stmt = $pdo->prepare("UPDATE tasks SET task_name = ?, task_order = ?, description = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$task_name, $task_order, $description ?: null, $task_id]);
                
                $message = 'Task updated successfully!';
                $messageType = 'success';
                
                // Update local task data
                $task['task_name'] = $task_name;
                $task['task_order'] = $task_order;
                $task['description'] = $description;
            }
        } catch (PDOException $e) {
            $message = 'Failed to update task. Please try again.';
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
    <title>Edit Task - Cursor1</title>
    <link rel="icon" type="image/png" href="../assets/images/RJA-icon Blue.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-edit"></i>
                <span>Edit Task</span>
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
                    <h1 class="page-title">Edit Task</h1>
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
                                   value="<?php echo htmlspecialchars($task_name); ?>" 
                                   placeholder="e.g., Bookkeeping, VAT Returns, Payroll" required>
                        </div>

                        <div class="form-group">
                            <label for="task_order" class="form-label">
                                <i class="fas fa-sort-numeric-up"></i>
                                Task Order
                            </label>
                            <input type="number" id="task_order" name="task_order" class="form-input" 
                                   value="<?php echo htmlspecialchars($task_order); ?>" 
                                   placeholder="Enter order number (e.g., 1, 2, 3...)" min="0">
                            <small class="form-help">Lower numbers appear first in lists and dropdowns</small>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left"></i>
                                Description
                            </label>
                            <textarea id="description" name="description" class="form-textarea" rows="4" 
                                      placeholder="Optional description of the task"><?php echo htmlspecialchars($description); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                            <a href="index" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancel
                            </a>
                        </div>
                    </form>
                    
                    <?php if ($can_delete): ?>
                        <div class="danger-section" style="margin-top: 30px; padding: 20px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px;">
                            <h3 style="color: #dc2626; margin-bottom: 10px; font-size: 1.1rem;">
                                <i class="fas fa-exclamation-triangle"></i>
                                Danger Zone
                            </h3>
                            <p style="margin-bottom: 15px; color: #6b7280;">
                                Deleting this task will permanently remove it from the system. This action cannot be undone.
                            </p>
                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this task? This action cannot be undone.');">
                                <button type="submit" name="delete_task" class="btn btn-delete" style="background: #dc2626;">
                                    <i class="fas fa-trash"></i>
                                    Delete Task
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="info-section" style="margin-top: 30px; padding: 20px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px;">
                            <h3 style="color: #0369a1; margin-bottom: 10px; font-size: 1.1rem;">
                                <i class="fas fa-info-circle"></i>
                                Task in Use
                            </h3>
                            <p style="margin-bottom: 0; color: #6b7280;">
                                This task cannot be deleted because it is being used in timesheet entries.
                            </p>
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
