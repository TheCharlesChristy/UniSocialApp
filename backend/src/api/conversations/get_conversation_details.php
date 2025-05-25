<?php
/**
 * Get Conversation Details API Endpoint
 * 
 * Gets details of a specific conversation
 * Endpoint: GET /api/conversations/:conversationId
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
    
    // Get conversation details
    $conversationQuery = "
        SELECT 
            conversation_id,
            created_at,
            updated_at,
            is_group_chat,
            group_name
        FROM conversations 
        WHERE conversation_id = ?
    ";
    
    $conversation = $Database->query($conversationQuery, [$conversationId]);
    if ($conversation === false) {
        throw new Exception('Failed to get conversation details');
    }
    
    if (empty($conversation)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Conversation not found']);
        exit();
    }
    
    $conversationData = $conversation[0];
    
    // Get all participants
    $participantsQuery = "
        SELECT 
            u.user_id,
            u.username,
            u.first_name,
            u.last_name,
            u.profile_picture,
            u.account_status,
            cp.joined_at,
            cp.left_at
        FROM conversation_participants cp
        INNER JOIN users u ON cp.user_id = u.user_id
        WHERE cp.conversation_id = ?
        ORDER BY cp.joined_at ASC
    ";
    
    $participants = $Database->query($participantsQuery, [$conversationId]);
    if ($participants === false) {
        throw new Exception('Failed to get conversation participants');
    }
    
    // Separate active and inactive participants
    $activeParticipants = [];
    $leftParticipants = [];
    
    foreach ($participants as $participant) {
        if ($participant['left_at'] === null) {
            $activeParticipants[] = $participant;
        } else {
            $leftParticipants[] = $participant;
        }
    }
    
    // Get message count and last message info
    $statsQuery = "
        SELECT 
            COUNT(*) as total_messages,
            MAX(created_at) as last_message_time
        FROM messages 
        WHERE conversation_id = ?
    ";
    
    $stats = $Database->query($statsQuery, [$conversationId]);
    if ($stats === false) {
        throw new Exception('Failed to get conversation statistics');
    }
    
    $conversationStats = $stats[0];
    
    // Get unread message count for current user
    $unreadQuery = "
        SELECT COUNT(*) as unread_count 
        FROM messages 
        WHERE conversation_id = ? AND sender_id != ? AND is_read = FALSE
    ";
    
    $unreadResult = $Database->query($unreadQuery, [$conversationId, $authUser['user_id']]);
    if ($unreadResult === false) {
        throw new Exception('Failed to get unread message count');
    }
    
    $unreadCount = $unreadResult[0]['unread_count'];
    
    // Prepare response
    $response = [
        'success' => true,
        'message' => 'Conversation details retrieved successfully',
        'conversation' => [
            'conversation_id' => (int)$conversationData['conversation_id'],
            'is_group_chat' => (bool)$conversationData['is_group_chat'],
            'group_name' => $conversationData['group_name'],
            'created_at' => $conversationData['created_at'],
            'updated_at' => $conversationData['updated_at'],
            'total_messages' => (int)$conversationStats['total_messages'],
            'last_message_time' => $conversationStats['last_message_time'],
            'unread_count' => (int)$unreadCount
        ],
        'participants' => $activeParticipants,
        'left_participants' => $leftParticipants
    ];
    
    // For private chats, add display information
    if (!$conversationData['is_group_chat'] && count($activeParticipants) >= 2) {
        $otherUser = null;
        foreach ($activeParticipants as $participant) {
            if ($participant['user_id'] != $authUser['user_id']) {
                $otherUser = $participant;
                break;
            }
        }
        if ($otherUser) {
            $response['conversation']['display_name'] = $otherUser['first_name'] . ' ' . $otherUser['last_name'];
            $response['conversation']['display_picture'] = $otherUser['profile_picture'];
        }
    }
    
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to get conversation details: ' . $e->getMessage()
    ]);
}
