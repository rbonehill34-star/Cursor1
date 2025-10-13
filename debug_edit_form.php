<?php
// Debug script to test the edit form processing
require_once 'config/database.php';

echo "=== Debug Edit Form Processing ===\n";

// Simulate the POST data that would come from the form
$_POST = [
    'reference' => 'TEST001',
    'name' => 'Test Company Ltd',
    'contact' => 'John Smith',
    'email' => 'john@test.com',
    'phone' => '1234567890',
    'year_end' => '2024-12-31',
    'company_number' => '12345678',
    'authentication_code' => 'ABC123',
    'utr_number' => '1234567890',
    'partner_id' => '',
    'year_end_work' => 'Y',
    'payroll' => 'N',
    'directors_sa' => 'Y',
    'vat' => 'Y',
    'vat_periods' => 'MJSD'
];

echo "Simulated POST data:\n";
foreach($_POST as $key => $value) {
    echo "- $key: " . ($value ?: 'EMPTY') . "\n";
}

echo "\n=== Processing Form Data ===\n";

// Process the data exactly like the edit form does
$reference = trim($_POST['reference'] ?? '');
$name = trim($_POST['name'] ?? '');
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

echo "Processed data:\n";
echo "- reference: '$reference'\n";
echo "- name: '$name'\n";
echo "- contact: '$contact'\n";
echo "- email: '$email'\n";
echo "- phone: '$phone'\n";
echo "- year_end: '$year_end'\n";
echo "- company_number: '$company_number'\n";
echo "- authentication_code: '$authentication_code'\n";
echo "- utr_number: '$utr_number'\n";
echo "- partner_id: " . ($partner_id ?: 'NULL') . "\n";
echo "- year_end_work: '$year_end_work'\n";
echo "- payroll: '$payroll'\n";
echo "- directors_sa: '$directors_sa'\n";
echo "- vat: '$vat'\n";
echo "- vat_periods: " . ($vat_periods ?: 'NULL') . "\n";

echo "\n=== Validation Tests ===\n";

// Test validation exactly like the form does
$validationErrors = [];

if (empty($reference) || empty($name)) {
    $validationErrors[] = 'Reference and name are required fields.';
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $validationErrors[] = 'Please enter a valid email address.';
}

if (!empty($company_number) && (strlen($company_number) > 8 || !ctype_digit($company_number))) {
    $validationErrors[] = 'Company Number must be 8 digits or less.';
}

if (!empty($authentication_code) && strlen($authentication_code) > 6) {
    $validationErrors[] = 'Authentication Code must be 6 characters or less.';
}

if (!empty($utr_number) && (strlen($utr_number) > 10 || !ctype_digit($utr_number))) {
    $validationErrors[] = 'UTR Number must be 10 digits or less.';
}

if ($vat === 'Y' && empty($vat_periods)) {
    $validationErrors[] = 'VAT Periods is required when VAT is set to Yes.';
}

if (empty($validationErrors)) {
    echo "✅ All validation checks passed!\n";
} else {
    echo "❌ Validation errors found:\n";
    foreach($validationErrors as $error) {
        echo "- $error\n";
    }
}

echo "\n=== Database Update Test ===\n";

try {
    // Get a test client ID
    $stmt = $pdo->query("SELECT id FROM clients LIMIT 1");
    $client = $stmt->fetch();
    
    if ($client) {
        $client_id = $client['id'];
        echo "Testing with client ID: $client_id\n";
        
        // Prepare the exact UPDATE statement from the edit form
        $updateSql = "UPDATE clients SET reference = ?, name = ?, contact = ?, email = ?, phone = ?, year_end = ?, company_number = ?, authentication_code = ?, utr_number = ?, partner_id = ?, year_end_work = ?, payroll = ?, directors_sa = ?, vat = ?, vat_periods = ? WHERE id = ?";
        
        echo "SQL: $updateSql\n\n";
        
        $stmt = $pdo->prepare($updateSql);
        
        // Execute with the processed data
        $executeData = [
            $reference,
            $name,
            $contact,
            $email,
            $phone,
            $year_end ?: null,
            $company_number ?: null,
            $authentication_code ?: null,
            $utr_number ?: null,
            $partner_id ?: null,
            $year_end_work,
            $payroll,
            $directors_sa,
            $vat,
            $vat_periods,
            $client_id
        ];
        
        echo "Execute data:\n";
        foreach($executeData as $i => $data) {
            echo "  [$i]: " . ($data ?: 'NULL') . "\n";
        }
        
        $result = $stmt->execute($executeData);
        
        if ($result) {
            echo "\n✅ UPDATE executed successfully!\n";
            echo "Rows affected: " . $stmt->rowCount() . "\n";
        } else {
            echo "\n❌ UPDATE failed!\n";
            echo "Error info: ";
            print_r($stmt->errorInfo());
        }
        
    } else {
        echo "No clients found to test with.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
}

echo "\n=== Debug Complete ===\n";
?>
