<?php
// Email template debugging script for live server
// This will help diagnose why templates aren't being picked up

require_once 'config/database.php';

echo "<h2>Email Templates Debug - Live Server</h2>";

try {
    // Check database connection
    echo "<h3>1. Database Connection</h3>";
    echo "✓ Connected to database successfully<br>";
    
    // Check if email_templates table exists
    echo "<h3>2. Table Structure</h3>";
    $stmt = $pdo->query("DESCRIBE email_templates");
    $columns = $stmt->fetchAll();
    
    echo "Email templates table structure:<br>";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})<br>";
    }
    
    // Check templates in database
    echo "<h3>3. Templates in Database</h3>";
    $stmt = $pdo->query("SELECT template_name, subject, body FROM email_templates ORDER BY template_name");
    $templates = $stmt->fetchAll();
    
    if (empty($templates)) {
        echo "❌ No templates found in database!<br>";
    } else {
        echo "✓ Found " . count($templates) . " templates:<br>";
        foreach ($templates as $template) {
            echo "<strong>Template: '{$template['template_name']}'</strong><br>";
            echo "Subject: " . htmlspecialchars($template['subject']) . "<br>";
            echo "Body: " . htmlspecialchars(substr($template['body'], 0, 100)) . "...<br><br>";
        }
    }
    
    // Test specific template lookups
    echo "<h3>4. Template Lookup Tests</h3>";
    
    $test_templates = ['Year End', 'VAT Returns', 'Other default'];
    foreach ($test_templates as $template_name) {
        $stmt = $pdo->prepare("SELECT subject, body FROM email_templates WHERE template_name = ?");
        $stmt->execute([$template_name]);
        $template = $stmt->fetch();
        
        if ($template) {
            echo "✓ '$template_name' - FOUND<br>";
            echo "&nbsp;&nbsp;Subject: " . htmlspecialchars($template['subject']) . "<br>";
        } else {
            echo "❌ '$template_name' - NOT FOUND<br>";
        }
    }
    
    // Check tasks table for mapping
    echo "<h3>5. Task ID Mapping</h3>";
    $stmt = $pdo->query("SELECT id, task_name FROM tasks ORDER BY id");
    $tasks = $stmt->fetchAll();
    
    echo "Current tasks:<br>";
    foreach ($tasks as $task) {
        echo "ID {$task['id']}: {$task['task_name']}<br>";
    }
    
    // Test the mapping logic
    echo "<h3>6. Template Mapping Logic Test</h3>";
    $template_mapping = [
        1 => 'Year End',      // Year End
        2 => 'VAT Returns',   // VAT Returns
    ];
    
    foreach ($template_mapping as $task_id => $template_name) {
        echo "Task ID $task_id → Template: '$template_name'<br>";
        
        // Test the actual lookup
        $stmt = $pdo->prepare("SELECT subject FROM email_templates WHERE template_name = ?");
        $stmt->execute([$template_name]);
        $result = $stmt->fetch();
        
        if ($result) {
            echo "&nbsp;&nbsp;✓ Template found: " . htmlspecialchars($result['subject']) . "<br>";
        } else {
            echo "&nbsp;&nbsp;❌ Template NOT found<br>";
        }
    }
    
    echo "<h3>7. Environment Check</h3>";
    echo "Server: " . $_SERVER['SERVER_NAME'] . "<br>";
    echo "Database Host: " . (defined('DB_HOST') ? DB_HOST : 'Not defined') . "<br>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Database Error</h3>";
    echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "This suggests there's an issue with the database connection or table structure.<br>";
}

echo "<hr>";
echo "<p><em>Run this script on your live server and check the results. Look for any ❌ marks that indicate issues.</em></p>";
?>
