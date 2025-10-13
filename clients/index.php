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

// Handle delete success message from edit page
if (isset($_GET['deleted'])) {
    $message = 'Client deleted successfully.';
    $messageType = 'success';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Clients - Cursor1</title>
    <link rel="icon" type="image/png" href="../assets/images/RJA-icon Blue.png">
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
                    <div class="search-container" style="margin-bottom: 20px;">
                        <div class="form-group">
                            <label for="clientSearch" class="form-label">
                                <i class="fas fa-search"></i>
                                Search Clients
                            </label>
                            <input type="text" id="clientSearch" class="form-input" 
                                   placeholder="Type to search clients..." 
                                   autocomplete="off">
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Reference</th>
                                    <th>Contact</th>
                                </tr>
                            </thead>
                            <tbody id="clientsTableBody">
                                <?php foreach ($clients as $client): ?>
                                    <tr class="clickable-row" data-href="edit?id=<?php echo $client['id']; ?>" style="cursor: pointer;">
                                        <td><?php echo htmlspecialchars($client['name']); ?></td>
                                        <td><?php echo htmlspecialchars($client['type'] ?? 'Company'); ?></td>
                                        <td><strong><?php echo htmlspecialchars($client['reference']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($client['contact'] ?? '-'); ?></td>
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

    <style>
        /* Reduce gap between header and page content */
        .admin-section {
            padding-top: 20px !important;
        }
    </style>

    <script>
        // Make table rows clickable
        document.querySelectorAll('.clickable-row').forEach(row => {
            row.addEventListener('click', function() {
                window.location.href = this.dataset.href;
            });
        });

        // Search functionality
        const searchInput = document.getElementById('clientSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const tableBody = document.getElementById('clientsTableBody');
                const rows = tableBody.getElementsByTagName('tr');

                Array.from(rows).forEach(row => {
                    const name = row.cells[0].textContent.toLowerCase();
                    const type = row.cells[1].textContent.toLowerCase();
                    const reference = row.cells[2].textContent.toLowerCase();
                    const contact = row.cells[3].textContent.toLowerCase();
                    
                    if (name.includes(searchTerm) || type.includes(searchTerm) || reference.includes(searchTerm) || contact.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    </script>
</body>
</html>
