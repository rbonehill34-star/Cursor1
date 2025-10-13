<?php
// Temporary script to update live database
// Delete this file after running

// Force production environment
$host = 'localhost';
$dbname = 'a1e750tdxgba_cursor1';
$username = 'a1e750tdxgba_15Crossways';
$password = 'Crossways15!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to live database successfully!\n";
    
    // Check if type column already exists
    $stmt = $pdo->query("SHOW COLUMNS FROM clients LIKE 'type'");
    if ($stmt->rowCount() > 0) {
        echo "Type column already exists. No changes needed.\n";
    } else {
        // Add type column to clients table between name and contact
        $sql = "ALTER TABLE clients ADD COLUMN type ENUM('Company', 'Individual', 'Partnership') NOT NULL DEFAULT 'Company' AFTER name";
        $pdo->exec($sql);
        echo "Successfully added 'type' column to live clients table.\n";
    }
    
    // Verify the change
    $stmt = $pdo->query('DESCRIBE clients');
    echo "\nUpdated clients table structure:\n";
    echo "Field - Type - Null - Key - Default - Extra\n";
    echo "==========================================\n";
    while($row = $stmt->fetch()) {
        echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . $row['Null'] . ' - ' . $row['Key'] . ' - ' . $row['Default'] . ' - ' . $row['Extra'] . "\n";
    }
    
} catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}

echo "\nScript completed. Please delete this file for security.\n";
?>
