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

if ($_POST) {
    $reference = trim($_POST['reference'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $year_end = $_POST['year_end'] ?? '';
    
    // Validation
    if (empty($reference) || empty($name)) {
        $message = 'Reference and name are required fields.';
        $messageType = 'error';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        try {
            // Check if reference already exists
            $stmt = $pdo->prepare("SELECT id FROM clients WHERE reference = ?");
            $stmt->execute([$reference]);
            
            if ($stmt->fetch()) {
                $message = 'Reference already exists. Please choose a different one.';
                $messageType = 'error';
            } else {
                // Insert new client
                $stmt = $pdo->prepare("INSERT INTO clients (reference, name, contact, email, phone, year_end, date_added) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$reference, $name, $contact, $email, $phone, $year_end ?: null]);
                
                $message = 'Client added successfully!';
                $messageType = 'success';
                
                // Clear form data
                $reference = $name = $contact = $email = $phone = $year_end = '';
            }
        } catch (PDOException $e) {
            $message = 'Failed to add client. Please try again.';
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
    <title>Add Client - Cursor1</title>
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
                <span>Add Client</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../practice" class="nav-link">Practice Portal</a>
                </li>
                <li class="nav-item">
                    <a href="index" class="nav-link">Back to Clients</a>
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
                    <h1 class="page-title">Add New Client</h1>
                    <div class="page-actions">
                        <a href="index" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Clients
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
                        <div class="form-layout">
                            <div class="form-group">
                                <label for="reference" class="form-label">
                                    <i class="fas fa-hashtag"></i>
                                    Reference *
                                </label>
                                <input type="text" id="reference" name="reference" class="form-input" 
                                       value="<?php echo htmlspecialchars($reference ?? ''); ?>" 
                                       placeholder="e.g., CLI001" required>
                            </div>

                            <div class="form-group">
                                <label for="name" class="form-label">
                                    <i class="fas fa-building"></i>
                                    Company Name *
                                </label>
                                <input type="text" id="name" name="name" class="form-input" 
                                       value="<?php echo htmlspecialchars($name ?? ''); ?>" 
                                       placeholder="Enter company name" required>
                            </div>

                            <div class="form-group">
                                <label for="contact" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Contact Person
                                </label>
                                <input type="text" id="contact" name="contact" class="form-input" 
                                       value="<?php echo htmlspecialchars($contact ?? ''); ?>" 
                                       placeholder="Contact person name">
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address
                                </label>
                                <input type="email" id="email" name="email" class="form-input" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                                       placeholder="contact@company.com">
                            </div>

                            <div class="form-group">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone"></i>
                                    Phone Number
                                </label>
                                <input type="tel" id="phone" name="phone" class="form-input" 
                                       value="<?php echo htmlspecialchars($phone ?? ''); ?>" 
                                       placeholder="+44 1234 567890">
                            </div>

                            <div class="form-group">
                                <label for="year_end" class="form-label">
                                    <i class="fas fa-calendar"></i>
                                    Year End Date
                                </label>
                                <input type="date" id="year_end" name="year_end" class="form-input" 
                                       value="<?php echo htmlspecialchars($year_end ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Add Client
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
