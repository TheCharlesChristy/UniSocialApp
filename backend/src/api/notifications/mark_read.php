<?php
/**
 * Mark Notification as Read API Endpoint
 * 
 * Marks a specific notification as read
 * Endpoint: PUT /api/notifications/:notificationId/read
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Only allow PUT requests
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
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

// Get notification ID from URL path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', $path);

// Find the notification ID in the URL
$notificationId = null;
for ($i = 0; $i < count($pathParts); $i++) {
    if ($pathParts[$i] === 'notifications' && isset($pathParts[$i + 1]) && is_numeric($pathParts[$i + 1])) {
        $notificationId = (int)$pathParts[$i + 1];
        break;
    }
}

// Validate notification ID
if (!$notificationId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
    exit();
}

$userId = $authUser['user_id'];

try {
    $Database->beginTransaction();
    
    // Check if notification exists and belongs to the current user
    $notification = $Database->query(
        "SELECT notification_id, is_read FROM notifications 
         WHERE notification_id = ? AND recipient_id = ?",
        [$notificationId, $userId]
    );
    
    if (!$notification || empty($notification)) {
        $Database->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Notification not found']);
        exit();
    }
    
    $notificationData = $notification[0];
    
    // Check if already read
    if ($notificationData['is_read']) {
        $Database->rollBack();
        echo json_encode([
            'success' => true,
            'message' => 'Notification already marked as read'
        ]);
        exit();
    }
    
    // Mark notification as read
    $result = $Database->execute(
        "UPDATE notifications 
         SET is_read = TRUE, read_at = NOW() 
         WHERE notification_id = ? AND recipient_id = ?",
        [$notificationId, $userId]
    );
    
    if ($result === false) {
        $Database->rollBack();
        throw new Exception('Failed to update notification');
    }
    
    $Database->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Notification marked as read'
    ]);

} catch (Exception $e) {
    $Database->rollBack();
    error_log("Mark notification read error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to mark notification as read']);
}
?>
