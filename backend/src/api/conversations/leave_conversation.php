<?php
// filepath: c:\xampp\htdocs\webdev\backend\src\api\conversations\leave_conversation.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
      // Check if user is a participant in the conversation and hasn't already left
    $check_participant_query = "
        SELECT cp.id, c.is_group_chat 
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
    
    // For private conversations, prevent leaving (as it would break the conversation)
    if (!$participant['is_group_chat']) {
        $Database->rollBack();
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Cannot leave a private conversation. Use delete conversation instead.'
        ]);
        exit();
    }
    
    // Check if this is the last participant in a group conversation
    $active_participants_query = "
        SELECT COUNT(*) as active_count 
        FROM conversation_participants 
        WHERE conversation_id = ? AND left_at IS NULL
    ";
    $count_result = $Database->query($active_participants_query, [$conversation_id]);
    
    if ($count_result === false) {
        $Database->rollBack();
        throw new Exception('Failed to get participant count');
    }
      $active_count = $count_result[0]['active_count'];
    
    // Check if the leaving user is the current owner (earliest active participant)
    $current_owner_query = "
        SELECT user_id 
        FROM conversation_participants 
        WHERE conversation_id = ? AND left_at IS NULL
        ORDER BY joined_at ASC 
        LIMIT 1
    ";
    $current_owner_result = $Database->query($current_owner_query, [$conversation_id]);
    
    $is_owner_leaving = false;
    if ($current_owner_result && !empty($current_owner_result)) {
        $current_owner_id = $current_owner_result[0]['user_id'];
        $is_owner_leaving = ($current_owner_id == $user_id);
    }
    
    // Update the participant record to mark as left
    $leave_query = "
        UPDATE conversation_participants 
        SET left_at = NOW() 
        WHERE id = ?
    ";
    $leave_result = $Database->execute($leave_query, [$participant['id']]);
    
    if ($leave_result === false) {
        $Database->rollBack();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to leave conversation'
        ]);
        exit();
    }
    
    // Determine ownership transfer information for response
    $ownership_transferred = false;
    $new_owner_id = null;
    
    if ($is_owner_leaving && $active_count > 1) {
        // Find the new owner (next earliest active participant after the current user leaves)
        $new_owner_query = "
            SELECT user_id 
            FROM conversation_participants 
            WHERE conversation_id = ? AND left_at IS NULL AND user_id != ?
            ORDER BY joined_at ASC 
            LIMIT 1
        ";
        $new_owner_result = $Database->query($new_owner_query, [$conversation_id, $user_id]);
        
        if ($new_owner_result && !empty($new_owner_result)) {
            $new_owner_id = $new_owner_result[0]['user_id'];
            $ownership_transferred = true;
        }
    }
    
    // If this was the last participant, we could optionally delete the conversation
    // For now, we'll leave the conversation intact for history purposes
      // Update conversation's last activity
    $update_activity_query = "
        UPDATE conversations 
        SET updated_at = NOW() 
        WHERE conversation_id = ?
    ";    $update_result = $Database->execute($update_activity_query, [$conversation_id]);
    
    if ($update_result === false) {
        $Database->rollBack();
        error_log("Failed to update conversation activity for conversation_id: " . $conversation_id);
        throw new Exception('Failed to update conversation activity');
    }
    
    // Commit transaction
    if (!$Database->commit()) {
        throw new Exception('Failed to commit transaction');
    }
      http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Successfully left the conversation',
        'data' => [
            'conversation_id' => $conversation_id,
            'left_at' => date('Y-m-d H:i:s'),
            'remaining_participants' => $active_count - 1,
            'ownership_transferred' => $ownership_transferred,
            'new_owner_id' => $new_owner_id
        ]
    ]);
    
} catch (Exception $e) {
    $Database->rollBack();
    
    error_log("Leave conversation error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error'
    ]);
}
?>
