<?php
/**
 * Get Conversation Messages API Endpoint
 * 
 * Gets messages in a conversation with pagination
 * Endpoint: GET /api/conversations/:conversationId/messages
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

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
$beforeMessageId = isset($_GET['before_message_id']) ? (int)$_GET['before_message_id'] : null;

// Validate pagination parameters
if ($page < 1) {
    $page = 1;
}
if ($limit < 1 || $limit > 100) {
    $limit = 50;
}

$offset = ($page - 1) * $limit;

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
    
    // Build the messages query
    $whereClause = "m.conversation_id = ?";
    $queryParams = [$conversationId];
    
    // Add before_message_id filter if provided
    if ($beforeMessageId) {
        $whereClause .= " AND m.message_id < ?";
        $queryParams[] = $beforeMessageId;
    }
    
    // Get total count of messages
    $countQuery = "
        SELECT COUNT(*) as total
        FROM messages m
        WHERE $whereClause
    ";
    
    $countResult = $Database->query($countQuery, $queryParams);
    if ($countResult === false) {
        throw new Exception('Failed to get message count');
    }
    
    $totalMessages = $countResult[0]['total'];
    $totalPages = ceil($totalMessages / $limit);
    
    // Get messages with sender information
    $messagesQuery = "
        SELECT 
            m.message_id,
            m.conversation_id,
            m.sender_id,
            m.content,
            m.created_at,
            m.is_read,
            m.read_at,
            u.username,
            u.first_name,
            u.last_name,
            u.profile_picture
        FROM messages m
        INNER JOIN users u ON m.sender_id = u.user_id
        WHERE $whereClause
        ORDER BY m.created_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $queryParams[] = $limit;
    $queryParams[] = $offset;
    
    $messages = $Database->query($messagesQuery, $queryParams);
    if ($messages === false) {
        throw new Exception('Failed to get messages');
    }
    
    // Format messages and add additional information
    $formattedMessages = [];
    foreach ($messages as $message) {
        $formattedMessage = [
            'message_id' => (int)$message['message_id'],
            'conversation_id' => (int)$message['conversation_id'],
            'sender_id' => (int)$message['sender_id'],
            'content' => $message['content'],
            'created_at' => $message['created_at'],
            'is_read' => (bool)$message['is_read'],
            'read_at' => $message['read_at'],
            'sender' => [
                'user_id' => (int)$message['sender_id'],
                'username' => $message['username'],
                'first_name' => $message['first_name'],
                'last_name' => $message['last_name'],
                'profile_picture' => $message['profile_picture']
            ],
            'is_own_message' => $message['sender_id'] == $authUser['user_id']
        ];
        
        $formattedMessages[] = $formattedMessage;
    }
    
    // Reverse the array to show oldest first (typical chat order)
    $formattedMessages = array_reverse($formattedMessages);
    
    // Return successful response
    echo json_encode([
        'success' => true,
        'message' => 'Messages retrieved successfully',
        'messages' => $formattedMessages,
        'total_messages' => (int)$totalMessages,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'conversation_id' => $conversationId
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve messages: ' . $e->getMessage()
    ]);
}
