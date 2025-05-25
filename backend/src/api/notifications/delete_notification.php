<?php
/**
 * Delete Notification API Endpoint
 * 
 * Deletes a specific notification
 * Endpoint: DELETE /api/notifications/:notificationId
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Only allow DELETE requests
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
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

// Get notification ID from multiple sources
$notificationId = null;

// 1. Try to get from URL query parameter
if (isset($_GET['notificationId']) && is_numeric($_GET['notificationId'])) {
    $notificationId = (int)$_GET['notificationId'];
}

// 2. Try to get from request body
if (!$notificationId) {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input && isset($input['notificationId']) && is_numeric($input['notificationId'])) {
        $notificationId = (int)$input['notificationId'];
    }
}

// 3. Try to get from URL path (RESTful style)
if (!$notificationId) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', $path);
    
    // Find the notification ID in the URL
    for ($i = 0; $i < count($pathParts); $i++) {
        if ($pathParts[$i] === 'notifications' && isset($pathParts[$i + 1]) && is_numeric($pathParts[$i + 1])) {
            $notificationId = (int)$pathParts[$i + 1];
            break;
        }
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
        "SELECT notification_id, recipient_id, sender_id FROM notifications 
         WHERE notification_id = ? AND (recipient_id = ? OR sender_id = ?)",
        [$notificationId, $userId, $userId]
    );
    
    if (!$notification || empty($notification)) {
        $Database->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Notification not found or access denied']);
        exit();
    }
    
    $notificationData = $notification[0];
    
    // Users can delete notifications they received or sent
    if ($notificationData['recipient_id'] != $userId && $notificationData['sender_id'] != $userId) {
        $Database->rollBack();
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit();
    }
    
    // Delete the notification
    $result = $Database->execute(
        "DELETE FROM notifications WHERE notification_id = ?",
        [$notificationId]
    );
    
    if ($result === false) {
        $Database->rollBack();
        throw new Exception('Failed to delete notification');
    }
    
    $Database->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Notification deleted successfully'
    ]);

} catch (Exception $e) {
    $Database->rollBack();
    error_log("Delete notification error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete notification']);
}
?>
