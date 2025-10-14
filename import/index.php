<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login');
    exit;
}

$account_type = $_SESSION['account_type'] ?? 'Basic';
$username = $_SESSION['username'] ?? 'User';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ../login');
    exit;
}

$message = '';
$messageType = '';

// Handle CSV template download
if (isset($_GET['template'])) {
    $table = $_GET['template'];
    
    if ($table === 'clients') {
        // Generate clients CSV template
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="clients_template.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Write header row
        fputcsv($output, ['reference', 'name', 'type', 'contact_forename', 'contact_surname', 'email', 'phone', 'company_number', 'authentication_code', 'utr_number', 'partner_id', 'year_end', 'year_end_work', 'payroll', 'directors_sa', 'vat', 'vat_periods']);
        
        // Write sample data row
        fputcsv($output, ['CLI001', 'Sample Company Ltd', 'Company', 'John', 'Smith', 'john@sample.com', '+44 1234 567890', '12345678', 'ABC123', '1234567890', '1', '31/03/2024', 'Y', 'N', 'Y', 'Y', 'MJSD']);
        
        fclose($output);
        exit;
    } elseif ($table === 'jobs') {
        // Generate jobs CSV template
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="jobs_template.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Write header row
        fputcsv($output, ['client_id', 'client_reference', 'task_id', 'description', 'budget_hours', 'state_id', 'urgent', 'partner_id', 'manager_id', 'preparer_id', 'period_end', 'deadline_date', 'expected_completion_date', 'received_date', 'assigned_date', 'completed_date', 'reviewed_date', 'sent_to_client_date', 'approved_date', 'submitted_date', 'archived_date', 'comments']);
        
        // Write sample data row
        fputcsv($output, [1, 'CLI001', 1, 'Sample job description', '10.50', 1, '0', '', '', '', '31/03/2024', '31/12/2024', '15/12/2024', '15/01/2024', '16/01/2024', '', '', '', '', '', '', '', 'Sample comments']);
        
        fclose($output);
        exit;
    }
}

// Field validation functions
function validateClientField($fieldName, $value, $allData = []) {
    $errors = [];
    
    switch ($fieldName) {
        case 'reference':
            if (empty(trim($value))) {
                $errors[] = 'Reference is required';
            }
            break;
        case 'name':
            if (empty(trim($value))) {
                $errors[] = 'Name is required';
            }
            break;
        case 'email':
            if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }
            break;
        case 'company_number':
            if (!empty($value) && (strlen($value) > 8 || !ctype_digit($value))) {
                $errors[] = 'Company Number must be 8 digits or less';
            }
            break;
        case 'authentication_code':
            if (!empty($value) && strlen($value) > 6) {
                $errors[] = 'Authentication Code must be 6 characters or less';
            }
            break;
        case 'utr_number':
            if (!empty($value) && (strlen($value) > 10 || !ctype_digit($value))) {
                $errors[] = 'UTR Number must be 10 digits or less';
            }
            break;
        case 'vat_periods':
            if (!empty($allData[14]) && $allData[14] === 'Y' && empty($value)) {
                $errors[] = 'VAT Periods is required when VAT is set to Yes';
            }
            break;
        case 'year_end':
            $dateErrors = validateDateFormat($value, 'Year End');
            $errors = array_merge($errors, $dateErrors);
            break;
    }
    
    return $errors;
}

function validateJobField($fieldName, $value, $allData = [], $pdo = null) {
    $errors = [];
    
    switch ($fieldName) {
        case 'client_id':
            if (empty($value)) {
                $errors[] = 'Client ID is required';
            } elseif (!is_numeric($value)) {
                $errors[] = 'Client ID must be a number';
            } elseif ($pdo) {
                // Check if client exists
                try {
                    $stmt = $pdo->prepare("SELECT id FROM clients WHERE id = ?");
                    $stmt->execute([$value]);
                    if (!$stmt->fetch()) {
                        $errors[] = "Client ID $value does not exist in the clients table";
                    }
                } catch (Exception $e) {
                    // Skip validation if database check fails
                }
            }
            break;
        case 'task_id':
            if (empty($value)) {
                $errors[] = 'Task ID is required';
            } elseif (!is_numeric($value)) {
                $errors[] = 'Task ID must be a number';
            } elseif ($pdo) {
                // Check if task exists
                try {
                    $stmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ?");
                    $stmt->execute([$value]);
                    if (!$stmt->fetch()) {
                        $errors[] = "Task ID $value does not exist in the tasks table";
                    }
                } catch (Exception $e) {
                    // Skip validation if database check fails
                }
            }
            break;
        case 'state_id':
            if (empty($value)) {
                $errors[] = 'State ID is required';
            } elseif (!is_numeric($value)) {
                $errors[] = 'State ID must be a number';
            } elseif ($pdo) {
                // Check if state exists
                try {
                    $stmt = $pdo->prepare("SELECT id FROM states WHERE id = ?");
                    $stmt->execute([$value]);
                    if (!$stmt->fetch()) {
                        $errors[] = "State ID $value does not exist in the states table";
                    }
                } catch (Exception $e) {
                    // Skip validation if database check fails
                }
            }
            break;
        case 'partner_id':
        case 'manager_id':
        case 'preparer_id':
            if (!empty($value) && $pdo) {
                // Check if user exists
                try {
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
                    $stmt->execute([$value]);
                    if (!$stmt->fetch()) {
                        $fieldDisplayName = ucfirst(str_replace('_', ' ', $fieldName));
                        $errors[] = "$fieldDisplayName ID $value does not exist in the users table";
                    }
                } catch (Exception $e) {
                    // Skip validation if database check fails
                }
            }
            break;
        case 'budget_hours':
            if (!empty($value) && (!is_numeric($value) || $value < 0)) {
                $errors[] = 'Budget Hours must be a positive number';
            }
            break;
        case 'urgent':
            if (!empty($value) && !in_array($value, ['0', '1'])) {
                $errors[] = 'Urgent must be 0 or 1';
            }
            break;
        case 'period_end':
        case 'deadline_date':
        case 'expected_completion_date':
        case 'received_date':
        case 'assigned_date':
        case 'completed_date':
        case 'reviewed_date':
        case 'sent_to_client_date':
        case 'approved_date':
        case 'submitted_date':
        case 'archived_date':
            $fieldDisplayName = ucfirst(str_replace('_', ' ', $fieldName));
            $dateErrors = validateDateFormat($value, $fieldDisplayName);
            $errors = array_merge($errors, $dateErrors);
            break;
    }
    
    return $errors;
}

function getFriendlyDatabaseError($errorMessage) {
    // Parse common database constraint errors and provide user-friendly messages
    if (strpos($errorMessage, 'Foreign key constraint fails') !== false) {
        if (strpos($errorMessage, 'task_id') !== false) {
            return 'Task ID does not exist in the tasks table. Please check that the task ID exists before importing.';
        } elseif (strpos($errorMessage, 'client_id') !== false) {
            return 'Client ID does not exist in the clients table. Please check that the client ID exists before importing.';
        } elseif (strpos($errorMessage, 'state_id') !== false) {
            return 'State ID does not exist in the states table. Please check that the state ID exists before importing.';
        } elseif (strpos($errorMessage, 'partner_id') !== false || strpos($errorMessage, 'manager_id') !== false || strpos($errorMessage, 'preparer_id') !== false) {
            return 'User ID does not exist in the users table. Please check that the user ID exists before importing.';
        } else {
            return 'Foreign key constraint violation. One or more referenced IDs do not exist in the related tables.';
        }
    } elseif (strpos($errorMessage, 'Duplicate entry') !== false) {
        return 'Duplicate entry detected. This record already exists in the database.';
    } elseif (strpos($errorMessage, 'Data too long') !== false) {
        return 'Data too long for one or more fields. Please check field lengths.';
    }
    
    return $errorMessage; // Return original message if no specific handling
}

function convertDateFormat($dateString) {
    if (empty($dateString)) {
        return null;
    }
    
    // Handle dd/mm/yyyy format
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateString, $matches)) {
        $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year = $matches[3];
        
        // Validate the date
        if (checkdate($month, $day, $year)) {
            return "$year-$month-$day";
        }
    }
    
    // Handle yyyy-mm-dd format (already correct)
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
        return $dateString;
    }
    
    // Handle other common formats if needed
    // dd-mm-yyyy
    if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $dateString, $matches)) {
        $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year = $matches[3];
        
        if (checkdate($month, $day, $year)) {
            return "$year-$month-$day";
        }
    }
    
    // If no valid format found, return null (will be treated as invalid)
    return null;
}

function validateDateFormat($dateString, $fieldName) {
    if (empty($dateString)) {
        return []; // Empty dates are allowed
    }
    
    $convertedDate = convertDateFormat($dateString);
    if ($convertedDate === null) {
        return ["$fieldName must be in dd/mm/yyyy or yyyy-mm-dd format"];
    }
    
    return []; // Valid date
}

// Handle CSV file import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    $table = $_POST['table'];
    $uploadedFile = $_FILES['csv_file'];
    
    if ($uploadedFile['error'] === UPLOAD_ERR_OK) {
        $csvFile = $uploadedFile['tmp_name'];
        $handle = fopen($csvFile, 'r');
        
        if ($handle !== FALSE) {
            $successCount = 0;
            $errorCount = 0;
            $errorRows = [];
            $rowNumber = 0;
            
            // Skip header row
            $header = fgetcsv($handle);
            $rowNumber++;
            
            try {
                $pdo->beginTransaction();
                
                while (($data = fgetcsv($handle)) !== FALSE) {
                    $rowNumber++;
                    $rowErrors = [];
                    
                    // Validate fields based on table type
                    if ($table === 'clients') {
                        $fieldNames = ['reference', 'name', 'type', 'contact_forename', 'contact_surname', 'email', 'phone', 'company_number', 'authentication_code', 'utr_number', 'partner_id', 'year_end', 'year_end_work', 'payroll', 'directors_sa', 'vat', 'vat_periods'];
                        
                        foreach ($fieldNames as $index => $fieldName) {
                            $value = $data[$index] ?? '';
                            $fieldErrors = validateClientField($fieldName, $value, $data);
                            if (!empty($fieldErrors)) {
                                $rowErrors[$fieldName] = $fieldErrors;
                            }
                        }
                        
                        // Check for duplicate reference
                        if (!empty($data[0])) {
                            $stmt = $pdo->prepare("SELECT id FROM clients WHERE reference = ?");
                            $stmt->execute([$data[0]]);
                            if ($stmt->fetch()) {
                                $rowErrors['reference'] = ['Reference already exists'];
                            }
                        }
                        
                    } elseif ($table === 'jobs') {
                        $fieldNames = ['client_id', 'client_reference', 'task_id', 'description', 'budget_hours', 'state_id', 'urgent', 'partner_id', 'manager_id', 'preparer_id', 'period_end', 'deadline_date', 'expected_completion_date', 'received_date', 'assigned_date', 'completed_date', 'reviewed_date', 'sent_to_client_date', 'approved_date', 'submitted_date', 'archived_date', 'comments'];
                        
                        foreach ($fieldNames as $index => $fieldName) {
                            $value = $data[$index] ?? '';
                            $fieldErrors = validateJobField($fieldName, $value, $data, $pdo);
                            if (!empty($fieldErrors)) {
                                $rowErrors[$fieldName] = $fieldErrors;
                            }
                        }
                    }
                    
                    // If there are validation errors, record them
                    if (!empty($rowErrors)) {
                        $errorCount++;
                        $errorRows[] = [
                            'row' => $rowNumber,
                            'errors' => $rowErrors,
                            'data' => $data
                        ];
                        continue;
                    }
                    
                    // Try to insert the row
                    try {
                        if ($table === 'clients') {
                            $stmt = $pdo->prepare("INSERT INTO clients (reference, name, type, contact_forename, contact_surname, email, phone, company_number, authentication_code, utr_number, partner_id, year_end_work, payroll, directors_sa, vat, vat_periods, year_end) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([
                                $data[0] ?? '', // reference
                                $data[1] ?? '', // name
                                $data[2] ?? 'Company', // type
                                $data[3] ?? '', // contact_forename
                                $data[4] ?? '', // contact_surname
                                $data[5] ?? '', // email
                                $data[6] ?? '', // phone
                                $data[7] ?? '', // company_number
                                $data[8] ?? '', // authentication_code
                                $data[9] ?? '', // utr_number
                                !empty($data[10]) ? $data[10] : null, // partner_id
                                !empty($data[12]) ? $data[12] : 'N', // year_end_work
                                !empty($data[13]) ? $data[13] : 'N', // payroll
                                !empty($data[14]) ? $data[14] : 'N', // directors_sa
                                !empty($data[15]) ? $data[15] : 'N', // vat
                                $data[16] ?? '', // vat_periods
                                convertDateFormat($data[11] ?? '') // year_end - convert date format
                            ]);
                            $successCount++;
                        } elseif ($table === 'jobs') {
                            $stmt = $pdo->prepare("INSERT INTO jobs (client_id, client_reference, task_id, description, budget_hours, state_id, urgent, partner_id, manager_id, preparer_id, period_end, deadline_date, expected_completion_date, received_date, assigned_date, completed_date, reviewed_date, sent_to_client_date, approved_date, submitted_date, archived_date, comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            
                            $stmt->execute([
                                !empty($data[0]) ? $data[0] : null, // client_id
                                $data[1] ?? '', // client_reference
                                !empty($data[2]) ? $data[2] : null, // task_id
                                $data[3] ?? '', // description
                                !empty($data[4]) ? $data[4] : null, // budget_hours
                                !empty($data[5]) ? $data[5] : null, // state_id
                                isset($data[6]) && $data[6] === '1' ? 1 : 0, // urgent
                                !empty($data[7]) ? $data[7] : null, // partner_id
                                !empty($data[8]) ? $data[8] : null, // manager_id
                                !empty($data[9]) ? $data[9] : null, // preparer_id
                                convertDateFormat($data[10] ?? ''), // period_end - convert date format
                                convertDateFormat($data[11] ?? ''), // deadline_date - convert date format
                                convertDateFormat($data[12] ?? ''), // expected_completion_date - convert date format
                                convertDateFormat($data[13] ?? ''), // received_date - convert date format
                                convertDateFormat($data[14] ?? ''), // assigned_date - convert date format
                                convertDateFormat($data[15] ?? ''), // completed_date - convert date format
                                convertDateFormat($data[16] ?? ''), // reviewed_date - convert date format
                                convertDateFormat($data[17] ?? ''), // sent_to_client_date - convert date format
                                convertDateFormat($data[18] ?? ''), // approved_date - convert date format
                                convertDateFormat($data[19] ?? ''), // submitted_date - convert date format
                                convertDateFormat($data[20] ?? ''), // archived_date - convert date format
                                $data[21] ?? '' // comments
                            ]);
                            $successCount++;
                        }
                    } catch (Exception $e) {
                        $errorCount++;
                        $friendlyError = getFriendlyDatabaseError($e->getMessage());
                        $errorRows[] = [
                            'row' => $rowNumber,
                            'errors' => ['database_error' => [$friendlyError]],
                            'data' => $data
                        ];
                    }
                }
                
                $pdo->commit();
                
                if ($errorCount === 0) {
                    $message = "Successfully imported $successCount rows.";
                    $messageType = 'success';
                } else {
                    $message = "Imported $successCount rows successfully. $errorCount rows had errors.";
                    $messageType = 'warning';
                }
                
            } catch (Exception $e) {
                $pdo->rollback();
                $message = "Import failed: " . $e->getMessage();
                $messageType = 'error';
                $errorRows = [];
            }
            
            fclose($handle);
        } else {
            $message = "Could not read the uploaded file.";
            $messageType = 'error';
        }
    } else {
        $message = "File upload failed.";
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Import Data - Cursor1</title>
    <link rel="icon" type="image/png" href="../assets/images/RJA-icon Blue.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-file-import"></i>
                <span>Import Data</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../home" class="nav-link">Home</a>
                </li>
                <li class="nav-item">
                    <a href="../settings" class="nav-link">Settings</a>
                </li>
                <li class="nav-item">
                    <a href="../practice" class="nav-link">Practice Portal</a>
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
        <div class="practice-section">
            <div class="container">
                <div class="practice-header">
                    <h1 class="practice-title">Import Data</h1>
                    <p class="practice-subtitle">Import clients and jobs from CSV files</p>
                </div>

                <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'warning' ? 'exclamation-triangle' : 'times-circle'); ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($errorRows)): ?>
                <div class="error-details">
                    <h3><i class="fas fa-exclamation-triangle"></i> Detailed Error Report</h3>
                    <p>Click on any row to see the specific field errors:</p>
                    
                    <div class="error-rows">
                        <?php foreach ($errorRows as $errorRow): ?>
                        <div class="error-row" onclick="toggleErrorDetails(<?php echo $errorRow['row']; ?>)">
                            <div class="error-row-header">
                                <span class="row-number">Row <?php echo $errorRow['row']; ?></span>
                                <span class="error-count"><?php echo count($errorRow['errors']); ?> error(s)</span>
                                <i class="fas fa-chevron-down toggle-icon" id="icon-<?php echo $errorRow['row']; ?>"></i>
                            </div>
                            
                            <div class="error-details-content" id="details-<?php echo $errorRow['row']; ?>" style="display: none;" onclick="event.stopPropagation();">
                                <div class="error-data-preview">
                                    <strong>Data:</strong>
                                    <div class="data-preview">
                                        <?php 
                                        $fieldNames = ($table === 'clients') ? 
                                            ['Reference', 'Name', 'Type', 'Contact Forename', 'Contact Surname', 'Email', 'Phone', 'Company Number', 'Auth Code', 'UTR', 'Partner ID', 'Year End', 'Year End Work', 'Payroll', 'Directors SA', 'VAT', 'VAT Periods'] :
                                            ['Client ID', 'Client Ref', 'Task ID', 'Description', 'Budget Hours', 'State ID', 'Urgent', 'Partner ID', 'Manager ID', 'Preparer ID', 'Period End', 'Deadline', 'Expected', 'Received', 'Assigned', 'Completed', 'Reviewed', 'Sent to Client', 'Approved', 'Submitted', 'Archived', 'Comments'];
                                        
                                        $fieldKeys = ($table === 'clients') ? 
                                            ['reference', 'name', 'type', 'contact_forename', 'contact_surname', 'email', 'phone', 'company_number', 'authentication_code', 'utr_number', 'partner_id', 'year_end', 'year_end_work', 'payroll', 'directors_sa', 'vat', 'vat_periods'] :
                                            ['client_id', 'client_reference', 'task_id', 'description', 'budget_hours', 'state_id', 'urgent', 'partner_id', 'manager_id', 'preparer_id', 'period_end', 'deadline_date', 'expected_completion_date', 'received_date', 'assigned_date', 'completed_date', 'reviewed_date', 'sent_to_client_date', 'approved_date', 'submitted_date', 'archived_date', 'comments'];
                                        
                                        foreach ($errorRow['data'] as $index => $value): 
                                            if (isset($fieldNames[$index])):
                                                $fieldKey = $fieldKeys[$index] ?? '';
                                                $hasError = !empty($fieldKey) && isset($errorRow['errors'][$fieldKey]);
                                        ?>
                                        <div class="data-field <?php echo $hasError ? 'has-error' : ''; ?>">
                                            <strong><?php echo htmlspecialchars($fieldNames[$index]); ?>:</strong>
                                            <span><?php echo htmlspecialchars($value ?: '(empty)'); ?></span>
                                            <?php if ($hasError): ?>
                                            <div class="field-error-indicator">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="field-errors">
                                    <strong>Field Errors:</strong>
                                    <?php if (is_array($errorRow['errors'])): ?>
                                        <?php foreach ($errorRow['errors'] as $fieldName => $fieldErrors): ?>
                                        <div class="field-error">
                                            <span class="field-name"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $fieldName))); ?>:</span>
                                            <ul class="error-list">
                                                <?php if (is_array($fieldErrors)): ?>
                                                    <?php foreach ($fieldErrors as $error): ?>
                                                    <li><?php echo htmlspecialchars($error); ?></li>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <li><?php echo htmlspecialchars($fieldErrors); ?></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="field-error">
                                            <span class="field-name">Error:</span>
                                            <ul class="error-list">
                                                <li><?php echo htmlspecialchars($errorRow['errors']); ?></li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Debug information (remove this in production) -->
                                    <div class="debug-info" style="margin-top: 15px; padding: 10px; background: #e9ecef; border-radius: 4px; font-size: 12px;">
                                        <strong>Debug Info:</strong><br>
                                        Field Keys: <?php echo htmlspecialchars(implode(', ', $fieldKeys)); ?><br>
                                        <?php if (is_array($errorRow['errors'])): ?>
                                        Error Fields: <?php echo htmlspecialchars(implode(', ', array_keys($errorRow['errors']))); ?><br>
                                        <?php else: ?>
                                        Error Type: <?php echo gettype($errorRow['errors']); ?><br>
                                        <?php endif; ?>
                                        Data Count: <?php echo count($errorRow['data']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="import-container">
                    <div class="import-card">
                        <h3><i class="fas fa-upload"></i> Import CSV File</h3>
                        
                        <form method="POST" enctype="multipart/form-data" class="import-form">
                            <div class="form-group">
                                <label for="table">Table to import:</label>
                                <select name="table" id="table" required class="form-control">
                                    <option value="">Select a table...</option>
                                    <option value="clients">Clients</option>
                                    <option value="jobs">Jobs</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="csv_file">Choose File:</label>
                                <div class="file-input-container">
                                    <input type="file" name="csv_file" id="csv_file" accept=".csv" required class="file-input">
                                    <label for="csv_file" class="file-input-label">
                                        <i class="fas fa-folder-open"></i>
                                        <span>Choose CSV File</span>
                                    </label>
                                    <div id="file-name" class="file-name"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" name="import" class="btn btn-primary">
                                    <i class="fas fa-upload"></i>
                                    Import
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="template-card">
                        <h3><i class="fas fa-download"></i> Download Templates</h3>
                        <p>Download CSV templates to see the required format for importing data.</p>
                        <div class="format-info">
                            <strong>Supported Date Formats:</strong>
                            <ul>
                                <li>dd/mm/yyyy (e.g., 31/12/2024)</li>
                                <li>yyyy-mm-dd (e.g., 2024-12-31)</li>
                                <li>dd-mm-yyyy (e.g., 31-12-2024)</li>
                            </ul>
                        </div>
                        
                        <div class="template-buttons">
                            <a href="?template=clients" class="btn btn-secondary">
                                <i class="fas fa-download"></i>
                                Clients Template
                            </a>
                            <a href="?template=jobs" class="btn btn-secondary">
                                <i class="fas fa-download"></i>
                                Jobs Template
                            </a>
                        </div>
                    </div>

                    <div class="reference-card">
                        <h3><i class="fas fa-info-circle"></i> Reference Data</h3>
                        <p>Use these valid IDs when importing jobs:</p>
                        
                        <div class="reference-sections">
                            <div class="reference-section">
                                <strong>Available Clients:</strong>
                                <div class="reference-list">
                                    <?php
                                    try {
                                        $stmt = $pdo->query("SELECT id, reference, name FROM clients ORDER BY reference LIMIT 10");
                                        $clients = $stmt->fetchAll();
                                        foreach ($clients as $client) {
                                            echo "<div class='reference-item'>ID: {$client['id']} - {$client['reference']} ({$client['name']})</div>";
                                        }
                                        if (count($clients) == 10) {
                                            echo "<div class='reference-item'><em>... and more (showing first 10)</em></div>";
                                        }
                                    } catch (Exception $e) {
                                        echo "<div class='reference-item'><em>Unable to load clients</em></div>";
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="reference-section">
                                <strong>Available Tasks:</strong>
                                <div class="reference-list">
                                    <?php
                                    try {
                                        $stmt = $pdo->query("SELECT id, task_name FROM tasks ORDER BY task_name LIMIT 10");
                                        $tasks = $stmt->fetchAll();
                                        foreach ($tasks as $task) {
                                            echo "<div class='reference-item'>ID: {$task['id']} - {$task['task_name']}</div>";
                                        }
                                        if (count($tasks) == 10) {
                                            echo "<div class='reference-item'><em>... and more (showing first 10)</em></div>";
                                        }
                                    } catch (Exception $e) {
                                        echo "<div class='reference-item'><em>Unable to load tasks</em></div>";
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="reference-section">
                                <strong>Available States:</strong>
                                <div class="reference-list">
                                    <?php
                                    try {
                                        $stmt = $pdo->query("SELECT id, state_name FROM states ORDER BY state_name LIMIT 10");
                                        $states = $stmt->fetchAll();
                                        foreach ($states as $state) {
                                            echo "<div class='reference-item'>ID: {$state['id']} - {$state['state_name']}</div>";
                                        }
                                        if (count($states) == 10) {
                                            echo "<div class='reference-item'><em>... and more (showing first 10)</em></div>";
                                        }
                                    } catch (Exception $e) {
                                        echo "<div class='reference-item'><em>Unable to load states</em></div>";
                                    }
                                    ?>
                                </div>
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
        .practice-section {
            padding-top: 20px !important;
        }
        
        /* Import page styles */
        .import-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .reference-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            margin-top: 20px;
        }
        
        .reference-card h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .reference-card h3 i {
            color: #17a2b8;
            margin-right: 10px;
        }
        
        .reference-sections {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .reference-section {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            background: #f8f9fa;
        }
        
        .reference-section strong {
            color: #495057;
            display: block;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .reference-list {
            max-height: 150px;
            overflow-y: auto;
        }
        
        .reference-item {
            padding: 4px 8px;
            font-size: 12px;
            color: #666;
            border-bottom: 1px solid #dee2e6;
        }
        
        .reference-item:last-child {
            border-bottom: none;
        }
        
        .reference-item em {
            color: #999;
            font-style: italic;
        }
        
        .import-card, .template-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
        }
        
        .import-card h3, .template-card h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .import-card h3 i, .template-card h3 i {
            color: #667eea;
            margin-right: 10px;
        }
        
        .import-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #333;
        }
        
        .form-control {
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .file-input-container {
            position: relative;
        }
        
        .file-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border: 2px dashed #667eea;
            border-radius: 8px;
            background: #f8f9ff;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            color: #667eea;
        }
        
        .file-input-label:hover {
            background: #667eea;
            color: white;
        }
        
        .file-input-label i {
            margin-right: 8px;
            font-size: 1.1rem;
        }
        
        .file-name {
            margin-top: 10px;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 6px;
            font-size: 14px;
            color: #666;
            display: none;
        }
        
        .file-name.show {
            display: block;
        }
        
        .template-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .template-card p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        
        .format-info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .format-info strong {
            color: #0066cc;
            display: block;
            margin-bottom: 8px;
        }
        
        .format-info ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .format-info li {
            color: #666;
            margin-bottom: 4px;
            font-size: 14px;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        /* Error details styles */
        .error-details {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #e9ecef;
            margin-bottom: 30px;
        }
        
        .error-details h3 {
            color: #dc3545;
            margin-bottom: 15px;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .error-details h3 i {
            margin-right: 10px;
        }
        
        .error-rows {
            margin-top: 20px;
        }
        
        .error-row {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .error-row:hover {
            border-color: #dc3545;
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.1);
        }
        
        .error-row-header {
            padding: 15px 20px;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 8px 8px 0 0;
        }
        
        .row-number {
            font-weight: 600;
            color: #333;
        }
        
        .error-count {
            background: #dc3545;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .toggle-icon {
            color: #666;
            transition: transform 0.3s ease;
        }
        
        .error-row.expanded .toggle-icon {
            transform: rotate(180deg);
        }
        
        .error-details-content {
            padding: 20px;
            background: white;
            border-top: 1px solid #e9ecef;
        }
        
        .error-data-preview {
            margin-bottom: 20px;
        }
        
        .data-preview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        
        .data-field {
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .data-field.has-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            position: relative;
        }
        
        .data-field strong {
            color: #333;
            margin-right: 8px;
        }
        
        .field-error-indicator {
            position: absolute;
            top: 5px;
            right: 8px;
            color: #dc3545;
            font-size: 12px;
        }
        
        .field-errors {
            margin-top: 20px;
        }
        
        .field-error {
            margin-bottom: 15px;
            padding: 12px;
            background: #f8d7da;
            border-radius: 6px;
            border-left: 4px solid #dc3545;
        }
        
        .field-name {
            font-weight: 600;
            color: #721c24;
            display: block;
            margin-bottom: 8px;
        }
        
        .error-list {
            margin: 0;
            padding-left: 20px;
        }
        
        .error-list li {
            color: #721c24;
            margin-bottom: 4px;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .import-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .import-card, .template-card {
                padding: 20px;
            }
            
            .template-buttons {
                flex-direction: column;
            }
            
            .error-details {
                padding: 20px;
            }
            
            .data-preview {
                grid-template-columns: 1fr;
            }
            
            .error-row-header {
                padding: 12px 15px;
                flex-direction: column;
                gap: 8px;
                text-align: center;
            }
        }
    </style>

    <script>
        // Handle file selection display
        document.getElementById('csv_file').addEventListener('change', function(e) {
            const fileName = e.target.files[0];
            const fileNameDiv = document.getElementById('file-name');
            
            if (fileName) {
                fileNameDiv.textContent = 'Selected: ' + fileName.name;
                fileNameDiv.classList.add('show');
            } else {
                fileNameDiv.classList.remove('show');
            }
        });

        // Handle table selection for template downloads
        document.getElementById('table').addEventListener('change', function(e) {
            // You could add logic here to show/hide relevant template buttons
        });

        // Toggle error details
        function toggleErrorDetails(rowNumber) {
            const detailsElement = document.getElementById('details-' + rowNumber);
            const iconElement = document.getElementById('icon-' + rowNumber);
            const rowElement = detailsElement.closest('.error-row');
            
            if (detailsElement.style.display === 'none') {
                detailsElement.style.display = 'block';
                rowElement.classList.add('expanded');
            } else {
                detailsElement.style.display = 'none';
                rowElement.classList.remove('expanded');
            }
        }
    </script>
</body>
</html>
