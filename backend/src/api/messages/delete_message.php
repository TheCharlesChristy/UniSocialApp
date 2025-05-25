<?php
/**
 * Delete Message API Endpoint
 * 
 * Deletes a message (only sender can delete their own messages)
 * Endpoint: DELETE /api/messages/:messageId
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

// Get message ID from URL path or query parameter
$messageId = null;

// Method 1: Check URL path (when nice URLs are working with .htaccess)
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Look for message ID in URL path
for ($i = 0; $i < count($pathParts); $i++) {
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
    // Get message details and verify ownership
    $messageCheckQuery = "
        SELECT 
            m.message_id,
            m.conversation_id,
            m.sender_id,
            m.content,
            m.created_at
        FROM messages m
        WHERE m.message_id = ?
    ";
    
    $messageResult = $Database->query($messageCheckQuery, [$messageId]);
    if ($messageResult === false) {
        throw new Exception('Failed to get message details');
    }
    
    if (empty($messageResult)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Message not found']);
        exit();
    }
    
    $messageData = $messageResult[0];
    
    // Check if current user is the sender of the message
    if ($messageData['sender_id'] != $authUser['user_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'You can only delete your own messages']);
        exit();
    }
    
    // Check if user is still a participant in the conversation
    $participantCheckQuery = "
        SELECT 1 FROM conversation_participants 
        WHERE conversation_id = ? AND user_id = ? AND left_at IS NULL
    ";
    
    $isParticipant = $Database->query($participantCheckQuery, [$messageData['conversation_id'], $authUser['user_id']]);
    if ($isParticipant === false) {
        throw new Exception('Failed to check conversation participation');
    }
    
    if (empty($isParticipant)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied to this conversation']);
        exit();
    }
    
    // Optional: Check if message is too old to delete (e.g., older than 24 hours)
    $messageTime = strtotime($messageData['created_at']);
    $currentTime = time();
    $hoursSinceMessage = ($currentTime - $messageTime) / 3600;
    
    // Uncomment the following lines if you want to restrict deletion of old messages
    /*
    if ($hoursSinceMessage > 24) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Cannot delete messages older than 24 hours']);
        exit();
    }
    */
    
    // Start transaction
    if (!$Database->beginTransaction()) {
        throw new Exception('Failed to start transaction');
    }
    
    // Delete the message
    $deleteQuery = "DELETE FROM messages WHERE message_id = ?";
    $result = $Database->execute($deleteQuery, [$messageId]);
    if ($result === false) {
        throw new Exception('Failed to delete message');
    }
    
    // Check if any rows were affected
    if ($result === 0) {
        $Database->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Message not found or already deleted']);
        exit();
    }
    
    // Update conversation's updated_at timestamp
    $updateConversationQuery = "UPDATE conversations SET updated_at = NOW() WHERE conversation_id = ?";
    $updateResult = $Database->execute($updateConversationQuery, [$messageData['conversation_id']]);
    if ($updateResult === false) {
        throw new Exception('Failed to update conversation timestamp');
    }
    
    // Commit transaction
    if (!$Database->commit()) {
        throw new Exception('Failed to commit transaction');
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Message deleted successfully',
        'deleted_message_id' => (int)$messageId,
        'conversation_id' => (int)$messageData['conversation_id']
    ]);

} catch (Exception $e) {
    $Database->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete message: ' . $e->getMessage()
    ]);
}
