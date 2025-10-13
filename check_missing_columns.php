<?php
// Simple script to check which columns are missing from the live server
require_once 'config/database.php';

echo "=== Checking Missing Columns ===\n";

try {
    // Get current table structure
    $stmt = $pdo->query("DESCRIBE clients");
    $columns = $stmt->fetchAll();
    $existingColumns = array_column($columns, 'Field');
    
    echo "Current columns in clients table:\n";
    foreach($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    // Define all required columns
    $requiredColumns = [
        'id',
        'reference', 
        'name',
        'contact',
        'email',
        'phone',
        'company_number',
        'authentication_code',
        'utr_number',
        'partner_id',
        'year_end_work',
        'payroll',
        'directors_sa',
        'vat',
        'vat_periods',
        'year_end',
        'date_added',
        'created_at',
        'updated_at'
    ];
    
    echo "\n=== Missing Columns Check ===\n";
    $missingColumns = [];
    
    foreach($requiredColumns as $requiredColumn) {
        if (!in_array($requiredColumn, $existingColumns)) {
            $missingColumns[] = $requiredColumn;
            echo "✗ MISSING: $requiredColumn\n";
        } else {
            echo "✓ EXISTS: $requiredColumn\n";
        }
    }
    
    if (empty($missingColumns)) {
        echo "\n✅ All required columns exist!\n";
        echo "The issue might be elsewhere. Let's test the UPDATE query...\n\n";
        
        // Test the exact UPDATE query from the edit form
        echo "=== Testing UPDATE Query ===\n";
        
        // Get a sample client ID
        $stmt = $pdo->query("SELECT id FROM clients LIMIT 1");
        $client = $stmt->fetch();
        
        if ($client) {
            $clientId = $client['id'];
            echo "Testing with client ID: $clientId\n";
            
            // Test the exact UPDATE query
            $updateSql = "UPDATE clients SET reference = ?, name = ?, contact = ?, email = ?, phone = ?, year_end = ?, company_number = ?, authentication_code = ?, utr_number = ?, partner_id = ?, year_end_work = ?, payroll = ?, directors_sa = ?, vat = ?, vat_periods = ? WHERE id = ?";
            
            $stmt = $pdo->prepare($updateSql);
            $result = $stmt->execute([
                'TEST_REF',           // reference
                'Test Company',       // name
                'Test Contact',       // contact
                'test@example.com',   // email
                '123456789',          // phone
                null,                 // year_end
                '12345678',           // company_number
                'ABC123',             // authentication_code
                '1234567890',         // utr_number
                null,                 // partner_id
                'N',                  // year_end_work
                'N',                  // payroll
                'N',                  // directors_sa
                'N',                  // vat
                null,                 // vat_periods
                $clientId             // id
            ]);
            
            if ($result) {
                echo "✅ UPDATE query works perfectly!\n";
                echo "The issue is likely in the form validation or data processing.\n";
            } else {
                echo "❌ UPDATE query failed!\n";
                echo "Error info: ";
                print_r($stmt->errorInfo());
            }
        } else {
            echo "No clients found to test with.\n";
        }
    } else {
        echo "\n❌ Missing columns found. Here are the SQL commands to add them:\n\n";
        
        $addCommands = [
            'authentication_code' => "ALTER TABLE clients ADD COLUMN authentication_code VARCHAR(6) AFTER company_number;",
            'utr_number' => "ALTER TABLE clients ADD COLUMN utr_number VARCHAR(10) AFTER authentication_code;",
            'partner_id' => "ALTER TABLE clients ADD COLUMN partner_id INT AFTER utr_number;",
            'year_end_work' => "ALTER TABLE clients ADD COLUMN year_end_work ENUM('Y', 'N') DEFAULT 'N' AFTER partner_id;",
            'payroll' => "ALTER TABLE clients ADD COLUMN payroll ENUM('Y', 'N') DEFAULT 'N' AFTER year_end_work;",
            'directors_sa' => "ALTER TABLE clients ADD COLUMN directors_sa ENUM('Y', 'N') DEFAULT 'N' AFTER payroll;",
            'vat' => "ALTER TABLE clients ADD COLUMN vat ENUM('Y', 'N') DEFAULT 'N' AFTER directors_sa;",
            'vat_periods' => "ALTER TABLE clients ADD COLUMN vat_periods ENUM('MJSD', 'JAJO', 'FMAN') AFTER vat;"
        ];
        
        foreach($missingColumns as $missingColumn) {
            if (isset($addCommands[$missingColumn])) {
                echo $addCommands[$missingColumn] . "\n";
            }
        }
        
        echo "\nRun these commands one by one in phpMyAdmin.\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n=== Check Complete ===\n";
?>
