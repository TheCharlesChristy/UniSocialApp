<?php
/**
 * Get Privacy Settings API Endpoint
 * 
 * Gets user's privacy settings
 * Endpoint: GET /api/privacy
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

$userId = $authUser['user_id'];

try {
    // Get user's privacy settings
    $privacySettings = $Database->query(
        "SELECT 
            post_default_privacy,
            profile_visibility,
            friend_list_visibility,
            who_can_send_requests,
            created_at,
            updated_at
         FROM privacy_settings 
         WHERE user_id = ?",
        [$userId]
    );
    
    // If no privacy settings exist, create default ones
    if (!$privacySettings || empty($privacySettings)) {
        $Database->beginTransaction();
        
        $createResult = $Database->execute(
            "INSERT INTO privacy_settings (user_id, post_default_privacy, profile_visibility, friend_list_visibility, who_can_send_requests, created_at, updated_at) 
             VALUES (?, 'public', 'public', 'friends', 'everyone', NOW(), NOW())",
            [$userId]
        );
        
        if ($createResult === false) {
            $Database->rollBack();
            throw new Exception('Failed to create default privacy settings');
        }
        
        $Database->commit();
        
        // Get the newly created settings
        $privacySettings = $Database->query(
            "SELECT 
                post_default_privacy,
                profile_visibility,
                friend_list_visibility,
                who_can_send_requests,
                created_at,
                updated_at
             FROM privacy_settings 
             WHERE user_id = ?",
            [$userId]
        );
        
        if (!$privacySettings || empty($privacySettings)) {
            throw new Exception('Failed to retrieve privacy settings');
        }
    }
    
    $settings = $privacySettings[0];
    
    // Return success response
    echo json_encode([
        'success' => true,
        'privacy_settings' => [
            'post_default_privacy' => $settings['post_default_privacy'],
            'profile_visibility' => $settings['profile_visibility'],
            'friend_list_visibility' => $settings['friend_list_visibility'],
            'who_can_send_requests' => $settings['who_can_send_requests'],
            'created_at' => $settings['created_at'],
            'updated_at' => $settings['updated_at']
        ]
    ]);

} catch (Exception $e) {
    error_log("Get privacy settings error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve privacy settings']);
}
?>
