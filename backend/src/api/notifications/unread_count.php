<?php
/**
 * Get Unread Notification Count API Endpoint
 * 
 * Gets count of unread notifications for the current user
 * Endpoint: GET /api/notifications/unread-count
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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
    // Get count of unread notifications
    $result = $Database->query(
        "SELECT COUNT(*) as count FROM notifications 
         WHERE recipient_id = ? AND is_read = FALSE",
        [$userId]
    );
    
    if ($result === false) {
        throw new Exception('Failed to get unread count');
    }
    
    $unreadCount = $result[0]['count'];
    
    // Return success response
    echo json_encode([
        'success' => true,
        'count' => (int)$unreadCount
    ]);

} catch (Exception $e) {
    error_log("Get unread count error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to get unread count']);
}
?>
