<?php
require_once '../config/database.php';

$message = '';
$messageType = '';

if ($_POST) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    // Validation
    if (empty($username) || empty($password) || empty($confirm_password)) {
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
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM login WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                $message = 'Username already exists. Please choose a different one.';
                $messageType = 'error';
            } else {
                // Create new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO login (username, password, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$username, $hashed_password]);
                
                $message = 'Account created successfully! You can now login.';
                $messageType = 'success';
                
                // Clear form data
                $username = $password = $confirm_password = '';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - Cursor1</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-database"></i>
                <span>Cursor1</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../home" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="../contact" class="nav-link">Contact</a>
                </li>
                <li class="nav-item">
                    <a href="../login" class="nav-link">Login</a>
                </li>
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

                        <button type="submit" class="btn btn-primary btn-register">
                            <i class="fas fa-user-plus"></i>
                            Create Account
                        </button>
                    </form>

                    <div class="register-footer">
                        <p>Already have an account?</p>
                        <a href="../login" class="btn btn-secondary">
                            <i class="fas fa-sign-in-alt"></i>
                            Sign In
                        </a>
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
</body>
</html>
