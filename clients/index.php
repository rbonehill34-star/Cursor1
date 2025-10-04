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

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $message = 'Client deleted successfully.';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Failed to delete client.';
        $messageType = 'error';
    }
}

// Get all clients
try {
    $stmt = $pdo->query("SELECT * FROM clients ORDER BY name ASC");
    $clients = $stmt->fetchAll();
} catch (PDOException $e) {
    $clients = [];
    $message = 'Failed to load clients.';
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients - Cursor1</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-users"></i>
                <span>Clients</span>
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
                    <h1 class="page-title">Client Management</h1>
                    <div class="page-actions">
                        <a href="add" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Add New Client
                        </a>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($clients)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>No clients yet</h3>
                        <p>Get started by adding your first client.</p>
                        <a href="add" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Add First Client
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Year End</th>
                                    <th>Date Added</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clients as $client): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($client['reference']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($client['name']); ?></td>
                                        <td><?php echo htmlspecialchars($client['contact'] ?? '-'); ?></td>
                                        <td>
                                            <?php if ($client['email']): ?>
                                                <a href="mailto:<?php echo htmlspecialchars($client['email']); ?>" class="email-link">
                                                    <?php echo htmlspecialchars($client['email']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="no-data">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($client['phone']): ?>
                                                <a href="tel:<?php echo htmlspecialchars($client['phone']); ?>" class="phone-link">
                                                    <?php echo htmlspecialchars($client['phone']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="no-data">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $client['year_end'] ? date('M j, Y', strtotime($client['year_end'])) : '-'; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($client['date_added'])); ?></td>
                                        <td>
                                            <a href="edit?id=<?php echo $client['id']; ?>" class="btn btn-action btn-edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $client['id']; ?>" 
                                               class="btn btn-action btn-delete"
                                               onclick="return confirm('Are you sure you want to delete this client?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
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
