<?php
/**
 * Send Message API Endpoint
 * 
 * Sends a message in a conversation
 * Endpoint: POST /api/conversations/:conversationId/messages
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

// Get conversation ID from URL path or query parameter
$conversationId = null;

// Method 1: Check URL path (when nice URLs are working with .htaccess)
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Look for conversation ID in URL path
for ($i = 0; $i < count($pathParts) - 1; $i++) {
    if ($pathParts[$i] === 'conversations' && isset($pathParts[$i + 1]) && is_numeric($pathParts[$i + 1])) {
        $conversationId = (int)$pathParts[$i + 1];
        break;
    }
}

// Method 2: Check query parameter as fallback
if (!$conversationId && isset($_GET['conversationId'])) {
    $conversationId = (int)$_GET['conversationId'];
}

// Validate conversation ID
if (!$conversationId || $conversationId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valid conversation ID is required']);
    exit();
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

// Validate required fields
if (!isset($input['content']) || empty(trim($input['content']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Message content is required']);
    exit();
}

$content = trim($input['content']);

// Validate message length
if (strlen($content) > 5000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Message content cannot exceed 5000 characters']);
    exit();
}

try {
    // Check if user is a participant in this conversation
    $participantCheckQuery = "
        SELECT 1 FROM conversation_participants 
        WHERE conversation_id = ? AND user_id = ? AND left_at IS NULL
    ";
    
    $isParticipant = $Database->query($participantCheckQuery, [$conversationId, $authUser['user_id']]);
    if ($isParticipant === false) {
        throw new Exception('Failed to check conversation participation');
    }
    
    if (empty($isParticipant)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied to this conversation']);
        exit();
    }
    
    // Verify conversation exists
    $conversationCheckQuery = "SELECT conversation_id FROM conversations WHERE conversation_id = ?";
    $conversationExists = $Database->query($conversationCheckQuery, [$conversationId]);
    if ($conversationExists === false) {
        throw new Exception('Failed to check conversation existence');
    }
    
    if (empty($conversationExists)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Conversation not found']);
        exit();
    }
    
    // Start transaction
    if (!$Database->beginTransaction()) {
        throw new Exception('Failed to start transaction');
    }
    
    // Insert the message
    $insertMessageQuery = "
        INSERT INTO messages (conversation_id, sender_id, content, created_at, is_read, read_at)
        VALUES (?, ?, ?, NOW(), FALSE, NULL)
    ";
    
    $result = $Database->execute($insertMessageQuery, [$conversationId, $authUser['user_id'], $content]);
    if ($result === false) {
        throw new Exception('Failed to insert message');
    }
    
    // Get the message ID
    $messageIdResult = $Database->query("SELECT LAST_INSERT_ID() as id");
    if ($messageIdResult === false) {
        throw new Exception('Failed to get message ID');
    }
    $messageId = $messageIdResult[0]['id'];
    
    // Update conversation's updated_at timestamp
    $updateConversationQuery = "UPDATE conversations SET updated_at = NOW() WHERE conversation_id = ?";
    $updateResult = $Database->execute($updateConversationQuery, [$conversationId]);
    if ($updateResult === false) {
        throw new Exception('Failed to update conversation timestamp');
    }
    
    // Get the sent message with timestamp for response
    $getMessageQuery = "
        SELECT 
            m.message_id,
            m.conversation_id,
            m.sender_id,
            m.content,
            m.created_at,
            m.is_read,
            m.read_at
        FROM messages m
        WHERE m.message_id = ?
    ";
    
    $sentMessage = $Database->query($getMessageQuery, [$messageId]);
    if ($sentMessage === false) {
        throw new Exception('Failed to retrieve sent message');
    }
    
    // Commit transaction
    if (!$Database->commit()) {
        throw new Exception('Failed to commit transaction');
    }
    
    $messageData = $sentMessage[0];
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Message sent successfully',
        'message_id' => (int)$messageData['message_id'],
        'sent_at' => $messageData['created_at'],
        'conversation_id' => (int)$conversationId,
        'content' => $messageData['content']
    ]);

} catch (Exception $e) {
    $Database->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send message: ' . $e->getMessage()
    ]);
}
