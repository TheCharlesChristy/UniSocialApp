<?php
/**
 * Delete User Account API Endpoint
 * 
 * Allows users to delete their own account or admins to delete any account
 * Endpoint: DELETE /api/users/delete_user.php
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

// Get user ID to delete from multiple sources
$userIdToDelete = null;

// 1. Try to get from URL query parameter
if (isset($_GET['userId']) && is_numeric($_GET['userId'])) {
    $userIdToDelete = (int)$_GET['userId'];
}

// 2. Try to get from request body
if (!$userIdToDelete) {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input && isset($input['userId']) && is_numeric($input['userId'])) {
        $userIdToDelete = (int)$input['userId'];
    }
}

// 3. If no specific user ID provided, delete current user's account
if (!$userIdToDelete) {
    $userIdToDelete = $authUser['user_id'];
}

$currentUserId = $authUser['user_id'];

// Check permissions - users can only delete their own account unless they're admin
if ($userIdToDelete !== $currentUserId) {
    // Check if current user is admin (you might need to adjust this based on your user roles system)
    $adminCheck = $Database->query(
        "SELECT role FROM users WHERE user_id = ?",
        [$currentUserId]
    );
    
    if (!$adminCheck || empty($adminCheck) || $adminCheck[0]['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied. You can only delete your own account']);
        exit();
    }
}

try {
    $Database->beginTransaction();
    
    // Check if user exists and is not already deleted
    $user = $Database->query(
        "SELECT user_id, username, account_status FROM users WHERE user_id = ?",
        [$userIdToDelete]
    );
    
    if (!$user || empty($user)) {
        $Database->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    $userData = $user[0];
    
    if ($userData['account_status'] === 'deleted') {
        $Database->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User account is already deleted']);
        exit();
    }
    
    // Soft delete approach - mark account as deleted instead of removing data
    // This preserves referential integrity and allows for account recovery
    $result = $Database->execute(
        "UPDATE users SET 
         account_status = 'deleted',
         email = CONCAT('deleted_', user_id, '_', email),
         username = CONCAT('deleted_', user_id, '_', username),
         updated_at = NOW()
         WHERE user_id = ?",
        [$userIdToDelete]
    );
    
    if ($result === false) {
        $Database->rollBack();
        throw new Exception('Failed to delete user account');
    }
    
    // Invalidate all user sessions by updating a token invalidation timestamp
    // (This assumes you have a way to track token validity)
    $Database->execute(
        "UPDATE users SET password_changed_at = NOW() WHERE user_id = ?",
        [$userIdToDelete]
    );
    
    // Optional: Clean up user's active sessions, notifications, etc.
    // Mark user's posts as from deleted user (optional)
    $Database->execute(
        "UPDATE posts SET updated_at = NOW() WHERE user_id = ?",
        [$userIdToDelete]
    );
    
    // Mark user's notifications as read/processed
    $Database->execute(
        "UPDATE notifications SET is_read = TRUE WHERE recipient_id = ? OR sender_id = ?",
        [$userIdToDelete, $userIdToDelete]
    );
    
    $Database->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'User account deleted successfully',
        'deleted_user_id' => $userIdToDelete,
        'deleted_username' => $userData['username']
    ]);

} catch (Exception $e) {
    $Database->rollBack();
    error_log("Delete user error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete user account']);
}
?>
