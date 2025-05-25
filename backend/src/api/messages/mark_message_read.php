<?php
/**
 * Mark Message as Read API Endpoint
 * 
 * Marks a message as read by the current user
 * Endpoint: PUT /api/messages/:messageId/read
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

// Get message ID from URL path or query parameter
$messageId = null;

// Method 1: Check URL path (when nice URLs are working with .htaccess)
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Look for message ID in URL path
for ($i = 0; $i < count($pathParts) - 1; $i++) {
    if ($pathParts[$i] === 'messages' && isset($pathParts[$i + 1]) && is_numeric($pathParts[$i + 1])) {
        $messageId = (int)$pathParts[$i + 1];
        break;
    }
}

// Method 2: Check query parameter as fallback
if (!$messageId && isset($_GET['messageId'])) {
    $messageId = (int)$_GET['messageId'];
}

// Validate message ID
if (!$messageId || $messageId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valid message ID is required']);
    exit();
}

try {
    // Get message details and check if user has access to it
    $messageCheckQuery = "
        SELECT 
            m.message_id,
            m.conversation_id,
            m.sender_id,
            m.is_read,
            m.read_at
        FROM messages m
        INNER JOIN conversation_participants cp ON m.conversation_id = cp.conversation_id
        WHERE m.message_id = ? AND cp.user_id = ? AND cp.left_at IS NULL
    ";
    
    $messageResult = $Database->query($messageCheckQuery, [$messageId, $authUser['user_id']]);
    if ($messageResult === false) {
        throw new Exception('Failed to check message access');
    }
    
    if (empty($messageResult)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Message not found or access denied']);
        exit();
    }
    
    $messageData = $messageResult[0];
    
    // Check if user is trying to mark their own message as read (not allowed)
    if ($messageData['sender_id'] == $authUser['user_id']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cannot mark your own message as read']);
        exit();
    }
    
    // Check if message is already marked as read
    if ($messageData['is_read']) {
        echo json_encode([
            'success' => true,
            'message' => 'Message already marked as read',
            'read_at' => $messageData['read_at']
        ]);
        exit();
    }
    
    // Mark message as read
    $updateQuery = "
        UPDATE messages 
        SET is_read = TRUE, read_at = NOW() 
        WHERE message_id = ?
    ";
    
    $result = $Database->execute($updateQuery, [$messageId]);
    if ($result === false) {
        throw new Exception('Failed to mark message as read');
    }
    
    // Get the updated read timestamp
    $getTimestampQuery = "SELECT read_at FROM messages WHERE message_id = ?";
    $timestampResult = $Database->query($getTimestampQuery, [$messageId]);
    if ($timestampResult === false) {
        throw new Exception('Failed to get read timestamp');
    }
    
    $readAt = $timestampResult[0]['read_at'];
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Message marked as read successfully',
        'message_id' => (int)$messageId,
        'read_at' => $readAt
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to mark message as read: ' . $e->getMessage()
    ]);
}
