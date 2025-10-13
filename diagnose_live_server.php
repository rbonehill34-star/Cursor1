<?php
// Diagnostic script for live server database issues
require_once 'config/database.php';

echo "=== Database Diagnostic Script ===\n";
echo "Environment: " . (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ? 'Local' : 'Production') . "\n";
echo "Server: " . $_SERVER['HTTP_HOST'] . "\n";
echo "Database: " . (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ? 'cursor1' : 'a1e750tdxgba_cursor1') . "\n\n";

try {
    // Test database connection
    echo "✓ Database connection successful\n\n";
    
    // Check clients table structure
    echo "=== Clients Table Structure ===\n";
    $stmt = $pdo->query("DESCRIBE clients");
    $columns = $stmt->fetchAll();
    
    foreach($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    echo "\n=== Testing INSERT with new fields ===\n";
    
    // Test if we can insert with new fields
    $testData = [
        'reference' => 'TEST001',
        'name' => 'Test Company',
        'contact' => 'Test Contact',
        'email' => 'test@example.com',
        'phone' => '123456789',
        'year_end' => null,
        'company_number' => '12345678',
        'authentication_code' => 'ABC123',
        'utr_number' => '1234567890',
        'partner_id' => null,
        'year_end_work' => 'N',
        'payroll' => 'N',
        'directors_sa' => 'N',
        'vat' => 'N',
        'vat_periods' => null
    ];
    
    $sql = "INSERT INTO clients (reference, name, contact, email, phone, year_end, company_number, authentication_code, utr_number, partner_id, year_end_work, payroll, directors_sa, vat, vat_periods, date_added) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $testData['reference'],
        $testData['name'],
        $testData['contact'],
        $testData['email'],
        $testData['phone'],
        $testData['year_end'],
        $testData['company_number'],
        $testData['authentication_code'],
        $testData['utr_number'],
        $testData['partner_id'],
        $testData['year_end_work'],
        $testData['payroll'],
        $testData['directors_sa'],
        $testData['vat'],
        $testData['vat_periods']
    ]);
    
    if ($result) {
        $testId = $pdo->lastInsertId();
        echo "✓ Test INSERT successful (ID: $testId)\n";
        
        // Test UPDATE with new fields
        echo "\n=== Testing UPDATE with new fields ===\n";
        $updateSql = "UPDATE clients SET reference = ?, name = ?, contact = ?, email = ?, phone = ?, year_end = ?, company_number = ?, authentication_code = ?, utr_number = ?, partner_id = ?, year_end_work = ?, payroll = ?, directors_sa = ?, vat = ?, vat_periods = ? WHERE id = ?";
        
        $stmt = $pdo->prepare($updateSql);
        $updateResult = $stmt->execute([
            'TEST001',
            'Updated Test Company',
            'Updated Contact',
            'updated@example.com',
            '987654321',
            null,
            '87654321',
            'XYZ789',
            '0987654321',
            null,
            'Y',
            'Y',
            'N',
            'Y',
            'MJSD',
            $testId
        ]);
        
        if ($updateResult) {
            echo "✓ Test UPDATE successful\n";
        } else {
            echo "✗ Test UPDATE failed\n";
            print_r($stmt->errorInfo());
        }
        
        // Clean up test record
        $pdo->exec("DELETE FROM clients WHERE id = $testId");
        echo "✓ Test record cleaned up\n";
        
    } else {
        echo "✗ Test INSERT failed\n";
        print_r($stmt->errorInfo());
    }
    
    echo "\n=== Checking for existing clients ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM clients");
    $result = $stmt->fetch();
    echo "Total clients in database: " . $result['count'] . "\n";
    
    if ($result['count'] > 0) {
        echo "\n=== Sample client data ===\n";
        $stmt = $pdo->query("SELECT * FROM clients LIMIT 1");
        $client = $stmt->fetch();
        foreach($client as $key => $value) {
            echo "$key: " . ($value ?? 'NULL') . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
}

echo "\n=== Diagnostic Complete ===\n";
?>
