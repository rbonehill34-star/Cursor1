<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in and has Administrator access
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit;
}

$account_type = $_SESSION['account_type'] ?? 'Basic';
if ($account_type !== 'Administrator') {
    header('Location: ../practice');
    exit;
}

$message = '';
$messageType = '';

// Check for success message from delete
if (isset($_GET['message']) && $_GET['message'] === 'deleted') {
    $message = 'User deleted successfully.';
    $messageType = 'success';
}

// Get all users
try {
    $stmt = $pdo->query("SELECT id, username, password, account_type, user_forename, user_surname, user_internal, user_signature, created_at FROM users ORDER BY id ASC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
    $message = 'Failed to load users.';
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>User Management - Cursor1</title>
    <link rel="icon" type="image/png" href="../assets/images/RJA-icon Blue.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-user"></i>
                <span>User Management</span>
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
                    <h1 class="page-title">User Management</h1>
                    <div class="page-actions">
                        <a href="../register" class="btn btn-success">
                            <i class="fas fa-user-plus"></i>
                            New User
                        </a>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3>No users found</h3>
                        <p>There are no users in the system.</p>
                        <a href="../register" class="btn btn-success">
                            <i class="fas fa-user-plus"></i>
                            Create First User
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Internal Name</th>
                                    <th>Username</th>
                                    <th>Account Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr class="user-row" data-user-id="<?php echo $user['id']; ?>" style="cursor: pointer;">
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['user_internal'] ?? ''); ?></strong>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                <span class="badge badge-primary">You</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badge_class = 'badge-secondary';
                                            if ($user['account_type'] === 'Administrator') {
                                                $badge_class = 'badge-danger';
                                            } elseif ($user['account_type'] === 'Manager') {
                                                $badge_class = 'badge-warning';
                                            } else {
                                                $badge_class = 'badge-info';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <?php echo htmlspecialchars($user['account_type']); ?>
                                            </span>
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
        // Add click handlers to user rows
        document.addEventListener('DOMContentLoaded', function() {
            const userRows = document.querySelectorAll('.user-row');
            
            userRows.forEach(function(row) {
                const userId = row.getAttribute('data-user-id');
                
                row.addEventListener('click', function(e) {
                    if (userId) {
                        window.location.href = 'edit.php?id=' + userId;
                    }
                });
            });
        });
    </script>

    <style>
        /* Reduce gap between header and page content */
        .admin-section {
            padding-top: 20px !important;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-primary {
            background-color: #007bff;
            color: white;
            margin-left: 8px;
        }
        
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-info {
            background-color: #17a2b8;
            color: white;
        }
        
        .badge-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .user-row {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        
        .user-row:hover {
            background-color: #f8f9fa;
        }
    </style>
</body>
</html>

