<?php
/**
 * Admin Delete User API Endpoint
 * 
 * Soft deletes a user account (admin only)
 * Endpoint: DELETE /api/admin/users/:userId
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

// Include authentication middleware (this will exit if not authorized)
require_once dirname(dirname(__FILE__)) . '/auth/auth_middleware.php';

// Check if user is admin
if ($authUser['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit();
}

// Get request body
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit();
}

// Get user ID from request body
$userId = isset($input['user_id']) ? (int)$input['user_id'] : null;

if (!$userId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit();
}

// Prevent admin from deleting themselves
if ($userId === $authUser['user_id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
    exit();
}

try {
    // Check if user exists
    $existingUser = $Database->query(
        "SELECT user_id, username, account_status, role FROM users WHERE user_id = ? AND account_status != 'deleted'",
        [$userId]
    );
    
    if ($existingUser === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    
    if (empty($existingUser)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    $user = $existingUser[0];
    
    // Prevent admin from deleting other admin accounts
    if ($user['role'] === 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Cannot delete admin accounts']);
        exit();
    }

    // Begin transaction
    $Database->beginTransaction();

    // Soft delete the user (set status to deleted)
    $result = $Database->execute(
        "UPDATE users SET account_status = 'deleted', email = CONCAT('deleted_', user_id, '_', email) WHERE user_id = ?",
        [$userId]
    );
    
    if ($result === false) {
        $Database->rollBack();
        throw new Exception('Failed to delete user: ' . $Database->getLastError());
    }
    
    // Delete user's sessions by blacklisting all their tokens (if token blacklist table exists)
    // This is optional - we'll continue even if it fails
    $Database->execute(
        "INSERT INTO token_blacklist (token, blacklisted_at) 
         SELECT CONCAT('user_', ?), NOW() 
         WHERE NOT EXISTS (SELECT 1 FROM token_blacklist WHERE token = CONCAT('user_', ?))",
        [$userId, $userId]
    );
    
    // Commit transaction
    $Database->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'User deleted successfully',
        'deleted_user' => [
            'user_id' => $user['user_id'],
            'username' => $user['username']
        ]
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($Database->inTransaction ?? false) {
        $Database->rollBack();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}
