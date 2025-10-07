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

// Initialize form data
$formData = [
    'client_id' => '',
    'client_reference' => '',
    'task_id' => '',
    'description' => '',
    'budget_hours' => '',
    'state_id' => '',
    'urgent' => false,
    'partner_id' => '',
    'manager_id' => '',
    'preparer_id' => '',
    'deadline_date' => '',
    'expected_completion_date' => '',
    'received_date' => '',
    'assigned_date' => '',
    'comments' => ''
];

if ($_POST) {
    // Get form data
    $formData['client_id'] = $_POST['client_id'] ?? '';
    $formData['client_reference'] = $_POST['client_reference'] ?? '';
    $formData['task_id'] = $_POST['task_id'] ?? '';
    $formData['description'] = trim($_POST['description'] ?? '');
    $formData['budget_hours'] = $_POST['budget_hours'] ?? '';
    $formData['state_id'] = $_POST['state_id'] ?? '';
    $formData['urgent'] = isset($_POST['urgent']);
    $formData['partner_id'] = $_POST['partner_id'] ?? '';
    $formData['manager_id'] = $_POST['manager_id'] ?? '';
    $formData['preparer_id'] = $_POST['preparer_id'] ?? '';
    $formData['deadline_date'] = $_POST['deadline_date'] ?? '';
    $formData['expected_completion_date'] = $_POST['expected_completion_date'] ?? '';
    $formData['received_date'] = $_POST['received_date'] ?? '';
    $formData['assigned_date'] = $_POST['assigned_date'] ?? '';
    $formData['comments'] = trim($_POST['comments'] ?? '');
    
    // Validation
    if (empty($formData['client_id']) || empty($formData['task_id']) || empty($formData['state_id'])) {
        $message = 'Client, Task, and State are required fields.';
        $messageType = 'error';
    } else {
        try {
            // Insert new job
            $stmt = $pdo->prepare("
                INSERT INTO jobs (
                    client_id, client_reference, task_id, description, budget_hours, 
                    state_id, urgent, partner_id, manager_id, preparer_id, 
                    deadline_date, expected_completion_date, received_date, 
                    assigned_date, comments, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $formData['client_id'],
                $formData['client_reference'] ?: null,
                $formData['task_id'],
                $formData['description'] ?: null,
                $formData['budget_hours'] ?: null,
                $formData['state_id'],
                $formData['urgent'] ? 1 : 0,
                $formData['partner_id'] ?: null,
                $formData['manager_id'] ?: null,
                $formData['preparer_id'] ?: null,
                $formData['deadline_date'] ?: null,
                $formData['expected_completion_date'] ?: null,
                $formData['received_date'] ?: null,
                $formData['assigned_date'] ?: null,
                $formData['comments'] ?: null
            ]);
            
            $message = 'Job created successfully!';
            $messageType = 'success';
            
            // Clear form data
            $formData = array_fill_keys(array_keys($formData), '');
            $formData['urgent'] = false;
            
        } catch (PDOException $e) {
            $message = 'Failed to create job. Please try again.';
            $messageType = 'error';
        }
    }
}

// Get data for dropdowns
try {
    // Get clients
    $stmt = $pdo->query("SELECT id, name, reference FROM clients ORDER BY name ASC");
    $clients = $stmt->fetchAll();
    
    // Get tasks
    $stmt = $pdo->query("SELECT id, task_name FROM tasks ORDER BY task_name ASC");
    $tasks = $stmt->fetchAll();
    
    // Get states
    $stmt = $pdo->query("SELECT id, state_name FROM state ORDER BY state_name ASC");
    $states = $stmt->fetchAll();
    
    // Get users for partner, manager, preparer
    $stmt = $pdo->query("SELECT id, username FROM login ORDER BY username ASC");
    $users = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $clients = $tasks = $states = $users = [];
    $message = 'Failed to load form data.';
    $messageType = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>New Job - Cursor1</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-plus"></i>
                <span>New Job</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../practice" class="nav-link">Practice Portal</a>
                </li>
                <li class="nav-item">
                    <a href="index" class="nav-link">Back to Jobs</a>
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
                    <h1 class="page-title">Create New Job</h1>
                    <div class="page-actions">
                        <a href="index" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Jobs
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
                    <form method="POST" action="" id="jobForm">
                        <div class="form-layout">
                            <!-- Client Selection -->
                            <div class="form-group">
                                <label for="client_id" class="form-label">
                                    <i class="fas fa-building"></i>
                                    Client *
                                </label>
                                <select id="client_id" name="client_id" class="form-input" required>
                                    <option value="">Select a client...</option>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?php echo $client['id']; ?>" 
                                                data-reference="<?php echo htmlspecialchars($client['reference']); ?>"
                                                <?php echo $formData['client_id'] == $client['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($client['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="client_reference" class="form-label">
                                    <i class="fas fa-hashtag"></i>
                                    Client Reference
                                </label>
                                <input type="text" id="client_reference" name="client_reference" class="form-input" 
                                       value="<?php echo htmlspecialchars($formData['client_reference']); ?>" 
                                       placeholder="Auto-filled when client is selected">
                            </div>

                            <!-- Task Selection -->
                            <div class="form-group">
                                <label for="task_id" class="form-label">
                                    <i class="fas fa-tasks"></i>
                                    Task *
                                </label>
                                <select id="task_id" name="task_id" class="form-input" required>
                                    <option value="">Select a task...</option>
                                    <?php foreach ($tasks as $task): ?>
                                        <option value="<?php echo $task['id']; ?>" 
                                                <?php echo $formData['task_id'] == $task['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($task['task_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left"></i>
                                    Description
                                </label>
                                <input type="text" id="description" name="description" class="form-input" 
                                       value="<?php echo htmlspecialchars($formData['description']); ?>" 
                                       placeholder="Job description" maxlength="100">
                            </div>

                            <!-- Budget Hours -->
                            <div class="form-group">
                                <label for="budget_hours" class="form-label">
                                    <i class="fas fa-clock"></i>
                                    Budget Hours
                                </label>
                                <input type="number" id="budget_hours" name="budget_hours" class="form-input" 
                                       value="<?php echo htmlspecialchars($formData['budget_hours']); ?>" 
                                       placeholder="0.00" step="0.25" min="0">
                            </div>

                            <!-- State -->
                            <div class="form-group">
                                <label for="state_id" class="form-label">
                                    <i class="fas fa-flag"></i>
                                    State *
                                </label>
                                <select id="state_id" name="state_id" class="form-input" required>
                                    <option value="">Select a state...</option>
                                    <?php foreach ($states as $state): ?>
                                        <option value="<?php echo $state['id']; ?>" 
                                                <?php echo $formData['state_id'] == $state['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($state['state_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Urgent Checkbox -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Priority
                                </label>
                                <div class="checkbox-group">
                                    <input type="checkbox" id="urgent" name="urgent" 
                                           <?php echo $formData['urgent'] ? 'checked' : ''; ?>>
                                    <label for="urgent" class="checkbox-label">Urgent</label>
                                </div>
                            </div>

                            <!-- Staff Assignment -->
                            <div class="form-group">
                                <label for="partner_id" class="form-label">
                                    <i class="fas fa-user-tie"></i>
                                    Partner
                                </label>
                                <select id="partner_id" name="partner_id" class="form-input">
                                    <option value="">Select a partner...</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>" 
                                                <?php echo $formData['partner_id'] == $user['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="manager_id" class="form-label">
                                    <i class="fas fa-user-cog"></i>
                                    Manager
                                </label>
                                <select id="manager_id" name="manager_id" class="form-input">
                                    <option value="">Select a manager...</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>" 
                                                <?php echo $formData['manager_id'] == $user['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="preparer_id" class="form-label">
                                    <i class="fas fa-user-edit"></i>
                                    Preparer
                                </label>
                                <select id="preparer_id" name="preparer_id" class="form-input">
                                    <option value="">Select a preparer...</option>
                                    <?php foreach ($users as $user): ?>
                                        <option value="<?php echo $user['id']; ?>" 
                                                <?php echo $formData['preparer_id'] == $user['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Dates -->
                            <div class="form-group">
                                <label for="deadline_date" class="form-label">
                                    <i class="fas fa-calendar-times"></i>
                                    Deadline Date
                                </label>
                                <input type="date" id="deadline_date" name="deadline_date" class="form-input" 
                                       value="<?php echo htmlspecialchars($formData['deadline_date']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="expected_completion_date" class="form-label">
                                    <i class="fas fa-calendar-check"></i>
                                    Expected Completion Date
                                </label>
                                <input type="date" id="expected_completion_date" name="expected_completion_date" class="form-input" 
                                       value="<?php echo htmlspecialchars($formData['expected_completion_date']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="received_date" class="form-label">
                                    <i class="fas fa-calendar-plus"></i>
                                    Received Date
                                </label>
                                <input type="date" id="received_date" name="received_date" class="form-input" 
                                       value="<?php echo htmlspecialchars($formData['received_date']); ?>">
                            </div>

                            <div class="form-group">
                                <label for="assigned_date" class="form-label">
                                    <i class="fas fa-calendar-alt"></i>
                                    Assigned Date
                                </label>
                                <input type="date" id="assigned_date" name="assigned_date" class="form-input" 
                                       value="<?php echo htmlspecialchars($formData['assigned_date']); ?>">
                            </div>

                            <!-- Comments -->
                            <div class="form-group full-width">
                                <label for="comments" class="form-label">
                                    <i class="fas fa-comment"></i>
                                    Comments
                                </label>
                                <textarea id="comments" name="comments" class="form-input" rows="3" 
                                          placeholder="Additional notes or comments" maxlength="255"><?php echo htmlspecialchars($formData['comments']); ?></textarea>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Create Job
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

    <script>
        // Auto-fill client reference when client is selected
        document.getElementById('client_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const clientReference = document.getElementById('client_reference');
            
            if (selectedOption.value && selectedOption.dataset.reference) {
                clientReference.value = selectedOption.dataset.reference;
            } else {
                clientReference.value = '';
            }
        });

        // Auto-fill client when client reference is typed
        document.getElementById('client_reference').addEventListener('input', function() {
            const reference = this.value.trim();
            const clientSelect = document.getElementById('client_id');
            
            if (reference) {
                // Find matching client by reference
                for (let option of clientSelect.options) {
                    if (option.dataset.reference && option.dataset.reference.toLowerCase().includes(reference.toLowerCase())) {
                        clientSelect.value = option.value;
                        break;
                    }
                }
            }
        });
    </script>

    <style>
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .checkbox-label {
            margin: 0;
            font-weight: normal;
            cursor: pointer;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .form-layout {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
    </style>
</body>
</html>


