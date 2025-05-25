<?php
/**
 * Reports API Basic Test Script
 * 
 * Simple test to verify database connectivity and reports table structure
 */

// Include database connection
$Database = require_once dirname(dirname(__FILE__)) . '/backend/src/db_handler/connection.php';

echo "<!DOCTYPE html>\n<html>\n<head>\n<title>Reports API Basic Test</title>\n<style>
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
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f5f5f5;
}
.container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>\n</head>\n<body>\n";
echo "<a href='http://localhost/webdev/tests' class='back-to-tests-btn'>← Back to Tests</a>\n";
echo "<h1>Reports API Basic Test</h1>\n";

// Check database connection
if ($Database->isConnected()) {
    echo "<p style='color: green;'>✓ Database connection successful</p>\n";
    
    // Test basic queries
    try {
        // Check if reports table exists and has proper structure
        $tableStructure = $Database->query("DESCRIBE reports");
        if ($tableStructure !== false) {
            echo "<p style='color: green;'>✓ Reports table accessible</p>\n";
            
            echo "<div class='container'>";
            echo "<h3>Reports Table Structure:</h3>";
            echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse;'>";
            echo "<tr style='background-color: #f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            
            foreach ($tableStructure as $column) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Default']) . "</td>";
                echo "<td>" . htmlspecialchars($column['Extra']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "</div>";
        }
        
        // Check if reports table has data
        $reportsCount = $Database->query("SELECT COUNT(*) as count FROM reports");
        if ($reportsCount !== false) {
            echo "<p style='color: green;'>✓ Reports table accessible - " . $reportsCount[0]['count'] . " reports found</p>\n";
        }
        
        // Check if users table exists (needed for foreign keys)
        $usersCount = $Database->query("SELECT COUNT(*) as count FROM users WHERE account_status = 'active'");
        if ($usersCount !== false) {
            echo "<p style='color: green;'>✓ Users table accessible - " . $usersCount[0]['count'] . " active users found</p>\n";
        }
        
        // Check if posts table exists (needed for post reports)
        $postsCount = $Database->query("SELECT COUNT(*) as count FROM posts");
        if ($postsCount !== false) {
            echo "<p style='color: green;'>✓ Posts table accessible - " . $postsCount[0]['count'] . " posts found</p>\n";
        }
        
        // Check if comments table exists (needed for comment reports)
        $commentsCount = $Database->query("SELECT COUNT(*) as count FROM comments");
        if ($commentsCount !== false) {
            echo "<p style='color: green;'>✓ Comments table accessible - " . $commentsCount[0]['count'] . " comments found</p>\n";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Database query error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    }
    
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>\n";
    $errors = $Database->getErrors();
    if (!empty($errors)) {
        echo "<p style='color: red;'>Errors: " . htmlspecialchars(implode(', ', $errors)) . "</p>\n";
    }
}

// List available endpoints
echo "<div class='container'>";
echo "<h2>Available Reports Endpoints</h2>\n";
echo "<h3>Reports API Endpoints:</h3>\n";
echo "<ul>\n";
echo "<li><strong>POST /api/reports/create_report.php</strong> - Create a new report</li>\n";
echo "<li><strong>GET /api/users/get_user_reports.php</strong> - Get reports filed by current user</li>\n";
echo "</ul>\n";

echo "<h3>Endpoint Details:</h3>\n";
echo "<h4>Create Report (POST /api/reports)</h4>\n";
echo "<p><strong>Required Parameters:</strong></p>\n";
echo "<ul>\n";
echo "<li>reported_id (integer) - ID of the user being reported</li>\n";
echo "<li>content_type (string) - Type of content: 'user', 'post', 'comment'</li>\n";
echo "<li>content_id (integer) - ID of the specific content</li>\n";
echo "<li>reason (string) - Reason for the report (max 100 chars)</li>\n";
echo "</ul>\n";
echo "<p><strong>Optional Parameters:</strong></p>\n";
echo "<ul>\n";
echo "<li>description (string) - Additional details about the report</li>\n";
echo "</ul>\n";

echo "<h4>Get User Reports (GET /api/users/:userId/reports)</h4>\n";
echo "<p><strong>URL Parameters:</strong></p>\n";
echo "<ul>\n";
echo "<li>userId (integer) - ID of the user (must be authenticated user)</li>\n";
echo "</ul>\n";
echo "<p><strong>Query Parameters:</strong></p>\n";
echo "<ul>\n";
echo "<li>page (integer, optional) - Page number (default: 1)</li>\n";
echo "<li>limit (integer, optional) - Results per page (default: 20, max: 50)</li>\n";
echo "</ul>\n";

echo "<h3>Testing:</h3>\n";
echo "<p>Use the <a href='test_reports_endpoints.php'>Reports API Test Page</a> to manually test these endpoints.</p>\n";
echo "</div>";

echo "\n</body>\n</html>";
?>
