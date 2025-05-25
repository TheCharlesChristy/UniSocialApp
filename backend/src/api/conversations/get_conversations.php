<?php
/**
 * Get User Conversations API Endpoint
 * 
 * Gets user's conversations with pagination
 * Endpoint: GET /api/conversations
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

// Get pagination parameters from query string
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

// Validate pagination parameters
if ($page < 1) {
    $page = 1;
}
if ($limit < 1 || $limit > 100) {
    $limit = 20;
}

$offset = ($page - 1) * $limit;

try {
    // Get total count of conversations for this user
    $countQuery = "
        SELECT COUNT(DISTINCT c.conversation_id) as total
        FROM conversations c
        INNER JOIN conversation_participants cp ON c.conversation_id = cp.conversation_id
        WHERE cp.user_id = ? AND cp.left_at IS NULL
    ";
    
    $countResult = $Database->query($countQuery, [$authUser['user_id']]);
    if ($countResult === false) {
        throw new Exception('Failed to get conversation count');
    }
    
    $totalConversations = $countResult[0]['total'];
    $totalPages = ceil($totalConversations / $limit);
    
    // Get conversations with participant information
    $conversationsQuery = "
        SELECT DISTINCT
            c.conversation_id,
            c.is_group_chat,
            c.group_name,
            c.created_at,
            c.updated_at,
            -- Get last message info
            (SELECT content FROM messages m 
             WHERE m.conversation_id = c.conversation_id 
             ORDER BY m.created_at DESC LIMIT 1) as last_message_content,
            (SELECT created_at FROM messages m 
             WHERE m.conversation_id = c.conversation_id 
             ORDER BY m.created_at DESC LIMIT 1) as last_message_time,
            -- Get unread message count for this user
            (SELECT COUNT(*) FROM messages m 
             WHERE m.conversation_id = c.conversation_id 
             AND m.sender_id != ? AND m.is_read = FALSE) as unread_count
        FROM conversations c
        INNER JOIN conversation_participants cp ON c.conversation_id = cp.conversation_id
        WHERE cp.user_id = ? AND cp.left_at IS NULL
        ORDER BY c.updated_at DESC
        LIMIT ? OFFSET ?
    ";
    
    $conversations = $Database->query($conversationsQuery, [
        $authUser['user_id'], 
        $authUser['user_id'], 
        $limit, 
        $offset
    ]);
    
    if ($conversations === false) {
        throw new Exception('Failed to get conversations');
    }
    
    // For each conversation, get participant information
    $conversationsWithParticipants = [];
    foreach ($conversations as $conversation) {
        // Get all participants for this conversation
        $participantsQuery = "
            SELECT 
                u.user_id,
                u.username,
                u.first_name,
                u.last_name,
                u.profile_picture,
                cp.joined_at
            FROM conversation_participants cp
            INNER JOIN users u ON cp.user_id = u.user_id
            WHERE cp.conversation_id = ? AND cp.left_at IS NULL
            ORDER BY cp.joined_at ASC
        ";
        
        $participants = $Database->query($participantsQuery, [$conversation['conversation_id']]);
        if ($participants === false) {
            throw new Exception('Failed to get conversation participants');
        }
        
        $conversation['participants'] = $participants;
        $conversation['participant_count'] = count($participants);
        
        // For private chats, get the other user's info for display name
        if (!$conversation['is_group_chat'] && count($participants) >= 2) {
            $otherUser = null;
            foreach ($participants as $participant) {
                if ($participant['user_id'] != $authUser['user_id']) {
                    $otherUser = $participant;
                    break;
                }
            }
            if ($otherUser) {
                $conversation['display_name'] = $otherUser['first_name'] . ' ' . $otherUser['last_name'];
                $conversation['display_picture'] = $otherUser['profile_picture'];
            }
        } else if ($conversation['is_group_chat']) {
            $conversation['display_name'] = $conversation['group_name'];
            $conversation['display_picture'] = null; // Group chats don't have profile pictures
        }
        
        $conversationsWithParticipants[] = $conversation;
    }
    
    // Return successful response
    echo json_encode([
        'success' => true,
        'message' => 'Conversations retrieved successfully',
        'conversations' => $conversationsWithParticipants,
        'total_conversations' => (int)$totalConversations,
        'current_page' => $page,
        'total_pages' => $totalPages
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve conversations: ' . $e->getMessage()
    ]);
}
