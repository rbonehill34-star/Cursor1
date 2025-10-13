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
        fputcsv($output, ['reference', 'name', 'type', 'contact', 'email', 'phone', 'company_number', 'authentication_code', 'utr_number', 'partner_id', 'year_end', 'year_end_work', 'payroll', 'directors_sa', 'vat', 'vat_periods']);
        
        // Write sample data row
        fputcsv($output, ['CLI001', 'Sample Company Ltd', 'Company', 'John Smith', 'john@sample.com', '+44 1234 567890', '12345678', 'ABC123', '1234567890', '1', '2024-03-31', 'Y', 'N', 'Y', 'Y', 'MJSD']);
        
        fclose($output);
        exit;
    } elseif ($table === 'jobs') {
        // Generate jobs CSV template
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="jobs_template.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Write header row
        fputcsv($output, ['client_id', 'client_reference', 'task_id', 'description', 'budget_hours', 'state_id', 'urgent', 'partner_id', 'manager_id', 'preparer_id', 'deadline_date', 'expected_completion_date', 'received_date', 'assigned_date', 'completed_date', 'reviewed_date', 'sent_to_client_date', 'approved_date', 'submitted_date', 'archived_date', 'comments']);
        
        // Write sample data row
        fputcsv($output, [1, 'CLI001', 1, 'Sample job description', '10.50', 1, '0', '', '', '', '2024-12-31', '2024-12-15', '2024-01-15', '2024-01-16', '', '', '', '', '', '', '', 'Sample comments']);
        
        fclose($output);
        exit;
    }
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
                    
                    try {
                        if ($table === 'clients') {
                            // Import clients data
                            $stmt = $pdo->prepare("INSERT INTO clients (reference, name, type, contact, email, phone, company_number, authentication_code, utr_number, partner_id, year_end, year_end_work, payroll, directors_sa, vat, vat_periods) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([
                                $data[0] ?? '', // reference
                                $data[1] ?? '', // name
                                $data[2] ?? 'Company', // type
                                $data[3] ?? '', // contact
                                $data[4] ?? '', // email
                                $data[5] ?? '', // phone
                                $data[6] ?? '', // company_number
                                $data[7] ?? '', // authentication_code
                                $data[8] ?? '', // utr_number
                                !empty($data[9]) ? $data[9] : null, // partner_id
                                !empty($data[10]) ? $data[10] : null, // year_end
                                !empty($data[11]) ? $data[11] : 'N', // year_end_work
                                !empty($data[12]) ? $data[12] : 'N', // payroll
                                !empty($data[13]) ? $data[13] : 'N', // directors_sa
                                !empty($data[14]) ? $data[14] : 'N', // vat
                                $data[15] ?? '' // vat_periods
                            ]);
                            $successCount++;
                        } elseif ($table === 'jobs') {
                            // Import jobs data
                            $stmt = $pdo->prepare("INSERT INTO jobs (client_id, client_reference, task_id, description, budget_hours, state_id, urgent, partner_id, manager_id, preparer_id, deadline_date, expected_completion_date, received_date, assigned_date, completed_date, reviewed_date, sent_to_client_date, approved_date, submitted_date, archived_date, comments) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            
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
                                !empty($data[10]) ? $data[10] : null, // deadline_date
                                !empty($data[11]) ? $data[11] : null, // expected_completion_date
                                !empty($data[12]) ? $data[12] : null, // received_date
                                !empty($data[13]) ? $data[13] : null, // assigned_date
                                !empty($data[14]) ? $data[14] : null, // completed_date
                                !empty($data[15]) ? $data[15] : null, // reviewed_date
                                !empty($data[16]) ? $data[16] : null, // sent_to_client_date
                                !empty($data[17]) ? $data[17] : null, // approved_date
                                !empty($data[18]) ? $data[18] : null, // submitted_date
                                !empty($data[19]) ? $data[19] : null, // archived_date
                                $data[20] ?? '' // comments
                            ]);
                            $successCount++;
                        }
                    } catch (Exception $e) {
                        $errorCount++;
                        $errorRows[] = $rowNumber;
                    }
                }
                
                $pdo->commit();
                
                if ($errorCount === 0) {
                    $message = "Successfully imported $successCount rows.";
                    $messageType = 'success';
                } else {
                    $message = "Imported $successCount rows successfully. $errorCount rows were rejected. Failed rows: " . implode(', ', $errorRows);
                    $messageType = 'warning';
                }
                
            } catch (Exception $e) {
                $pdo->rollback();
                $message = "Import failed: " . $e->getMessage();
                $messageType = 'error';
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
    </script>
</body>
</html>
