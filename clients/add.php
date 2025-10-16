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

if ($_POST) {
    $reference = trim($_POST['reference'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $type = $_POST['type'] ?? 'Company';
    $contact_forename = trim($_POST['contact_forename'] ?? '');
    $contact_surname = trim($_POST['contact_surname'] ?? '');
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
            // Check if reference already exists
            $stmt = $pdo->prepare("SELECT id FROM clients WHERE reference = ?");
            $stmt->execute([$reference]);
            
            if ($stmt->fetch()) {
                $message = 'Reference already exists. Please choose a different one.';
                $messageType = 'error';
            } else {
                // Insert new client
                $stmt = $pdo->prepare("INSERT INTO clients (reference, name, type, contact_forename, contact_surname, email, phone, year_end, company_number, authentication_code, utr_number, partner_id, year_end_work, payroll, directors_sa, vat, vat_periods, date_added) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$reference, $name, $type, $contact_forename, $contact_surname, $email, $phone, $year_end ?: null, $company_number ?: null, $authentication_code ?: null, $utr_number ?: null, $partner_id ?: null, $year_end_work, $payroll, $directors_sa, $vat, $vat_periods]);
                
                $message = 'Client added successfully!';
                $messageType = 'success';
                
                // Clear form data
                $reference = $name = $type = $contact_forename = $contact_surname = $email = $phone = $year_end = $company_number = $authentication_code = $utr_number = $partner_id = $year_end_work = $payroll = $directors_sa = $vat = $vat_periods = '';
            }
        } catch (PDOException $e) {
            $message = 'Failed to add client: ' . $e->getMessage();
            $messageType = 'error';
            // Log the error for debugging
            error_log("Client add error: " . $e->getMessage() . " - SQL: " . $e->getTraceAsString());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Add Client - Cursor1</title>
    <link rel="icon" type="image/png" href="../assets/images/RJA-icon Blue.png">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-user-plus"></i>
                <span>Add Client</span>
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
                    <h1 class="page-title">Add New Client</h1>
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
                                       value="<?php echo htmlspecialchars($reference ?? ''); ?>" 
                                       placeholder="e.g., CLI001" required>
                            </div>

                            <div class="form-group">
                                <label for="name" class="form-label">
                                    <i class="fas fa-building"></i>
                                    Client Name *
                                </label>
                                <input type="text" id="name" name="name" class="form-input" 
                                       value="<?php echo htmlspecialchars($name ?? ''); ?>" 
                                       placeholder="Enter client name" required>
                            </div>

                            <div class="form-group">
                                <label for="type" class="form-label">
                                    <i class="fas fa-tag"></i>
                                    Type *
                                </label>
                                <select id="type" name="type" class="form-input" required>
                                    <option value="Company" <?php echo ($type === 'Company') ? 'selected' : ''; ?>>Company</option>
                                    <option value="Individual" <?php echo ($type === 'Individual') ? 'selected' : ''; ?>>Individual</option>
                                    <option value="Partnership" <?php echo ($type === 'Partnership') ? 'selected' : ''; ?>>Partnership</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="contact_forename" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Contact Forename
                                </label>
                                <input type="text" id="contact_forename" name="contact_forename" class="form-input" 
                                       value="<?php echo htmlspecialchars($contact_forename ?? ''); ?>" 
                                       placeholder="Contact forename">
                            </div>

                            <div class="form-group">
                                <label for="contact_surname" class="form-label">
                                    <i class="fas fa-user"></i>
                                    Contact Surname
                                </label>
                                <input type="text" id="contact_surname" name="contact_surname" class="form-input" 
                                       value="<?php echo htmlspecialchars($contact_surname ?? ''); ?>" 
                                       placeholder="Contact surname">
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope"></i>
                                    Email Address
                                </label>
                                <input type="email" id="email" name="email" class="form-input" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                                       placeholder="contact@company.com">
                            </div>

                            <div class="form-group">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone"></i>
                                    Phone Number
                                </label>
                                <input type="tel" id="phone" name="phone" class="form-input" 
                                       value="<?php echo htmlspecialchars($phone ?? ''); ?>" 
                                       placeholder="+44 1234 567890">
                            </div>

                            <div class="form-group">
                                <label for="year_end" class="form-label">
                                    <i class="fas fa-calendar"></i>
                                    Year End Date
                                </label>
                                <input type="date" id="year_end" name="year_end" class="form-input" 
                                       value="<?php echo htmlspecialchars($year_end ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="company_number" class="form-label">
                                    <i class="fas fa-hashtag"></i>
                                    Company Number
                                </label>
                                <input type="text" id="company_number" name="company_number" class="form-input" 
                                       value="<?php echo htmlspecialchars($company_number ?? ''); ?>" 
                                       placeholder="8 digit company number" maxlength="8" pattern="[0-9]{1,8}">
                            </div>

                            <div class="form-group">
                                <label for="authentication_code" class="form-label">
                                    <i class="fas fa-key"></i>
                                    Authentication Code
                                </label>
                                <input type="text" id="authentication_code" name="authentication_code" class="form-input" 
                                       value="<?php echo htmlspecialchars($authentication_code ?? ''); ?>" 
                                       placeholder="6 character code" maxlength="6">
                            </div>

                            <div class="form-group">
                                <label for="utr_number" class="form-label">
                                    <i class="fas fa-id-card"></i>
                                    UTR Number
                                </label>
                                <input type="text" id="utr_number" name="utr_number" class="form-input" 
                                       value="<?php echo htmlspecialchars($utr_number ?? ''); ?>" 
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
                                        $stmt = $pdo->query("SELECT id, username, user_internal FROM users ORDER BY user_internal ASC, username ASC");
                                        $users = $stmt->fetchAll();
                                        foreach ($users as $user) {
                                            $selected = ($partner_id == $user['id']) ? 'selected' : '';
                                            $display_name = !empty($user['user_internal']) ? $user['user_internal'] : $user['username'];
                                            echo "<option value=\"{$user['id']}\" $selected>" . htmlspecialchars($display_name) . "</option>";
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
                                    <option value="N" <?php echo ($year_end_work === 'N') ? 'selected' : ''; ?>>No</option>
                                    <option value="Y" <?php echo ($year_end_work === 'Y') ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="payroll" class="form-label">
                                    <i class="fas fa-money-bill-wave"></i>
                                    Payroll
                                </label>
                                <select id="payroll" name="payroll" class="form-input">
                                    <option value="N" <?php echo ($payroll === 'N') ? 'selected' : ''; ?>>No</option>
                                    <option value="Y" <?php echo ($payroll === 'Y') ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="directors_sa" class="form-label">
                                    <i class="fas fa-user-crown"></i>
                                    Directors SA
                                </label>
                                <select id="directors_sa" name="directors_sa" class="form-input">
                                    <option value="N" <?php echo ($directors_sa === 'N') ? 'selected' : ''; ?>>No</option>
                                    <option value="Y" <?php echo ($directors_sa === 'Y') ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="vat" class="form-label">
                                    <i class="fas fa-percentage"></i>
                                    VAT
                                </label>
                                <select id="vat" name="vat" class="form-input">
                                    <option value="N" <?php echo ($vat === 'N') ? 'selected' : ''; ?>>No</option>
                                    <option value="Y" <?php echo ($vat === 'Y') ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>

                            <div class="form-group" id="vat_periods_group" style="<?php echo ($vat === 'Y') ? '' : 'display: none;'; ?>">
                                <label for="vat_periods" class="form-label">
                                    <i class="fas fa-calendar-alt"></i>
                                    VAT Periods
                                </label>
                                <select id="vat_periods" name="vat_periods" class="form-input">
                                    <option value="">Select Period</option>
                                    <option value="MJSD" <?php echo ($vat_periods === 'MJSD') ? 'selected' : ''; ?>>MJSD</option>
                                    <option value="JAJO" <?php echo ($vat_periods === 'JAJO') ? 'selected' : ''; ?>>JAJO</option>
                                    <option value="FMAN" <?php echo ($vat_periods === 'FMAN') ? 'selected' : ''; ?>>FMAN</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Add Client
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
