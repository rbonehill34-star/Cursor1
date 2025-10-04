<?php
// Environment test page - remove this after deployment
require_once 'config/database.php';

// Get environment info
$server_name = $_SERVER['SERVER_NAME'] ?? 'Not set';
$http_host = $_SERVER['HTTP_HOST'] ?? 'Not set';
$server_software = $_SERVER['SERVER_SOFTWARE'] ?? 'Not set';
$document_root = $_SERVER['DOCUMENT_ROOT'] ?? 'Not set';

// Test database connection
$db_status = 'Unknown';
$db_error = '';

try {
    $test_query = $pdo->query("SELECT 1 as test");
    $result = $test_query->fetch();
    $db_status = $result ? 'Connected Successfully' : 'Query Failed';
} catch (Exception $e) {
    $db_status = 'Connection Failed';
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Environment Test - Cursor1</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .status { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        h1 { color: #333; text-align: center; }
        h2 { color: #666; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .info-item { background: #f8f9fa; padding: 15px; border-radius: 5px; }
        .info-label { font-weight: bold; color: #495057; }
        .info-value { color: #6c757d; word-break: break-all; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Environment Test - Cursor1</h1>
        
        <div class="status <?php echo $db_status === 'Connected Successfully' ? 'success' : 'error'; ?>">
            <strong>Database Status:</strong> <?php echo $db_status; ?>
            <?php if ($db_error): ?>
                <br><strong>Error:</strong> <?php echo htmlspecialchars($db_error); ?>
            <?php endif; ?>
        </div>

        <div class="status info">
            <strong>Environment:</strong> <?php echo $environment; ?>
        </div>

        <h2>Server Information</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Server Name:</div>
                <div class="info-value"><?php echo htmlspecialchars($server_name); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">HTTP Host:</div>
                <div class="info-value"><?php echo htmlspecialchars($http_host); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Server Software:</div>
                <div class="info-value"><?php echo htmlspecialchars($server_software); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Document Root:</div>
                <div class="info-value"><?php echo htmlspecialchars($document_root); ?></div>
            </div>
        </div>

        <h2>Database Configuration</h2>
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Host:</div>
                <div class="info-value"><?php echo htmlspecialchars($host); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Database:</div>
                <div class="info-value"><?php echo htmlspecialchars($dbname); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Username:</div>
                <div class="info-value"><?php echo htmlspecialchars($username); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Password:</div>
                <div class="info-value"><?php echo str_repeat('*', strlen($password)); ?></div>
            </div>
        </div>

        <div class="status info">
            <strong>Note:</strong> This test page should be removed after successful deployment for security reasons.
        </div>

        <div style="text-align: center; margin-top: 30px;">
            <a href="home/" style="background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go to Website</a>
        </div>
    </div>
</body>
</html>
