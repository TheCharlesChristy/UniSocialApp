<?php
/**
 * Mark All Notifications as Read API Endpoint
 * 
 * Marks all notifications as read for the current user
 * Endpoint: PUT /api/notifications/read-all
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Allow GET, POST, and PUT requests
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST', 'PUT'])) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Require authentication
$requireAuth = true;

// Get database connection
$Database = require_once dirname(dirname(dirname(__FILE__))) . '/db_handler/connection.php';

// Include authentication middleware
require_once dirname(dirname(__FILE__)) . '/auth/auth_middleware.php';

// Check database connection
if (!$Database->isConnected()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit();
}

$userId = $authUser['user_id'];

try {
    $Database->beginTransaction();
    
    // Get count of unread notifications before marking them read
    $unreadResult = $Database->query(
        "SELECT COUNT(*) as count FROM notifications 
         WHERE recipient_id = ? AND is_read = FALSE",
        [$userId]
    );
    
    if ($unreadResult === false) {
        $Database->rollBack();
        throw new Exception('Failed to get unread count');
    }
    
    $unreadCount = $unreadResult[0]['count'];
    
    // Mark all unread notifications as read
    $result = $Database->execute(
        "UPDATE notifications 
         SET is_read = TRUE, read_at = NOW() 
         WHERE recipient_id = ? AND is_read = FALSE",
        [$userId]
    );
    
    if ($result === false) {
        $Database->rollBack();
        throw new Exception('Failed to update notifications');
    }
    
    $Database->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'count' => (int)$unreadCount,
        'message' => "Marked {$unreadCount} notifications as read"
    ]);

} catch (Exception $e) {
    $Database->rollBack();
    error_log("Mark all notifications read error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to mark notifications as read']);
}
?>
