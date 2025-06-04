<?php
// filepath: c:\xampp\htdocs\backend\src\api\conversations\delete_conversation.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
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

try {
    // Get user ID from authenticated user
    $user_id = $authUser['user_id'];
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid JSON input'
        ]);
        exit();
    }
    
    // Validate required fields
    if (!isset($input['conversation_id']) || empty($input['conversation_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Conversation ID is required'
        ]);
        exit();
    }
    
    $conversation_id = intval($input['conversation_id']);
    
    if ($conversation_id <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid conversation ID'
        ]);
        exit();
    }
    
    // Start transaction
    if (!$Database->beginTransaction()) {
        throw new Exception('Failed to start transaction');
    }
    
    // Check if user is a participant in the conversation
    $check_participant_query = "
        SELECT cp.id, c.is_group_chat, c.created_at
        FROM conversation_participants cp
        JOIN conversations c ON cp.conversation_id = c.conversation_id
        WHERE cp.conversation_id = ? AND cp.user_id = ? AND cp.left_at IS NULL
    ";
    $participant_result = $Database->query($check_participant_query, [$conversation_id, $user_id]);
    
    if ($participant_result === false || empty($participant_result)) {
        $Database->rollBack();
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'You are not an active participant in this conversation'
        ]);
        exit();
    }
    
    $participant = $participant_result[0];
      // For group conversations, check if user has permission to delete
    // Only allow deletion if user is the current owner or if it's a private conversation
    if ($participant['is_group_chat']) {
        // Find the current owner (earliest active participant, or earliest overall if no active participants)
        $current_owner_query = "
            SELECT user_id 
            FROM conversation_participants 
            WHERE conversation_id = ? AND left_at IS NULL
            ORDER BY joined_at ASC 
            LIMIT 1
        ";
        $current_owner_result = $Database->query($current_owner_query, [$conversation_id]);
        
        // If no active participants found, check if user is the original creator
        if ($current_owner_result === false || empty($current_owner_result)) {
            // Fallback to original creator check
            $original_creator_query = "
                SELECT user_id 
                FROM conversation_participants 
                WHERE conversation_id = ? 
                ORDER BY joined_at ASC 
                LIMIT 1
            ";
            $original_creator_result = $Database->query($original_creator_query, [$conversation_id]);
            
            if ($original_creator_result === false || empty($original_creator_result)) {
                $Database->rollBack();
                throw new Exception('Failed to check conversation ownership');
            }
            
            $current_owner_id = $original_creator_result[0]['user_id'];
        } else {
            $current_owner_id = $current_owner_result[0]['user_id'];
        }
        
        if ($current_owner_id != $user_id) {
            $Database->rollBack();
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Only the conversation owner can delete a group conversation'
            ]);
            exit();
        }
    }
    
    // Get conversation details for response
    $conversation_details_query = "
        SELECT conversation_id, is_group_chat, group_name, created_at
        FROM conversations 
        WHERE conversation_id = ?
    ";
    $conversation_details = $Database->query($conversation_details_query, [$conversation_id]);
    
    if ($conversation_details === false || empty($conversation_details)) {
        $Database->rollBack();
        throw new Exception('Failed to get conversation details');
    }
    
    $conversation_info = $conversation_details[0];
    
    // Count messages that will be deleted
    $message_count_query = "
        SELECT COUNT(*) as message_count 
        FROM messages 
        WHERE conversation_id = ?
    ";
    $message_count_result = $Database->query($message_count_query, [$conversation_id]);
    
    if ($message_count_result === false) {
        $Database->rollBack();
        throw new Exception('Failed to get message count');
    }
    
    $message_count = $message_count_result[0]['message_count'];
    
    // Delete all messages in the conversation (cascade should handle this, but being explicit)
    $delete_messages_query = "DELETE FROM messages WHERE conversation_id = ?";
    $delete_messages_result = $Database->execute($delete_messages_query, [$conversation_id]);
    
    if ($delete_messages_result === false) {
        $Database->rollBack();
        throw new Exception('Failed to delete conversation messages');
    }
    
    // Delete all participant records (cascade should handle this, but being explicit)
    $delete_participants_query = "DELETE FROM conversation_participants WHERE conversation_id = ?";
    $delete_participants_result = $Database->execute($delete_participants_query, [$conversation_id]);
    
    if ($delete_participants_result === false) {
        $Database->rollBack();
        throw new Exception('Failed to delete conversation participants');
    }
    
    // Delete the conversation itself
    $delete_conversation_query = "DELETE FROM conversations WHERE conversation_id = ?";
    $delete_conversation_result = $Database->execute($delete_conversation_query, [$conversation_id]);
    
    if ($delete_conversation_result === false) {
        $Database->rollBack();
        throw new Exception('Failed to delete conversation');
    }
    
    // Commit transaction
    if (!$Database->commit()) {
        throw new Exception('Failed to commit transaction');
    }
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Conversation deleted successfully',
        'data' => [
            'conversation_id' => $conversation_id,
            'conversation_type' => $conversation_info['is_group_chat'] ? 'group' : 'private',
            'conversation_name' => $conversation_info['group_name'],
            'messages_deleted' => (int)$message_count,
            'deleted_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    $Database->rollBack();
    
    error_log("Delete conversation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
