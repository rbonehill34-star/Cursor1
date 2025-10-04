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

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        // Check if task is being used in timesheet
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM timesheet WHERE task_id = ?");
        $stmt->execute([$_GET['delete']]);
        $usage = $stmt->fetch();
        
        if ($usage['count'] > 0) {
            $message = 'Cannot delete task - it is being used in timesheet entries.';
            $messageType = 'error';
        } else {
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$_GET['delete']]);
            $message = 'Task deleted successfully.';
            $messageType = 'success';
        }
    } catch (PDOException $e) {
        $message = 'Failed to delete task.';
        $messageType = 'error';
    }
}

// Get all tasks
try {
    $stmt = $pdo->query("SELECT t.*, COUNT(ts.id) as usage_count FROM tasks t LEFT JOIN timesheet ts ON t.id = ts.task_id GROUP BY t.id ORDER BY t.task_name ASC");
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    $tasks = [];
    $message = 'Failed to load tasks.';
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - Cursor1</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-tasks"></i>
                <span>Tasks</span>
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
                    <h1 class="page-title">Task Management</h1>
                    <div class="page-actions">
                        <a href="add" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Add New Task
                        </a>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($tasks)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h3>No tasks yet</h3>
                        <p>Get started by adding your first task.</p>
                        <a href="add" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Add First Task
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Task Name</th>
                                    <th>Description</th>
                                    <th>Timesheet Usage</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks as $task): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($task['task_name']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($task['description'] ?? '-'); ?></td>
                                        <td>
                                            <span class="usage-badge">
                                                <i class="fas fa-clock"></i>
                                                <?php echo $task['usage_count']; ?> entries
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($task['created_at'])); ?></td>
                                        <td>
                                            <a href="edit?id=<?php echo $task['id']; ?>" class="btn btn-action btn-edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($task['usage_count'] == 0): ?>
                                                <a href="?delete=<?php echo $task['id']; ?>" 
                                                   class="btn btn-action btn-delete"
                                                   onclick="return confirm('Are you sure you want to delete this task?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="btn btn-action" style="background: #6c757d; cursor: not-allowed;" title="Cannot delete - task in use">
                                                    <i class="fas fa-lock"></i>
                                                </span>
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
</body>
</html>
