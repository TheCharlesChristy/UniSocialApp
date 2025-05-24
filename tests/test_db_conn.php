<?php
/**
 * Database Connection Test Page
 * 
 * This page tests the database connection and displays the connection status
 */

// Include the database connection handler
require_once __DIR__ . '/../backend/src/db_handler/connection.php';

// Get the connection status
$isConnected = $Database->isConnected();
$errors = $Database->getErrors();

// Set page title
$pageTitle = "Database Connection Test";

// Start HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            padding: 0;
            color: #333;
        }
        h1 {
            color: #0066cc;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        .status {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow: auto;
        }
        .test-section {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        .back-to-tests-btn {
            position: fixed;
            top: 10px;
            left: 10px;
            background-color: #007bff;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            z-index: 1000;
            transition: background-color 0.3s;
        }
        .back-to-tests-btn:hover {
            background-color: #0056b3;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <a href="http://localhost/webdev/tests" class="back-to-tests-btn">← Back to Tests</a>
    <h1><?php echo $pageTitle; ?></h1>
    
    <div class="test-section">
        <h2>Connection Status</h2>
        <?php if ($isConnected): ?>
            <div class="status success">
                <strong>Success:</strong> Database connection established!
            </div>
        <?php else: ?>
            <div class="status error">
                <strong>Error:</strong> Failed to connect to the database.
            </div>
            <?php if (!empty($errors)): ?>
                <h3>Error Details:</h3>
                <pre><?php print_r($errors); ?></pre>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <?php if ($isConnected): ?>
    <div class="test-section">
        <h2>Database Details</h2>
        <?php
            // Get basic database info
            $dbInfo = $Database->query("SELECT DATABASE() AS current_db, VERSION() AS mysql_version");
            if ($dbInfo):
        ?>
            <table border="1" cellpadding="5" cellspacing="0">
                <tr>
                    <th>Current Database</th>
                    <td><?php echo $dbInfo[0]['current_db']; ?></td>
                </tr>
                <tr>
                    <th>MySQL Version</th>
                    <td><?php echo $dbInfo[0]['mysql_version']; ?></td>
                </tr>
            </table>
        <?php else: ?>
            <div class="status error">Failed to retrieve database information.</div>
        <?php endif; ?>
    </div>
    
    <div class="test-section">
        <h2>Database Tables</h2>
        <?php
            // List all tables
            $tables = $Database->query("SHOW TABLES");
            if ($tables && count($tables) > 0):
        ?>
            <ul>
                <?php foreach ($tables as $table): ?>
                    <li><?php echo reset($table); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="status error">No tables found in database or failed to retrieve table list.</div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="test-section">
        <h2>PHP Environment Info</h2>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>PHP Version</th>
                <td><?php echo phpversion(); ?></td>
            </tr>
            <tr>
                <th>PDO Drivers</th>
                <td><?php echo implode(', ', PDO::getAvailableDrivers()); ?></td>
            </tr>
            <tr>
                <th>Server Software</th>
                <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
            </tr>
        </table>
    </div>
</body>
</html>
