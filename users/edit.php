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
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if user exists
try {
    $stmt = $pdo->prepare("SELECT id, username, account_type, user_forename, user_surname, user_internal, user_signature, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_POST) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'delete') {
            // Handle delete
            if ($user_id == $_SESSION['user_id']) {
                $message = 'You cannot delete your own account.';
                $messageType = 'error';
            } else {
                try {
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    header('Location: index.php?message=deleted');
                    exit;
                } catch (PDOException $e) {
                    $message = 'Failed to delete user. User may have associated records.';
                    $messageType = 'error';
                }
            }
        } elseif ($_POST['action'] === 'save') {
            // Handle save
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $new_account_type = $_POST['account_type'] ?? '';
            $user_forename = trim($_POST['user_forename'] ?? '');
            $user_surname = trim($_POST['user_surname'] ?? '');
            $user_internal = trim($_POST['user_internal'] ?? '');
            $user_signature = trim($_POST['user_signature'] ?? '');
            
            // Validation
            if (empty($username)) {
                $message = 'Username is required.';
                $messageType = 'error';
            } elseif (strlen($username) < 3) {
                $message = 'Username must be at least 3 characters long.';
                $messageType = 'error';
            } elseif (!in_array($new_account_type, ['Administrator', 'Manager', 'Basic'])) {
                $message = 'Please select a valid account type.';
                $messageType = 'error';
            } elseif (!empty($password) && strlen($password) < 6) {
                $message = 'Password must be at least 6 characters long (or leave blank to keep current password).';
                $messageType = 'error';
            } else {
                try {
                    // Check if username is taken by another user
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                    $stmt->execute([$username, $user_id]);
                    
                    if ($stmt->fetch()) {
                        $message = 'Username already exists. Please choose a different one.';
                        $messageType = 'error';
                    } else {
                        // Update user
                        if (!empty($password)) {
                            // Update with new password
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, account_type = ?, user_forename = ?, user_surname = ?, user_internal = ?, user_signature = ? WHERE id = ?");
                            $stmt->execute([$username, $hashed_password, $new_account_type, $user_forename, $user_surname, $user_internal, $user_signature, $user_id]);
                        } else {
                            // Update without changing password
                            $stmt = $pdo->prepare("UPDATE users SET username = ?, account_type = ?, user_forename = ?, user_surname = ?, user_internal = ?, user_signature = ? WHERE id = ?");
                            $stmt->execute([$username, $new_account_type, $user_forename, $user_surname, $user_internal, $user_signature, $user_id]);
                        }
                        
                        // If user changed their own account type, update session
                        if ($user_id == $_SESSION['user_id']) {
                            $_SESSION['account_type'] = $new_account_type;
                            $_SESSION['username'] = $username;
                        }
                        
                        $message = 'User updated successfully!';
                        $messageType = 'success';
                        
                        // Refresh user data
                        $stmt = $pdo->prepare("SELECT id, username, account_type, user_forename, user_surname, user_internal, user_signature, created_at FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $user = $stmt->fetch();
                    }
                } catch (PDOException $e) {
                    $message = 'Failed to update user. Please try again.';
                    $messageType = 'error';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Edit User - Cursor1</title>
    <link rel="icon" type="image/png" href="../assets/images/RJA-icon Blue.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-user-edit"></i>
                <span>Edit User</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-arrow-left"></i>
                        Back to Users
                    </a>
                </li>
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
                    <h1 class="page-title">Edit User</h1>
                    <div class="page-actions">
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Users
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
                    <form method="POST" action="" id="editUserForm">
                        <input type="hidden" name="action" value="save">
                        
                        <div class="form-layout">
                            <!-- Username -->
                            <div class="form-group">
                                <label for="username" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Username *
                                </label>
                                <input type="text" id="username" name="username" class="form-input" 
                                       value="<?php echo htmlspecialchars($user['username']); ?>" 
                                       placeholder="Enter username (min 3 characters)" required>
                            </div>

                            <!-- Password -->
                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock"></i>
                                    New Password (optional)
                                </label>
                                <input type="password" id="password" name="password" class="form-input" 
                                       placeholder="Leave blank to keep current password">
                                <small style="color: #6c757d; font-size: 12px; margin-top: 4px; display: block;">
                                    Only enter a password if you want to change it. Minimum 6 characters.
                                </small>
                            </div>

                            <!-- Forename -->
                            <div class="form-group">
                                <label for="user_forename" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Forename
                                </label>
                                <input type="text" id="user_forename" name="user_forename" class="form-input" 
                                       value="<?php echo htmlspecialchars($user['user_forename'] ?? ''); ?>" 
                                       placeholder="Enter forename">
                            </div>

                            <!-- Surname -->
                            <div class="form-group">
                                <label for="user_surname" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Surname
                                </label>
                                <input type="text" id="user_surname" name="user_surname" class="form-input" 
                                       value="<?php echo htmlspecialchars($user['user_surname'] ?? ''); ?>" 
                                       placeholder="Enter surname">
                            </div>

                            <!-- Internal Name -->
                            <div class="form-group">
                                <label for="user_internal" class="form-label">
                                    <i class="fas fa-building"></i>
                                    Internal Name
                                </label>
                                <input type="text" id="user_internal" name="user_internal" class="form-input" 
                                       value="<?php echo htmlspecialchars($user['user_internal'] ?? ''); ?>" 
                                       placeholder="Enter internal name">
                            </div>

                            <!-- Email Signature -->
                            <div class="form-group">
                                <label for="user_signature" class="form-label">
                                    <i class="fas fa-signature"></i>
                                    Email Signature
                                </label>
                                <input type="text" id="user_signature" name="user_signature" class="form-input" 
                                       value="<?php echo htmlspecialchars($user['user_signature'] ?? ''); ?>" 
                                       placeholder="Enter email signature (e.g., Sam)">
                            </div>

                            <!-- Account Type -->
                            <div class="form-group">
                                <label for="account_type" class="form-label">
                                    <i class="fas fa-user-tag"></i>
                                    Account Type *
                                </label>
                                <select id="account_type" name="account_type" class="form-input" required>
                                    <option value="Basic" <?php echo $user['account_type'] === 'Basic' ? 'selected' : ''; ?>>
                                        Basic - Limited access to assigned items only
                                    </option>
                                    <option value="Manager" <?php echo $user['account_type'] === 'Manager' ? 'selected' : ''; ?>>
                                        Manager - Access to clients and timesheets
                                    </option>
                                    <option value="Administrator" <?php echo $user['account_type'] === 'Administrator' ? 'selected' : ''; ?>>
                                        Administrator - Full access to all features
                                    </option>
                                </select>
                            </div>

                            <!-- User Info -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-info-circle"></i>
                                    User Information
                                </label>
                                <div style="background: #f8f9fa; padding: 12px; border-radius: 4px; font-size: 14px; color: #495057;">
                                    <strong>User ID:</strong> <?php echo $user['id']; ?><br>
                                    <strong>Created:</strong> <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <br><span style="color: #007bff; font-weight: 600;">This is your account</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                            
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancel
                            </a>
                            
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                    <i class="fas fa-trash"></i>
                                    Delete User
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>

                    <!-- Hidden delete form -->
                    <form method="POST" action="" id="deleteForm" style="display: none;">
                        <input type="hidden" name="action" value="delete">
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

    <script>
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                document.getElementById('deleteForm').submit();
            }
        }
    </script>

    <style>
        .form-layout {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .form-actions .btn-danger {
            margin-left: auto;
        }
    </style>
</body>
</html>

