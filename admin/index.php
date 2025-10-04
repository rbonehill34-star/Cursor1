<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../login');
    exit;
}

// Get form responses
try {
    $stmt = $pdo->prepare("SELECT * FROM formresponse ORDER BY created_at DESC");
    $stmt->execute();
    $responses = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Failed to load form responses.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Cursor1</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-database"></i>
                <span>Cursor1 Admin</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../home" class="nav-link">Home</a>
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
        <div class="admin-section">
            <div class="container">
                <div class="admin-header">
                    <h1 class="admin-title">Admin Dashboard</h1>
                    <p class="admin-subtitle">Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                </div>

                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo count($responses); ?></h3>
                            <p>Total Messages</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo count(array_filter($responses, function($r) { 
                                return strtotime($r['created_at']) > strtotime('-24 hours'); 
                            })); ?></h3>
                            <p>Last 24 Hours</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo count(array_filter($responses, function($r) { 
                                return strtotime($r['created_at']) > strtotime('-7 days'); 
                            })); ?></h3>
                            <p>This Week</p>
                        </div>
                    </div>
                </div>

                <div class="responses-section">
                    <div class="section-header">
                        <h2>Form Responses</h2>
                        <div class="section-actions">
                            <button class="btn btn-secondary" onclick="refreshPage()">
                                <i class="fas fa-sync-alt"></i>
                                Refresh
                            </button>
                        </div>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php elseif (empty($responses)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-inbox"></i>
                            </div>
                            <h3>No messages yet</h3>
                            <p>Form responses will appear here when users submit the contact form.</p>
                        </div>
                    <?php else: ?>
                        <div class="responses-table-container">
                            <table class="responses-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Telephone</th>
                                        <th>Message</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($responses as $response): ?>
                                        <tr>
                                            <td><?php echo $response['id']; ?></td>
                                            <td><?php echo htmlspecialchars($response['name']); ?></td>
                                            <td>
                                                <a href="mailto:<?php echo htmlspecialchars($response['email']); ?>" class="email-link">
                                                    <?php echo htmlspecialchars($response['email']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($response['telephone']): ?>
                                                    <a href="tel:<?php echo htmlspecialchars($response['telephone']); ?>" class="phone-link">
                                                        <?php echo htmlspecialchars($response['telephone']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="no-data">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="message-cell">
                                                <div class="message-preview">
                                                    <?php echo htmlspecialchars(substr($response['message'], 0, 100)); ?>
                                                    <?php if (strlen($response['message']) > 100): ?>
                                                        <span class="message-more">...</span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (strlen($response['message']) > 100): ?>
                                                    <button class="btn btn-sm btn-outline" onclick="showFullMessage(<?php echo $response['id']; ?>, '<?php echo htmlspecialchars(addslashes($response['message'])); ?>')">
                                                        View Full
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M j, Y g:i A', strtotime($response['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal for full message -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Full Message</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p id="modalMessage"></p>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Cursor1. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function refreshPage() {
            window.location.reload();
        }

        function showFullMessage(id, message) {
            document.getElementById('modalMessage').textContent = message;
            document.getElementById('messageModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('messageModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('messageModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
