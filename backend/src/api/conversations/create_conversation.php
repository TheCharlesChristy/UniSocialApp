<?php
/**
 * Create Conversation API Endpoint
 * 
 * Creates a new conversation between users
 * Endpoint: POST /api/conversations
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

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

// Validate required fields
if (!isset($input['participants']) || !is_array($input['participants']) || empty($input['participants'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Participants array is required']);
    exit();
}

$participants = $input['participants'];
$isGroupChat = isset($input['is_group_chat']) ? (bool)$input['is_group_chat'] : false;
$groupName = isset($input['group_name']) ? trim($input['group_name']) : null;

// Validate participants
if (count($participants) < 1) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'At least one participant is required']);
    exit();
}

if (count($participants) > 50) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Maximum 50 participants allowed']);
    exit();
}

// Add current user to participants if not already included
if (!in_array($authUser['user_id'], $participants)) {
    $participants[] = $authUser['user_id'];
}

// If more than 2 participants, it must be a group chat
if (count($participants) > 2) {
    $isGroupChat = true;
}

// Validate group name for group chats
if ($isGroupChat && empty($groupName)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Group name is required for group chats']);
    exit();
}

if (!empty($groupName) && strlen($groupName) > 100) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Group name cannot exceed 100 characters']);
    exit();
}

// Remove duplicates and validate that all participants exist
$participants = array_unique($participants);
$participantIds = array_map('intval', $participants);

try {
    // Start transaction
    if (!$Database->beginTransaction()) {
        throw new Exception('Failed to start transaction');
    }

    // Verify all participants exist and are active
    $placeholders = str_repeat('?,', count($participantIds) - 1) . '?';
    $checkUsersQuery = "
        SELECT user_id, username, account_status 
        FROM users 
        WHERE user_id IN ($placeholders) AND account_status = 'active'
    ";
    
    $validUsers = $Database->query($checkUsersQuery, $participantIds);
    if ($validUsers === false) {
        throw new Exception('Failed to validate participants');
    }
    
    if (count($validUsers) !== count($participantIds)) {
        $Database->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'One or more participants are invalid or inactive']);
        exit();
    }

    // For private chats (2 participants), check if conversation already exists
    if (!$isGroupChat && count($participantIds) == 2) {
        $existingConvQuery = "
            SELECT c.conversation_id 
            FROM conversations c
            INNER JOIN conversation_participants cp1 ON c.conversation_id = cp1.conversation_id
            INNER JOIN conversation_participants cp2 ON c.conversation_id = cp2.conversation_id
            WHERE c.is_group_chat = FALSE 
            AND cp1.user_id = ? AND cp1.left_at IS NULL
            AND cp2.user_id = ? AND cp2.left_at IS NULL
            AND (SELECT COUNT(*) FROM conversation_participants cp3 
                 WHERE cp3.conversation_id = c.conversation_id 
                 AND cp3.left_at IS NULL) = 2
        ";
        
        $existingConv = $Database->query($existingConvQuery, [$participantIds[0], $participantIds[1]]);
        if ($existingConv === false) {
            throw new Exception('Failed to check for existing conversation');
        }
        
        if (!empty($existingConv)) {
            $Database->rollBack();
            echo json_encode([
                'success' => true,
                'message' => 'Conversation already exists',
                'conversation_id' => (int)$existingConv[0]['conversation_id'],
                'existing' => true
            ]);
            exit();
        }
    }

    // Create the conversation
    $createConvQuery = "
        INSERT INTO conversations (created_at, updated_at, is_group_chat, group_name)
        VALUES (NOW(), NOW(), ?, ?)
    ";
    
    $result = $Database->execute($createConvQuery, [$isGroupChat, $groupName]);
    if ($result === false) {
        throw new Exception('Failed to create conversation');
    }
    
    // Get the conversation ID
    $conversationId = $Database->query("SELECT LAST_INSERT_ID() as id")[0]['id'];
    
    // Add participants to the conversation
    $addParticipantQuery = "
        INSERT INTO conversation_participants (conversation_id, user_id, joined_at)
        VALUES (?, ?, NOW())
    ";
    
    foreach ($participantIds as $participantId) {
        $result = $Database->execute($addParticipantQuery, [$conversationId, $participantId]);
        if ($result === false) {
            throw new Exception('Failed to add participant');
        }
    }
    
    // Commit transaction
    if (!$Database->commit()) {
        throw new Exception('Failed to commit transaction');
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Conversation created successfully',
        'conversation_id' => (int)$conversationId
    ]);

} catch (Exception $e) {
    $Database->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create conversation: ' . $e->getMessage()
    ]);
}
