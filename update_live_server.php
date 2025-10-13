<?php
// Update script specifically for live server
require_once 'config/database.php';

echo "=== Live Server Database Update ===\n";
echo "Environment: " . (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ? 'Local' : 'Production') . "\n\n";

try {
    // Check current table structure
    echo "Current clients table structure:\n";
    $stmt = $pdo->query("DESCRIBE clients");
    $columns = $stmt->fetchAll();
    $existingColumns = array_column($columns, 'Field');
    
    foreach($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    // Define the new columns we need to add
    $newColumns = [
        'company_number' => "ALTER TABLE clients ADD COLUMN company_number VARCHAR(8) AFTER phone",
        'authentication_code' => "ALTER TABLE clients ADD COLUMN authentication_code VARCHAR(6) AFTER company_number",
        'utr_number' => "ALTER TABLE clients ADD COLUMN utr_number VARCHAR(10) AFTER authentication_code",
        'partner_id' => "ALTER TABLE clients ADD COLUMN partner_id INT AFTER utr_number",
        'year_end_work' => "ALTER TABLE clients ADD COLUMN year_end_work ENUM('Y', 'N') DEFAULT 'N' AFTER partner_id",
        'payroll' => "ALTER TABLE clients ADD COLUMN payroll ENUM('Y', 'N') DEFAULT 'N' AFTER year_end_work",
        'directors_sa' => "ALTER TABLE clients ADD COLUMN directors_sa ENUM('Y', 'N') DEFAULT 'N' AFTER payroll",
        'vat' => "ALTER TABLE clients ADD COLUMN vat ENUM('Y', 'N') DEFAULT 'N' AFTER directors_sa",
        'vat_periods' => "ALTER TABLE clients ADD COLUMN vat_periods ENUM('MJSD', 'JAJO', 'FMAN') AFTER vat"
    ];
    
    echo "\n=== Adding missing columns ===\n";
    
    foreach($newColumns as $columnName => $sql) {
        if (!in_array($columnName, $existingColumns)) {
            try {
                $pdo->exec($sql);
                echo "✓ Added column: $columnName\n";
            } catch (PDOException $e) {
                echo "✗ Failed to add column $columnName: " . $e->getMessage() . "\n";
            }
        } else {
            echo "- Column $columnName already exists\n";
        }
    }
    
    // Add foreign key constraint if it doesn't exist
    echo "\n=== Adding foreign key constraint ===\n";
    try {
        // Check if foreign key already exists
        $stmt = $pdo->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clients' AND COLUMN_NAME = 'partner_id' AND CONSTRAINT_NAME != 'PRIMARY'");
        $fkExists = $stmt->fetch();
        
        if (!$fkExists) {
            $pdo->exec("ALTER TABLE clients ADD CONSTRAINT fk_clients_partner FOREIGN KEY (partner_id) REFERENCES users(id) ON DELETE SET NULL");
            echo "✓ Added foreign key constraint for partner_id\n";
        } else {
            echo "- Foreign key constraint already exists\n";
        }
    } catch (PDOException $e) {
        echo "✗ Failed to add foreign key constraint: " . $e->getMessage() . "\n";
    }
    
    // Add indexes
    echo "\n=== Adding indexes ===\n";
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_clients_company_number ON clients(company_number)",
        "CREATE INDEX IF NOT EXISTS idx_clients_utr_number ON clients(utr_number)",
        "CREATE INDEX IF NOT EXISTS idx_clients_partner ON clients(partner_id)",
        "CREATE INDEX IF NOT EXISTS idx_clients_vat ON clients(vat)"
    ];
    
    foreach($indexes as $indexSql) {
        try {
            $pdo->exec($indexSql);
            echo "✓ Created index: " . substr($indexSql, 17, 30) . "...\n";
        } catch (PDOException $e) {
            echo "✗ Failed to create index: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Final table structure ===\n";
    $stmt = $pdo->query("DESCRIBE clients");
    $columns = $stmt->fetchAll();
    
    foreach($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    echo "\n=== Update Complete ===\n";
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}
?>
