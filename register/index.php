<?php
session_start();
require_once '../config/database.php';

$message = '';
$messageType = '';

if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $account_type = $_POST['account_type'] ?? '';
    $user_forename = trim($_POST['user_forename'] ?? '');
    $user_surname = trim($_POST['user_surname'] ?? '');
    $user_internal = trim($_POST['user_internal'] ?? '');
    $user_signature = trim($_POST['user_signature'] ?? '');
    
    // Validation
    if (empty($username) || empty($password) || empty($confirm_password) || empty($account_type)) {
        $message = 'Please fill in all fields.';
        $messageType = 'error';
    } elseif (strlen($username) < 3) {
        $message = 'Username must be at least 3 characters long.';
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters long.';
        $messageType = 'error';
    } elseif ($password !== $confirm_password) {
        $message = 'Passwords do not match.';
        $messageType = 'error';
    } elseif (!in_array($account_type, ['Administrator', 'Manager', 'Basic'])) {
        $message = 'Please select a valid account type.';
        $messageType = 'error';
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                $message = 'Username already exists. Please choose a different one.';
                $messageType = 'error';
            } else {
                // Create new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, password, account_type, user_forename, user_surname, user_internal, user_signature, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$username, $hashed_password, $account_type, $user_forename, $user_surname, $user_internal, $user_signature]);
                
                $message = 'Account created successfully! You can now login.';
                $messageType = 'success';
                
                // If admin is creating user, redirect to users page after a short delay
                if (isset($_SESSION['user_id']) && ($_SESSION['account_type'] ?? '') === 'Administrator') {
                    $message = 'User created successfully!';
                    echo '<script>setTimeout(function(){ window.location.href = "../users"; }, 1500);</script>';
                }
                
                // Clear form data
                $username = $password = $confirm_password = $account_type = $user_forename = $user_surname = $user_internal = $user_signature = '';
            }
        } catch (PDOException $e) {
            $message = 'Registration failed. Please try again.';
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
    <title>Create Account - Cursor1</title>
    <link rel="icon" type="image/png" href="../assets/images/RJA-icon Blue.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-user-plus"></i>
                <span>Create New User</span>
            </div>
            <ul class="nav-menu">
                <?php if (isset($_SESSION['user_id']) && ($_SESSION['account_type'] ?? '') === 'Administrator'): ?>
                    <li class="nav-item">
                        <a href="../users" class="nav-link">
                            <i class="fas fa-arrow-left"></i>
                            Back to Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../practice" class="nav-link">Practice Portal</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="../home" class="nav-link">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="../contact" class="nav-link">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a href="../login" class="nav-link">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <div class="register-section">
            <div class="container">
                <div class="register-container">
                    <div class="register-header">
                        <div class="register-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h1 class="register-title">Create New Account</h1>
                        <p class="register-subtitle">Set up your admin credentials</p>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form class="register-form" method="POST" action="">
                        <div class="form-group">
                            <label for="username" class="form-label">
                                <i class="fas fa-user"></i>
                                Username
                            </label>
                            <input type="text" id="username" name="username" class="form-input" 
                                   value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                                   placeholder="Choose a username (min 3 characters)" required>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i>
                                Password
                            </label>
                            <input type="password" id="password" name="password" class="form-input" 
                                   placeholder="Choose a password (min 6 characters)" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password" class="form-label">
                                <i class="fas fa-lock"></i>
                                Confirm Password
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input" 
                                   placeholder="Confirm your password" required>
                        </div>

                        <div class="form-group">
                            <label for="user_forename" class="form-label">
                                <i class="fas fa-user"></i>
                                Forename
                            </label>
                            <input type="text" id="user_forename" name="user_forename" class="form-input" 
                                   value="<?php echo htmlspecialchars($user_forename ?? ''); ?>" 
                                   placeholder="Enter forename">
                        </div>

                        <div class="form-group">
                            <label for="user_surname" class="form-label">
                                <i class="fas fa-user"></i>
                                Surname
                            </label>
                            <input type="text" id="user_surname" name="user_surname" class="form-input" 
                                   value="<?php echo htmlspecialchars($user_surname ?? ''); ?>" 
                                   placeholder="Enter surname">
                        </div>

                        <div class="form-group">
                            <label for="user_internal" class="form-label">
                                <i class="fas fa-building"></i>
                                Internal Name
                            </label>
                            <input type="text" id="user_internal" name="user_internal" class="form-input" 
                                   value="<?php echo htmlspecialchars($user_internal ?? ''); ?>" 
                                   placeholder="Enter internal name">
                        </div>

                        <div class="form-group">
                            <label for="user_signature" class="form-label">
                                <i class="fas fa-signature"></i>
                                Email Signature
                            </label>
                            <input type="text" id="user_signature" name="user_signature" class="form-input" 
                                   value="<?php echo htmlspecialchars($user_signature ?? ''); ?>" 
                                   placeholder="Enter email signature (e.g., Sam)">
                        </div>

                        <div class="form-group">
                            <label for="account_type" class="form-label">
                                <i class="fas fa-user-tag"></i>
                                Account Type
                            </label>
                            <select id="account_type" name="account_type" class="form-input" required>
                                <option value="">Select account type</option>
                                <option value="Basic" <?php echo ($account_type ?? '') === 'Basic' ? 'selected' : ''; ?>>Basic - Limited access to assigned items only</option>
                                <option value="Manager" <?php echo ($account_type ?? '') === 'Manager' ? 'selected' : ''; ?>>Manager - Access to clients and timesheets</option>
                                <option value="Administrator" <?php echo ($account_type ?? '') === 'Administrator' ? 'selected' : ''; ?>>Administrator - Full access to all features</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary btn-register">
                            <i class="fas fa-user-plus"></i>
                            Create Account
                        </button>
                    </form>

                    <?php if (isset($_SESSION['user_id']) && ($_SESSION['account_type'] ?? '') === 'Administrator'): ?>
                        <div class="register-footer">
                            <a href="../users" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                Back to User Management
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="register-footer">
                            <p>Already have an account?</p>
                            <a href="../login" class="btn btn-secondary">
                                <i class="fas fa-sign-in-alt"></i>
                                Sign In
                            </a>
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
