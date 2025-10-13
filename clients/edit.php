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
$client = null;

// Get client ID from URL
$client_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$client_id) {
    header('Location: index');
    exit;
}

// Handle delete action
if (isset($_POST['delete_client'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->execute([$client_id]);
        header('Location: index?deleted=1');
        exit;
    } catch (PDOException $e) {
        $message = 'Failed to delete client.';
        $messageType = 'error';
    }
}

// Handle update action
if (isset($_POST['update_client'])) {
    $reference = trim($_POST['reference'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $type = $_POST['type'] ?? 'Company';
    $contact = trim($_POST['contact'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $year_end = $_POST['year_end'] ?? '';
    $company_number = trim($_POST['company_number'] ?? '');
    $authentication_code = trim($_POST['authentication_code'] ?? '');
    $utr_number = trim($_POST['utr_number'] ?? '');
    $partner_id = $_POST['partner_id'] ?? null;
    $year_end_work = $_POST['year_end_work'] ?? 'N';
    $payroll = $_POST['payroll'] ?? 'N';
    $directors_sa = $_POST['directors_sa'] ?? 'N';
    $vat = $_POST['vat'] ?? 'N';
    $vat_periods = $_POST['vat_periods'] ?? null;
    // Ensure vat_periods is null when VAT is N
    if ($vat === 'N') {
        $vat_periods = null;
    }
    
    // Validation
    if (empty($reference) || empty($name)) {
        $message = 'Reference and name are required fields.';
        $messageType = 'error';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } elseif (!empty($company_number) && (strlen($company_number) > 8 || !ctype_digit($company_number))) {
        $message = 'Company Number must be 8 digits or less.';
        $messageType = 'error';
    } elseif (!empty($authentication_code) && strlen($authentication_code) > 6) {
        $message = 'Authentication Code must be 6 characters or less.';
        $messageType = 'error';
    } elseif (!empty($utr_number) && (strlen($utr_number) > 10 || !ctype_digit($utr_number))) {
        $message = 'UTR Number must be 10 digits or less.';
        $messageType = 'error';
    } elseif ($vat === 'Y' && empty($vat_periods)) {
        $message = 'VAT Periods is required when VAT is set to Yes.';
        $messageType = 'error';
    } else {
        try {
            // Check if reference already exists for a different client
            $stmt = $pdo->prepare("SELECT id FROM clients WHERE reference = ? AND id != ?");
            $stmt->execute([$reference, $client_id]);
            
            if ($stmt->fetch()) {
                $message = 'Reference already exists. Please choose a different one.';
                $messageType = 'error';
            } else {
                // Update client
                $stmt = $pdo->prepare("UPDATE clients SET reference = ?, name = ?, type = ?, contact = ?, email = ?, phone = ?, year_end = ?, company_number = ?, authentication_code = ?, utr_number = ?, partner_id = ?, year_end_work = ?, payroll = ?, directors_sa = ?, vat = ?, vat_periods = ? WHERE id = ?");
                $stmt->execute([$reference, $name, $type, $contact, $email, $phone, $year_end ?: null, $company_number ?: null, $authentication_code ?: null, $utr_number ?: null, $partner_id ?: null, $year_end_work, $payroll, $directors_sa, $vat, $vat_periods, $client_id]);
                
                $message = 'Client updated successfully!';
                $messageType = 'success';
            }
        } catch (PDOException $e) {
            $message = 'Failed to update client: ' . $e->getMessage();
            $messageType = 'error';
            // Log the error for debugging
            error_log("Client update error: " . $e->getMessage() . " - SQL: " . $e->getTraceAsString());
        }
    }
}

// Get client data
try {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch();
    
    if (!$client) {
        header('Location: index');
        exit;
    }
} catch (PDOException $e) {
    header('Location: index');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Edit Client - Cursor1</title>
    <link rel="icon" type="image/png" href="../assets/images/RJA-icon Blue.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-edit"></i>
                <span>Edit Client</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../practice" class="nav-link">Practice Portal</a>
                </li>
                <li class="nav-item">
                    <a href="index" class="nav-link">Back to Clients</a>
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
                    <h1 class="page-title">Edit Client</h1>
                    <div class="page-actions">
                        <a href="index" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Back to Clients
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
                    <form method="POST" action="">
                        <div class="form-layout">
                            <div class="form-group">
                                <label for="reference" class="form-label">
                                    <i class="fas fa-hashtag"></i>
                                    Reference *
                                </label>
                                <input type="text" id="reference" name="reference" class="form-input" 
                                       value="<?php echo htmlspecialchars($client['reference']); ?>" 
                                       placeholder="e.g., CLI001" required>
                            </div>

                            <div class="form-group">
                                <label for="name" class="form-label">
                                    <i class="fas fa-building"></i>
                                    Client Name *
                                </label>
                                <input type="text" id="name" name="name" class="form-input" 
                                       value="<?php echo htmlspecialchars($client['name']); ?>" 
                                       placeholder="Enter client name" required>
                            </div>

                            <div class="form-group">
                                <label for="type" class="form-label">
                                    <i class="fas fa-tag"></i>
                                    Type *
                                </label>
                                <select id="type" name="type" class="form-input" required>
                                    <option value="Company" <?php echo (($client['type'] ?? 'Company') === 'Company') ? 'selected' : ''; ?>>Company</option>
                                    <option value="Individual" <?php echo (($client['type'] ?? 'Company') === 'Individual') ? 'selected' : ''; ?>>Individual</option>
                                    <option value="Partnership" <?php echo (($client['type'] ?? 'Company') === 'Partnership') ? 'selected' : ''; ?>>Partnership</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="contact" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Contact Person
                                </label>
                                <input type="text" id="contact" name="contact" class="form-input" 
                                       value="<?php echo htmlspecialchars($client['contact'] ?? ''); ?>" 
                                       placeholder="Contact person name">
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address
                                </label>
                                <input type="email" id="email" name="email" class="form-input" 
                                       value="<?php echo htmlspecialchars($client['email'] ?? ''); ?>" 
                                       placeholder="contact@company.com">
                            </div>

                            <div class="form-group">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone"></i>
                                    Phone Number
                                </label>
                                <input type="tel" id="phone" name="phone" class="form-input" 
                                       value="<?php echo htmlspecialchars($client['phone'] ?? ''); ?>" 
                                       placeholder="+44 1234 567890">
                            </div>

                            <div class="form-group">
                                <label for="year_end" class="form-label">
                                    <i class="fas fa-calendar"></i>
                                    Year End Date
                                </label>
                                <input type="date" id="year_end" name="year_end" class="form-input" 
                                       value="<?php echo $client['year_end'] ? date('Y-m-d', strtotime($client['year_end'])) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="company_number" class="form-label">
                                    <i class="fas fa-hashtag"></i>
                                    Company Number
                                </label>
                                <input type="text" id="company_number" name="company_number" class="form-input" 
                                       value="<?php echo htmlspecialchars($client['company_number'] ?? ''); ?>" 
                                       placeholder="8 digit company number" maxlength="8" pattern="[0-9]{1,8}">
                            </div>

                            <div class="form-group">
                                <label for="authentication_code" class="form-label">
                                    <i class="fas fa-key"></i>
                                    Authentication Code
                                </label>
                                <input type="text" id="authentication_code" name="authentication_code" class="form-input" 
                                       value="<?php echo htmlspecialchars($client['authentication_code'] ?? ''); ?>" 
                                       placeholder="6 character code" maxlength="6">
                            </div>

                            <div class="form-group">
                                <label for="utr_number" class="form-label">
                                    <i class="fas fa-id-card"></i>
                                    UTR Number
                                </label>
                                <input type="text" id="utr_number" name="utr_number" class="form-input" 
                                       value="<?php echo htmlspecialchars($client['utr_number'] ?? ''); ?>" 
                                       placeholder="10 digit UTR number" maxlength="10" pattern="[0-9]{1,10}">
                            </div>

                            <div class="form-group">
                                <label for="partner_id" class="form-label">
                                    <i class="fas fa-user-tie"></i>
                                    Partner
                                </label>
                                <select id="partner_id" name="partner_id" class="form-input">
                                    <option value="">Select Partner</option>
                                    <?php
                                    try {
                                        $stmt = $pdo->query("SELECT id, username FROM users ORDER BY username ASC");
                                        $users = $stmt->fetchAll();
                                        foreach ($users as $user) {
                                            $selected = ($client['partner_id'] == $user['id']) ? 'selected' : '';
                                            echo "<option value=\"{$user['id']}\" $selected>" . htmlspecialchars($user['username']) . "</option>";
                                        }
                                    } catch (PDOException $e) {
                                        // Handle error silently
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="year_end_work" class="form-label">
                                    <i class="fas fa-calendar-check"></i>
                                    Year End Work
                                </label>
                                <select id="year_end_work" name="year_end_work" class="form-input">
                                    <option value="N" <?php echo ($client['year_end_work'] === 'N') ? 'selected' : ''; ?>>No</option>
                                    <option value="Y" <?php echo ($client['year_end_work'] === 'Y') ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="payroll" class="form-label">
                                    <i class="fas fa-money-bill-wave"></i>
                                    Payroll
                                </label>
                                <select id="payroll" name="payroll" class="form-input">
                                    <option value="N" <?php echo ($client['payroll'] === 'N') ? 'selected' : ''; ?>>No</option>
                                    <option value="Y" <?php echo ($client['payroll'] === 'Y') ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="directors_sa" class="form-label">
                                    <i class="fas fa-user-crown"></i>
                                    Directors SA
                                </label>
                                <select id="directors_sa" name="directors_sa" class="form-input">
                                    <option value="N" <?php echo ($client['directors_sa'] === 'N') ? 'selected' : ''; ?>>No</option>
                                    <option value="Y" <?php echo ($client['directors_sa'] === 'Y') ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="vat" class="form-label">
                                    <i class="fas fa-percentage"></i>
                                    VAT
                                </label>
                                <select id="vat" name="vat" class="form-input">
                                    <option value="N" <?php echo ($client['vat'] === 'N') ? 'selected' : ''; ?>>No</option>
                                    <option value="Y" <?php echo ($client['vat'] === 'Y') ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>

                            <div class="form-group" id="vat_periods_group" style="<?php echo ($client['vat'] === 'Y') ? '' : 'display: none;'; ?>">
                                <label for="vat_periods" class="form-label">
                                    <i class="fas fa-calendar-alt"></i>
                                    VAT Periods
                                </label>
                                <select id="vat_periods" name="vat_periods" class="form-input">
                                    <option value="">Select Period</option>
                                    <option value="MJSD" <?php echo ($client['vat_periods'] === 'MJSD') ? 'selected' : ''; ?>>MJSD</option>
                                    <option value="JAJO" <?php echo ($client['vat_periods'] === 'JAJO') ? 'selected' : ''; ?>>JAJO</option>
                                    <option value="FMAN" <?php echo ($client['vat_periods'] === 'FMAN') ? 'selected' : ''; ?>>FMAN</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="update_client" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Changes
                            </button>
                            <a href="index" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancel
                            </a>
                        </div>
                    </form>

                    <div class="form-section" style="margin-top: 30px; padding-top: 30px; border-top: 2px solid #e5e7eb;">
                        <h3 style="color: #dc2626; margin-bottom: 15px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            Danger Zone
                        </h3>
                        <p style="margin-bottom: 15px; color: #6b7280;">
                            Deleting this client will permanently remove all associated data. This action cannot be undone.
                        </p>
                        <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this client? This action cannot be undone.');">
                            <button type="submit" name="delete_client" class="btn btn-delete" style="background: #dc2626;">
                                <i class="fas fa-trash"></i>
                                Delete Client
                            </button>
                        </form>
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
        // Show/hide VAT periods field based on VAT selection
        document.getElementById('vat').addEventListener('change', function() {
            const vatPeriodsGroup = document.getElementById('vat_periods_group');
            const vatPeriodsSelect = document.getElementById('vat_periods');
            
            if (this.value === 'Y') {
                vatPeriodsGroup.style.display = 'block';
                vatPeriodsSelect.required = true;
            } else {
                vatPeriodsGroup.style.display = 'none';
                vatPeriodsSelect.required = false;
                vatPeriodsSelect.value = '';
            }
        });
        
        // Initialize VAT periods field state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const vatSelect = document.getElementById('vat');
            const vatPeriodsGroup = document.getElementById('vat_periods_group');
            const vatPeriodsSelect = document.getElementById('vat_periods');
            
            if (vatSelect.value === 'N') {
                vatPeriodsGroup.style.display = 'none';
                vatPeriodsSelect.required = false;
                vatPeriodsSelect.value = '';
            }
        });
    </script>
</body>
</html>

