<?php
require_once '../config/database.php';

$message = '';
$messageType = '';

if ($_POST) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $message_text = trim($_POST['message'] ?? '');
    
    // Basic validation
    if (empty($name) || empty($email) || empty($message_text)) {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        try {
            // Insert into database
            $stmt = $pdo->prepare("INSERT INTO formresponse (name, email, telephone, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $telephone, $message_text]);
            
            $message = 'Thank you for your message! We will get back to you soon.';
            $messageType = 'success';
            
            // Clear form data
            $name = $email = $telephone = $message_text = '';
        } catch (PDOException $e) {
            $message = 'Sorry, there was an error submitting your message. Please try again.';
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
    <title>Contact Us - RJ Accountancy Limited</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <img src="../assets/images/RJA-icon Blue.png" alt="RJ Accountancy Logo" style="height: 30px; margin-right: 10px;">
                <span>RJ Accountancy</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../home" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="../about" class="nav-link">About Us</a>
                </li>
                <li class="nav-item">
                    <a href="../fees" class="nav-link">Fees</a>
                </li>
                <li class="nav-item">
                    <a href="../contact" class="nav-link active">Contact</a>
                </li>
                <li class="nav-item">
                    <a href="../login" class="nav-link">Login</a>
                </li>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <div class="contact-section">
            <div class="container">
                <div class="contact-header">
                    <h1 class="contact-title">Get in Touch</h1>
                    <p class="contact-subtitle">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
                </div>

                <div class="contact-content">
                    <div class="contact-info">
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h3>Email Us</h3>
                            <p>info@rjaccountancy.co.uk</p>
                            <p>Send us an email and we'll respond within 24 hours.</p>
                        </div>
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <h3>Call Us</h3>
                            <p>01526 354 687</p>
                            <p>Give us a call during business hours.</p>
                        </div>
                        <div class="info-card">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h3>Business Hours</h3>
                            <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
                        </div>
                    </div>

                    <div class="contact-form-container">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType; ?>">
                                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>

                        <form class="contact-form" method="POST" action="">
                            <div class="form-group">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Full Name *
                                </label>
                                <input type="text" id="name" name="name" class="form-input" 
                                       value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address *
                                </label>
                                <input type="email" id="email" name="email" class="form-input" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="telephone" class="form-label">
                                    <i class="fas fa-phone"></i>
                                    Telephone Number
                                </label>
                                <input type="tel" id="telephone" name="telephone" class="form-input" 
                                       value="<?php echo htmlspecialchars($telephone ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="message" class="form-label">
                                    <i class="fas fa-comment"></i>
                                    Message *
                                </label>
                                <textarea id="message" name="message" class="form-textarea" rows="6" 
                                          placeholder="Tell us how we can help you..." required><?php echo htmlspecialchars($message_text ?? ''); ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary btn-submit">
                                <i class="fas fa-paper-plane"></i>
                                Send Message
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 RJ Accountancy Limited. All rights reserved.</p>
            <p>Director: Rob Bonehill (FCA)</p>
        </div>
    </footer>
</body>
</html>
