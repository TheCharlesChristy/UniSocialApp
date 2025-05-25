<?php
/**
 * Update Privacy Settings API Endpoint
 * 
 * Updates user's privacy settings
 * Endpoint: PUT /api/privacy
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

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$userId = $authUser['user_id'];

// Define valid values for each setting
$validPostPrivacy = ['public', 'friends', 'private'];
$validProfileVisibility = ['public', 'friends', 'private'];
$validFriendListVisibility = ['public', 'friends', 'private'];
$validWhoCanSendRequests = ['everyone', 'friends_of_friends', 'nobody'];

$updatedSettings = [];
$updateFields = [];
$updateParams = [];

try {
    $Database->beginTransaction();
    
    // Check if privacy settings exist, create if not
    $existingSettings = $Database->query(
        "SELECT privacy_id FROM privacy_settings WHERE user_id = ?",
        [$userId]
    );
    
    if (!$existingSettings || empty($existingSettings)) {
        // Create default privacy settings
        $createResult = $Database->execute(
            "INSERT INTO privacy_settings (user_id, post_default_privacy, profile_visibility, friend_list_visibility, who_can_send_requests, created_at, updated_at) 
             VALUES (?, 'public', 'public', 'friends', 'everyone', NOW(), NOW())",
            [$userId]
        );
        
        if ($createResult === false) {
            $Database->rollBack();
            throw new Exception('Failed to create privacy settings');
        }
    }
    
    // Validate and prepare updates
    if (isset($input['post_default_privacy'])) {
        $value = trim($input['post_default_privacy']);
        if (in_array($value, $validPostPrivacy)) {
            $updateFields[] = "post_default_privacy = ?";
            $updateParams[] = $value;
            $updatedSettings[] = 'post_default_privacy';
        } else {
            $Database->rollBack();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid post_default_privacy value. Must be: public, friends, or private']);
            exit();
        }
    }
    
    if (isset($input['profile_visibility'])) {
        $value = trim($input['profile_visibility']);
        if (in_array($value, $validProfileVisibility)) {
            $updateFields[] = "profile_visibility = ?";
            $updateParams[] = $value;
            $updatedSettings[] = 'profile_visibility';
        } else {
            $Database->rollBack();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid profile_visibility value. Must be: public, friends, or private']);
            exit();
        }
    }
    
    if (isset($input['friend_list_visibility'])) {
        $value = trim($input['friend_list_visibility']);
        if (in_array($value, $validFriendListVisibility)) {
            $updateFields[] = "friend_list_visibility = ?";
            $updateParams[] = $value;
            $updatedSettings[] = 'friend_list_visibility';
        } else {
            $Database->rollBack();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid friend_list_visibility value. Must be: public, friends, or private']);
            exit();
        }
    }
    
    if (isset($input['who_can_send_requests'])) {
        $value = trim($input['who_can_send_requests']);
        if (in_array($value, $validWhoCanSendRequests)) {
            $updateFields[] = "who_can_send_requests = ?";
            $updateParams[] = $value;
            $updatedSettings[] = 'who_can_send_requests';
        } else {
            $Database->rollBack();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid who_can_send_requests value. Must be: everyone, friends_of_friends, or nobody']);
            exit();
        }
    }
    
    // Check if there are any fields to update
    if (empty($updateFields)) {
        $Database->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No valid fields provided for update']);
        exit();
    }
    
    // Add updated_at to the update
    $updateFields[] = "updated_at = NOW()";
    $updateParams[] = $userId;
    
    // Build and execute update query
    $updateSql = "UPDATE privacy_settings SET " . implode(', ', $updateFields) . " WHERE user_id = ?";
    $result = $Database->execute($updateSql, $updateParams);
    
    if ($result === false) {
        $Database->rollBack();
        throw new Exception('Failed to update privacy settings');
    }
    
    $Database->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Privacy settings updated successfully',
        'updated_settings' => $updatedSettings
    ]);

} catch (Exception $e) {
    $Database->rollBack();
    error_log("Update privacy settings error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update privacy settings']);
}
?>
