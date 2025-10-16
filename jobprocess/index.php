<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit;
}

$account_type = $_SESSION['account_type'] ?? 'Basic';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../login');
    exit;
}

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_state'])) {
        $state_name = trim($_POST['state_name']);
        $state_order = intval($_POST['state_order']);
        $state_access = $_POST['state_access'];
        
        if (!empty($state_name)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO state (state_name, state_order, state_access) VALUES (?, ?, ?)");
                $stmt->execute([$state_name, $state_order, $state_access]);
                $message = 'Job stage added successfully.';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Failed to add job stage.';
                $messageType = 'error';
            }
        }
    }
    
    if (isset($_POST['update_state'])) {
        $state_id = intval($_POST['state_id']);
        $state_name = trim($_POST['state_name']);
        $state_order = intval($_POST['state_order']);
        $state_access = $_POST['state_access'];
        
        if (!empty($state_name)) {
            try {
                $stmt = $pdo->prepare("UPDATE state SET state_name = ?, state_order = ?, state_access = ? WHERE id = ?");
                $stmt->execute([$state_name, $state_order, $state_access, $state_id]);
                $message = 'Job stage updated successfully.';
                $messageType = 'success';
            } catch (PDOException $e) {
                $message = 'Failed to update job stage.';
                $messageType = 'error';
            }
        }
    }
    
    if (isset($_POST['delete_state'])) {
        $state_id = intval($_POST['state_id']);
        try {
            // Check if state is being used by any jobs
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM jobs WHERE state_id = ?");
            $stmt->execute([$state_id]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                $message = 'Cannot delete job stage as it is being used by existing jobs.';
                $messageType = 'error';
            } else {
                $stmt = $pdo->prepare("DELETE FROM state WHERE id = ?");
                $stmt->execute([$state_id]);
                $message = 'Job stage deleted successfully.';
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            $message = 'Failed to delete job stage.';
            $messageType = 'error';
        }
    }
}

// Get all states ordered by state_order
try {
    $stmt = $pdo->query("SELECT * FROM state ORDER BY state_order ASC, id ASC");
    $states = $stmt->fetchAll();
} catch (PDOException $e) {
    $states = [];
    $message = 'Failed to load job stages.';
    $messageType = 'error';
}

// Get next available order number
$next_order = 1;
if (!empty($states)) {
    $max_order = max(array_column($states, 'state_order'));
    $next_order = $max_order + 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Job Process - Cursor1</title>
    <link rel="icon" type="image/png" href="../assets/images/RJA-icon Blue.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-cogs"></i>
                <span>Job Process</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../home" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="../practice" class="nav-link">Practice Portal</a>
                </li>
                <li class="nav-item">
                    <a href="../settings" class="nav-link">Settings</a>
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
        <div class="practice-section">
            <div class="container">
                <div class="practice-header">
                    <h1 class="practice-title">Job Process Management</h1>
                    <p class="practice-subtitle">Manage job stages and their access levels</p>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Add New State Form -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-plus"></i> Add New Job Stage</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="form-grid">
                            <div class="form-group">
                                <label for="state_name">Job Stage Name</label>
                                <input type="text" id="state_name" name="state_name" required class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="state_order">Order</label>
                                <input type="number" id="state_order" name="state_order" value="<?php echo $next_order; ?>" min="1" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="state_access">Access Level</label>
                                <select id="state_access" name="state_access" class="form-control">
                                    <option value="Basic">Basic</option>
                                    <option value="Manager">Manager</option>
                                    <option value="Administrator">Administrator</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <button type="submit" name="add_state" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Stage
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- States List -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> Job Stages</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($states)): ?>
                            <p class="text-muted">No job stages found.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Job Stage</th>
                                            <th>Access</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($states as $state): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($state['state_order']); ?></td>
                                                <td><?php echo htmlspecialchars($state['state_name']); ?></td>
                                                <td>
                                                    <span class="badge badge-<?php echo strtolower($state['state_access']); ?>">
                                                        <?php echo htmlspecialchars($state['state_access']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editState(<?php echo htmlspecialchars(json_encode($state)); ?>)">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this job stage?')">
                                                        <input type="hidden" name="state_id" value="<?php echo $state['id']; ?>">
                                                        <button type="submit" name="delete_state" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Edit Modal -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Job Stage</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <form method="POST" id="editForm">
                    <input type="hidden" name="state_id" id="edit_state_id">
                    <div class="form-group">
                        <label for="edit_state_name">Job Stage Name</label>
                        <input type="text" id="edit_state_name" name="state_name" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_state_order">Order</label>
                        <input type="number" id="edit_state_order" name="state_order" min="1" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="edit_state_access">Access Level</label>
                        <select id="edit_state_access" name="state_access" class="form-control">
                            <option value="Basic">Basic</option>
                            <option value="Manager">Manager</option>
                            <option value="Administrator">Administrator</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" name="update_state" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Stage
                        </button>
                        <button type="button" onclick="closeModal()" class="btn btn-secondary">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Cursor1. All rights reserved.</p>
        </div>
    </footer>

    <style>
        .practice-section {
            padding-top: 20px !important;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            margin-bottom: 30px;
        }
        
        .card-header {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            background: #f8f9fa;
            border-radius: 12px 12px 0 0;
        }
        
        .card-header h3 {
            margin: 0;
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 500;
            margin-bottom: 5px;
            color: #555;
        }
        
        .form-control {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .badge-basic {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-manager {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .badge-administrator {
            background: #ffebee;
            color: #d32f2f;
        }
        
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            color: #333;
        }
        
        .close {
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }
        
        .close:hover {
            color: #333;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                margin: 10% auto;
                width: 95%;
            }
        }
    </style>

    <script>
        function editState(state) {
            document.getElementById('edit_state_id').value = state.id;
            document.getElementById('edit_state_name').value = state.state_name;
            document.getElementById('edit_state_order').value = state.state_order;
            document.getElementById('edit_state_access').value = state.state_access;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
