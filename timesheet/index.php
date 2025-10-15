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
$editing_entry = null;

// Get selected week (default to current week)
$selected_week = $_GET['week'] ?? date('Y-W');
$year = substr($selected_week, 0, 4);
$week = substr($selected_week, 5, 2);

// Handle edit entry request
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM timesheet WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['edit'], $user_id]);
        $editing_entry = $stmt->fetch();
    } catch (PDOException $e) {
        $message = 'Failed to load entry for editing.';
        $messageType = 'error';
    }
}

// Handle copy entry request (get values from URL parameters)
$copied_values = null;
if (isset($_GET['date']) && !isset($_GET['edit'])) {
    $copied_values = [
        'date' => $_GET['date'] ?? '',
        'client_id' => $_GET['client_id'] ?? '',
        'task_id' => $_GET['task_id'] ?? '',
        'time_spent' => $_GET['time_spent'] ?? '',
        'description' => $_GET['description'] ?? ''
    ];
}

// Calculate week dates (Monday to Sunday)
$week_start = date('Y-m-d', strtotime($year . 'W' . str_pad($week, 2, '0', STR_PAD_LEFT)));
$week_end = date('Y-m-d', strtotime($week_start . ' +6 days'));

// Get week days (Monday to Sunday)
$week_days = [];
for ($i = 0; $i < 7; $i++) {
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
    $entry_id = $_POST['entry_id'] ?? '';
    $action = $_POST['action'] ?? 'add';
    
    // Validation
    if (empty($date) || empty($client_id) || empty($task_id) || empty($time_spent)) {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    } elseif (strlen($description) > 100) {
        $message = 'Description must be 100 characters or less.';
        $messageType = 'error';
    } elseif ($action === 'update' && empty($entry_id)) {
        $message = 'No entry selected for update.';
        $messageType = 'error';
    } else {
        try {
            if ($action === 'update' && !empty($entry_id)) {
                // Update existing entry
                $stmt = $pdo->prepare("UPDATE timesheet SET date = ?, client_id = ?, task_id = ?, time_spent = ?, description = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
                $stmt->execute([$date, $client_id, $task_id, $time_spent, $description, $entry_id, $user_id]);
                $message = 'Timesheet entry updated successfully!';
            } else {
                // Insert new entry
                $stmt = $pdo->prepare("INSERT INTO timesheet (user_id, date, client_id, task_id, time_spent, description, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$user_id, $date, $client_id, $task_id, $time_spent, $description]);
                $message = 'Timesheet entry added successfully!';
            }
            
            $messageType = 'success';
            
            // Clear form data
            $date = $client_id = $task_id = $time_spent = $description = $entry_id = '';
            $editing_entry = null;
        } catch (PDOException $e) {
            $message = 'Failed to save timesheet entry. Please try again.';
            $messageType = 'error';
        }
    }
}

// Get clients and tasks for dropdowns
try {
    $stmt = $pdo->query("SELECT id, name FROM clients ORDER BY name ASC");
    $clients = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT id, task_name FROM tasks ORDER BY task_order ASC, task_name ASC");
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
    
    // Calculate daily totals
    $daily_totals = [];
    foreach ($week_days as $day) {
        $total_seconds = 0;
        if (isset($entries_by_date[$day['date']])) {
            foreach ($entries_by_date[$day['date']] as $entry) {
                $time_parts = explode(':', $entry['time_spent']);
                $total_seconds += ($time_parts[0] * 3600) + ($time_parts[1] * 60);
            }
        }
        $daily_totals[$day['date']] = [
            'hours' => floor($total_seconds / 3600),
            'minutes' => floor(($total_seconds % 3600) / 60),
            'total_seconds' => $total_seconds
        ];
    }
} catch (PDOException $e) {
    $timesheet_entries = [];
    $entries_by_date = [];
    $daily_totals = [];
}

// Get available weeks for calendar
$available_weeks = [];
for ($i = -12; $i <= 12; $i++) {
    $week_date = date('Y-m-d', strtotime("$i weeks"));
    $week_number = date('Y-W', strtotime($week_date));
    $week_label = date('M j', strtotime($week_date)) . ' - ' . date('M j, Y', strtotime($week_date . ' +6 days'));
    $available_weeks[$week_number] = $week_label;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Timesheet - Cursor1</title>
    <link rel="icon" type="image/png" href="../assets/images/RJA-icon Blue.png">
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
                            <p>Monday to Sunday</p>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="calendar-main">
                        <!-- Add/Edit Entry Form -->
                        <div class="form-section">
                            <h3 class="section-title">
                                <?php 
                                if ($editing_entry) {
                                    echo 'Edit Timesheet Entry';
                                } elseif ($copied_values) {
                                    echo 'Add Timesheet Entry (Copied)';
                                } else {
                                    echo 'Add Timesheet Entry';
                                }
                                ?>
                            </h3>
                            <form method="POST" action="">
                                <input type="hidden" name="entry_id" id="entry_id" value="<?php echo $editing_entry ? $editing_entry['id'] : ''; ?>">
                                
                                <div class="form-layout">
                                    <div class="form-group">
                                        <label for="date" class="form-label">
                                            <i class="fas fa-calendar"></i>
                                            Date *
                                        </label>
                                        <input type="date" id="date" name="date" class="form-input" 
                                               value="<?php echo $editing_entry ? $editing_entry['date'] : ($copied_values ? $copied_values['date'] : ($date ?? date('Y-m-d'))); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="client_id" class="form-label">
                                            <i class="fas fa-users"></i>
                                            Client *
                                        </label>
                                        <div class="custom-dropdown">
                                            <input type="text" id="client_search" class="form-input client-search" 
                                                   placeholder="Search clients..." autocomplete="off">
                                            <select id="client_id" name="client_id" class="form-input client-select" required>
                                                <option value="">Select a client</option>
                                                <?php foreach ($clients as $client): ?>
                                                    <option value="<?php echo $client['id']; ?>" 
                                                            <?php echo ($editing_entry ? $editing_entry['client_id'] : ($copied_values ? $copied_values['client_id'] : ($client_id ?? ''))) == $client['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($client['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
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
                                                        <?php echo ($editing_entry ? $editing_entry['task_id'] : ($copied_values ? $copied_values['task_id'] : ($task_id ?? ''))) == $task['id'] ? 'selected' : ''; ?>>
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
                                               value="<?php echo $editing_entry ? $editing_entry['time_spent'] : ($copied_values ? $copied_values['time_spent'] : ($time_spent ?? '')); ?>" required>
                                    </div>

                                    <div class="form-group" style="grid-column: 1 / -1;">
                                        <label for="description" class="form-label">
                                            <i class="fas fa-comment"></i>
                                            Description (max 100 characters)
                                        </label>
                                        <textarea id="description" name="description" class="form-textarea" rows="3" 
                                                  maxlength="100" placeholder="Brief description of work done"><?php echo htmlspecialchars($editing_entry ? $editing_entry['description'] : ($copied_values ? $copied_values['description'] : ($description ?? ''))); ?></textarea>
                                        <small class="char-count">0/100 characters</small>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" name="action" value="add" class="btn btn-primary" id="add-btn">
                                        <i class="fas fa-plus"></i>
                                        Add Entry
                                    </button>
                                    <button type="submit" name="action" value="update" class="btn btn-success" id="update-btn" style="display: none;">
                                        <i class="fas fa-save"></i>
                                        Update Entry
                                    </button>
                                    <button type="submit" name="action" value="add" class="btn btn-info" id="add-new-btn" style="display: none;" onclick="clearEntryId()">
                                        <i class="fas fa-plus"></i>
                                        Add Entry
                                    </button>
                                    <button type="button" class="btn btn-secondary" onclick="clearForm()" id="clear-btn">
                                        <i class="fas fa-times"></i>
                                        Clear Form
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Week View -->
                        <div class="week-view-section">
                            <h3 class="section-title">Week View</h3>
                            <div class="week-view">
                                <?php foreach ($week_days as $day): ?>
                                    <div class="day-column <?php echo $day['is_today'] ? 'today' : ''; ?>" 
                                         onclick="selectDay('<?php echo $day['date']; ?>')"
                                         data-date="<?php echo $day['date']; ?>">
                                        <div class="day-header">
                                            <div><?php echo $day['day_short']; ?></div>
                                            <div><?php echo $day['day_number']; ?></div>
                                        </div>
                                        <div class="total-time">
                                            <?php 
                                            $total = $daily_totals[$day['date']] ?? ['hours' => 0, 'minutes' => 0];
                                            if ($total['hours'] > 0 || $total['minutes'] > 0): 
                                            ?>
                                                <?php echo $total['hours']; ?>h <?php echo $total['minutes']; ?>m
                                            <?php else: ?>
                                                0h 0m
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Selected Day Entries -->
                        <div class="selected-day-section" id="selected-day-section" style="display: none;">
                            <h3 class="section-title">Selected Day Entries</h3>
                            <div class="selected-day-entries" id="selected-day-entries">
                                <!-- Entries will be populated by JavaScript -->
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

    <style>
        /* Reduce gap between header and page content */
        .admin-section {
            padding-top: 20px !important;
        }
        
        /* Custom dropdown styles */
        .custom-dropdown {
            position: relative;
        }
        
        .client-search {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10;
            background: white;
            border-bottom: none;
            border-radius: 8px 8px 0 0;
        }
        
        .client-select {
            margin-top: 40px;
            border-radius: 0 0 8px 8px;
            border-top: 1px solid #e9ecef;
        }
        
        .client-select:focus {
            border-top-color: #667eea;
        }
        
        .client-search:focus + .client-select {
            border-top-color: #667eea;
        }
        
        /* Mobile optimizations for timesheet form */
        @media (max-width: 768px) {
            .calendar-container {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .calendar-sidebar {
                order: 2;
                padding: 15px;
            }
            
            .calendar-main {
                order: 1;
            }
            
            .form-section {
                padding: 15px;
                margin-bottom: 20px;
            }
            
            .section-title {
                font-size: 1.1rem;
                margin-bottom: 15px;
                padding-bottom: 8px;
            }
            
            .form-layout {
                grid-template-columns: 1fr;
                gap: 15px;
                margin-bottom: 20px;
            }
            
            .form-group {
                margin-bottom: 0;
            }
            
            .form-label {
                font-size: 0.9rem;
                margin-bottom: 6px;
            }
            
            .form-input,
            .form-textarea,
            select.form-input {
                padding: 10px 12px;
                font-size: 16px; /* Prevents zoom on iOS */
                border-radius: 8px;
            }
            
            .client-search {
                padding: 10px 12px;
                font-size: 16px;
                border-radius: 6px 6px 0 0;
            }
            
            .client-select {
                margin-top: 35px;
                border-radius: 0 0 6px 6px;
            }
            
            .form-textarea {
                min-height: 80px;
                resize: vertical;
            }
            
            .char-count {
                font-size: 0.75rem;
                margin-top: 3px;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 10px;
                margin-top: 20px;
                padding-top: 15px;
            }
            
            .form-actions .btn {
                width: 100%;
                justify-content: center;
                padding: 12px 20px;
                font-size: 0.9rem;
                min-height: 44px; /* Touch-friendly */
            }
            
            .week-view-section {
                margin-top: 20px;
            }
            
            .week-view {
                grid-template-columns: repeat(7, minmax(35px, 1fr));
                gap: 1px;
            }
            
            .day-column {
                padding: 2px;
                min-height: 50px;
                min-width: 35px;
            }
            
            .day-header {
                padding: 1px;
                font-size: 0.6rem;
            }
            
            .total-time {
                font-size: 0.55rem;
            }
        }
        
        @media (max-width: 480px) {
            .admin-section {
                padding-top: 10px !important;
            }
            
            .container {
                padding: 0 10px;
            }
            
            .form-section {
                padding: 10px;
                margin-bottom: 15px;
            }
            
            .section-title {
                font-size: 1rem;
                margin-bottom: 12px;
                padding-bottom: 6px;
            }
            
            .form-layout {
                gap: 12px;
                margin-bottom: 15px;
            }
            
            .form-label {
                font-size: 0.85rem;
                margin-bottom: 4px;
            }
            
            .form-input,
            .form-textarea,
            select.form-input {
                padding: 8px 10px;
                font-size: 16px;
                border-radius: 6px;
            }
            
            .client-search {
                padding: 8px 10px;
                font-size: 16px;
                border-radius: 5px 5px 0 0;
            }
            
            .client-select {
                margin-top: 30px;
                border-radius: 0 0 5px 5px;
            }
            
            .form-textarea {
                min-height: 70px;
            }
            
            .char-count {
                font-size: 0.7rem;
            }
            
            .form-actions {
                gap: 8px;
                margin-top: 15px;
                padding-top: 12px;
            }
            
            .form-actions .btn {
                padding: 10px 16px;
                font-size: 0.85rem;
                min-height: 40px;
            }
            
            .calendar-sidebar {
                padding: 10px;
            }
            
            .week-info {
                margin-top: 15px;
                padding: 10px;
            }
            
            .week-info h4 {
                font-size: 1rem;
                margin-bottom: 3px;
            }
            
            .week-info p {
                font-size: 0.8rem;
            }
            
            .week-view {
                grid-template-columns: repeat(7, minmax(30px, 1fr));
            }
            
            .day-column {
                padding: 1px;
                min-height: 45px;
                min-width: 30px;
            }
            
            .day-header {
                padding: 1px;
                font-size: 0.55rem;
            }
            
            .total-time {
                font-size: 0.5rem;
            }
            
            .selected-day-section {
                margin-top: 20px;
            }
            
            .entries-table th,
            .entries-table td {
                padding: 6px;
                font-size: 0.75rem;
            }
        }
    </style>

    <script>
        // Timesheet entries data
        const timesheetEntries = <?php echo json_encode($entries_by_date); ?>;
        
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
        
        // Client search functionality
        const clientSearch = document.getElementById('client_search');
        const clientSelect = document.getElementById('client_id');
        const allClients = Array.from(clientSelect.options);
        
        clientSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            // Clear all options except the first one
            clientSelect.innerHTML = '<option value="">Select a client</option>';
            
            // Filter and add matching clients
            allClients.forEach(option => {
                if (option.value && option.text.toLowerCase().includes(searchTerm)) {
                    clientSelect.appendChild(option.cloneNode(true));
                }
            });
            
            // If search is cleared, show all clients
            if (searchTerm === '') {
                allClients.forEach(option => {
                    if (option.value) {
                        clientSelect.appendChild(option.cloneNode(true));
                    }
                });
            }
        });
        
        // Update search box when client is selected from dropdown
        clientSelect.addEventListener('change', function() {
            if (this.value) {
                const selectedOption = this.options[this.selectedIndex];
                clientSearch.value = selectedOption.text;
            } else {
                clientSearch.value = '';
            }
        });
        
        // Initialize search box with selected client name
        if (clientSelect.value) {
            const selectedOption = clientSelect.options[clientSelect.selectedIndex];
            clientSearch.value = selectedOption.text;
        }
        
        // Edit entry function
        function editEntry(entryId) {
            window.location.href = '?edit=' + entryId;
        }
        
        // Copy entry function
        function copyEntry() {
            // Get current form values
            const date = document.getElementById('date').value;
            const clientId = document.getElementById('client_id').value;
            const taskId = document.getElementById('task_id').value;
            const timeSpent = document.getElementById('time_spent').value;
            const description = document.getElementById('description').value;
            
            // Redirect to add mode with copied values
            const params = new URLSearchParams({
                date: date,
                client_id: clientId,
                task_id: taskId,
                time_spent: timeSpent,
                description: description
            });
            
            window.location.href = '?' + params.toString();
        }
        
        // Select day function
        function selectDay(date) {
            // Remove highlight from all days
            document.querySelectorAll('.day-column').forEach(day => {
                day.classList.remove('selected');
            });
            
            // Add highlight to selected day
            const selectedDay = document.querySelector(`[data-date="${date}"]`);
            selectedDay.classList.add('selected');
            
            // Show selected day entries
            const selectedDaySection = document.getElementById('selected-day-section');
            const selectedDayEntries = document.getElementById('selected-day-entries');
            
            if (timesheetEntries[date] && timesheetEntries[date].length > 0) {
                let entriesHtml = `
                    <table class="entries-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Description</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                timesheetEntries[date].forEach(entry => {
                    const description = entry.description || 'No description';
                    const descriptionTruncated = description.length > 40 ? description.substring(0, 40) + '...' : description;
                    const timeFormatted = entry.time_spent.substring(0, 5); // Remove seconds
                    
                    entriesHtml += `
                        <tr onclick="selectEntryRow(this, ${entry.id})">
                            <td class="entry-client">${entry.client_name}</td>
                            <td class="entry-description">${descriptionTruncated}</td>
                            <td class="entry-time">${timeFormatted}</td>
                        </tr>
                    `;
                });
                
                entriesHtml += `
                        </tbody>
                    </table>
                `;
                selectedDayEntries.innerHTML = entriesHtml;
                selectedDaySection.style.display = 'block';
            } else {
                selectedDayEntries.innerHTML = '<p class="no-entries">No entries for this day.</p>';
                selectedDaySection.style.display = 'block';
            }
        }
        
        // Select entry row and populate form
        function selectEntryRow(row, entryId) {
            // Remove selection from all rows
            document.querySelectorAll('.entries-table tr').forEach(r => {
                r.classList.remove('selected');
            });
            
            // Add selection to clicked row
            row.classList.add('selected');
            
            // Populate form
            populateForm(entryId);
        }
        
        // Populate form with entry data
        function populateForm(entryId) {
            // Find the entry in our data
            for (const date in timesheetEntries) {
                const entry = timesheetEntries[date].find(e => e.id == entryId);
                if (entry) {
                    document.getElementById('date').value = entry.date;
                    document.getElementById('client_id').value = entry.client_id;
                    document.getElementById('task_id').value = entry.task_id;
                    document.getElementById('time_spent').value = entry.time_spent;
                    document.getElementById('description').value = entry.description || '';
                    
                    // Update client search box with selected client name
                    const clientSelect = document.getElementById('client_id');
                    const selectedOption = clientSelect.options[clientSelect.selectedIndex];
                    document.getElementById('client_search').value = selectedOption.text;
                    
                    // Store the entry ID for update functionality
                    document.getElementById('entry_id').value = entryId;
                    
                    // Show update and add new buttons, hide add button
                    document.getElementById('add-btn').style.display = 'none';
                    document.getElementById('update-btn').style.display = 'inline-flex';
                    document.getElementById('add-new-btn').style.display = 'inline-flex';
                    
                    // Update character count
                    charCount.textContent = (entry.description || '').length + '/100 characters';
                    
                    // Scroll to form
                    document.querySelector('.form-section').scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start' 
                    });
                    break;
                }
            }
        }
        
        // Clear entry ID function (called when + Add Entry button is clicked)
        function clearEntryId() {
            // Clear the entry ID so it creates a new entry instead of updating
            document.getElementById('entry_id').value = '';
        }
        
        // Clear form function
        function clearForm() {
            document.getElementById('date').value = '';
            document.getElementById('client_id').value = '';
            document.getElementById('client_search').value = '';
            document.getElementById('task_id').value = '';
            document.getElementById('time_spent').value = '';
            document.getElementById('description').value = '';
            
            // Clear entry ID
            document.getElementById('entry_id').value = '';
            
            // Show add button, hide update and add new buttons
            document.getElementById('add-btn').style.display = 'inline-flex';
            document.getElementById('update-btn').style.display = 'none';
            document.getElementById('add-new-btn').style.display = 'none';
            
            // Clear row selection
            document.querySelectorAll('.entries-table tr').forEach(r => {
                r.classList.remove('selected');
            });
            
            // Update character count
            charCount.textContent = '0/100 characters';
        }
        
        // Initialize with current day selected
        document.addEventListener('DOMContentLoaded', function() {
            const today = '<?php echo date('Y-m-d'); ?>';
            selectDay(today);
            
            // Scroll to form when editing
            <?php if ($editing_entry): ?>
            document.querySelector('.form-section').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>
