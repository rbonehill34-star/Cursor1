<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Get selected week (default to current week)
$selected_week = $_GET['week'] ?? date('Y-W');
$year = substr($selected_week, 0, 4);
$week = substr($selected_week, 5, 2);

// Calculate week dates
$week_start = date('Y-m-d', strtotime($year . 'W' . str_pad($week, 2, '0', STR_PAD_LEFT)));
$week_end = date('Y-m-d', strtotime($week_start . ' +5 days'));

// Get week days (Monday to Saturday)
$week_days = [];
for ($i = 0; $i < 6; $i++) {
    $date = date('Y-m-d', strtotime($week_start . " +$i days"));
    $week_days[] = [
        'date' => $date,
        'day_name' => date('l', strtotime($date)),
        'day_short' => date('D', strtotime($date)),
        'day_number' => date('j', strtotime($date)),
        'is_today' => $date === date('Y-m-d')
    ];
}

// Handle form submission
if ($_POST) {
    $date = $_POST['date'] ?? '';
    $client_id = $_POST['client_id'] ?? '';
    $task_id = $_POST['task_id'] ?? '';
    $time_spent = $_POST['time_spent'] ?? '';
    $description = trim($_POST['description'] ?? '');
    
    // Validation
    if (empty($date) || empty($client_id) || empty($task_id) || empty($time_spent)) {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    } elseif (strlen($description) > 100) {
        $message = 'Description must be 100 characters or less.';
        $messageType = 'error';
    } else {
        try {
            // Insert timesheet entry
            $stmt = $pdo->prepare("INSERT INTO timesheet (user_id, date, client_id, task_id, time_spent, description, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$user_id, $date, $client_id, $task_id, $time_spent, $description]);
            
            $message = 'Timesheet entry added successfully!';
            $messageType = 'success';
            
            // Clear form data
            $date = $client_id = $task_id = $time_spent = $description = '';
        } catch (PDOException $e) {
            $message = 'Failed to add timesheet entry. Please try again.';
            $messageType = 'error';
        }
    }
}

// Get clients and tasks for dropdowns
try {
    $stmt = $pdo->query("SELECT id, name FROM clients ORDER BY name ASC");
    $clients = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT id, task_name FROM tasks ORDER BY task_name ASC");
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    $clients = $tasks = [];
}

// Get timesheet entries for the selected week
try {
    $stmt = $pdo->prepare("
        SELECT ts.*, c.name as client_name, t.task_name 
        FROM timesheet ts 
        JOIN clients c ON ts.client_id = c.id 
        JOIN tasks t ON ts.task_id = t.id 
        WHERE ts.user_id = ? AND ts.date BETWEEN ? AND ? 
        ORDER BY ts.date ASC, ts.created_at ASC
    ");
    $stmt->execute([$user_id, $week_start, $week_end]);
    $timesheet_entries = $stmt->fetchAll();
    
    // Group entries by date
    $entries_by_date = [];
    foreach ($timesheet_entries as $entry) {
        $entries_by_date[$entry['date']][] = $entry;
    }
} catch (PDOException $e) {
    $timesheet_entries = [];
    $entries_by_date = [];
}

// Get available weeks for calendar
$available_weeks = [];
for ($i = -12; $i <= 12; $i++) {
    $week_date = date('Y-m-d', strtotime("$i weeks"));
    $week_number = date('Y-W', strtotime($week_date));
    $week_label = date('M j', strtotime($week_date)) . ' - ' . date('M j, Y', strtotime($week_date . ' +5 days'));
    $available_weeks[$week_number] = $week_label;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Timesheet - Cursor1</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-clock"></i>
                <span>Timesheet</span>
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
                    <h1 class="page-title">Timesheet</h1>
                    <div class="page-actions">
                        <a href="../practice" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Portal
                        </a>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div class="calendar-container">
                    <!-- Calendar Sidebar -->
                    <div class="calendar-sidebar">
                        <h3>Select Week</h3>
                        <form method="GET" action="">
                            <div class="form-group">
                                <select name="week" class="form-input" onchange="this.form.submit()">
                                    <?php foreach ($available_weeks as $week_value => $week_label): ?>
                                        <option value="<?php echo $week_value; ?>" <?php echo $selected_week === $week_value ? 'selected' : ''; ?>>
                                            <?php echo $week_label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                        
                        <div class="week-info">
                            <h4>Week of <?php echo date('M j, Y', strtotime($week_start)); ?></h4>
                            <p>Monday to Saturday</p>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="calendar-main">
                        <!-- Add Entry Form -->
                        <div class="form-section">
                            <h3 class="section-title">Add Timesheet Entry</h3>
                            <form method="POST" action="">
                                <div class="form-layout">
                                    <div class="form-group">
                                        <label for="date" class="form-label">
                                            <i class="fas fa-calendar"></i>
                                            Date *
                                        </label>
                                        <input type="date" id="date" name="date" class="form-input" 
                                               value="<?php echo $date ?? date('Y-m-d'); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="client_id" class="form-label">
                                            <i class="fas fa-users"></i>
                                            Client *
                                        </label>
                                        <select id="client_id" name="client_id" class="form-input" required>
                                            <option value="">Select a client</option>
                                            <?php foreach ($clients as $client): ?>
                                                <option value="<?php echo $client['id']; ?>" 
                                                        <?php echo ($client_id ?? '') == $client['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($client['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="task_id" class="form-label">
                                            <i class="fas fa-tasks"></i>
                                            Task *
                                        </label>
                                        <select id="task_id" name="task_id" class="form-input" required>
                                            <option value="">Select a task</option>
                                            <?php foreach ($tasks as $task): ?>
                                                <option value="<?php echo $task['id']; ?>" 
                                                        <?php echo ($task_id ?? '') == $task['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($task['task_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="time_spent" class="form-label">
                                            <i class="fas fa-clock"></i>
                                            Time Spent *
                                        </label>
                                        <input type="time" id="time_spent" name="time_spent" class="form-input" 
                                               value="<?php echo $time_spent ?? ''; ?>" required>
                                    </div>

                                    <div class="form-group" style="grid-column: 1 / -1;">
                                        <label for="description" class="form-label">
                                            <i class="fas fa-comment"></i>
                                            Description (max 100 characters)
                                        </label>
                                        <textarea id="description" name="description" class="form-textarea" rows="3" 
                                                  maxlength="100" placeholder="Brief description of work done"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                                        <small class="char-count">0/100 characters</small>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        Add Entry
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Week View -->
                        <div class="week-view-section">
                            <h3 class="section-title">Week View</h3>
                            <div class="week-view">
                                <?php foreach ($week_days as $day): ?>
                                    <div class="day-column <?php echo $day['is_today'] ? 'today' : ''; ?>">
                                        <div class="day-header">
                                            <div><?php echo $day['day_short']; ?></div>
                                            <div><?php echo $day['day_number']; ?></div>
                                        </div>
                                        <div class="day-entries">
                                            <?php if (isset($entries_by_date[$day['date']])): ?>
                                                <?php foreach ($entries_by_date[$day['date']] as $entry): ?>
                                                    <div class="timesheet-entry" title="<?php echo htmlspecialchars($entry['description']); ?>">
                                                        <div class="entry-client"><?php echo htmlspecialchars($entry['client_name']); ?></div>
                                                        <div class="entry-task"><?php echo htmlspecialchars($entry['task_name']); ?></div>
                                                        <div class="entry-time"><?php echo date('H:i', strtotime($entry['time_spent'])); ?></div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
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

    <script>
        // Character counter for description
        const description = document.getElementById('description');
        const charCount = document.querySelector('.char-count');
        
        description.addEventListener('input', function() {
            const length = this.value.length;
            charCount.textContent = length + '/100 characters';
            charCount.style.color = length > 100 ? '#dc3545' : '#6c757d';
        });
        
        // Initialize character count
        charCount.textContent = description.value.length + '/100 characters';
    </script>
</body>
</html>
