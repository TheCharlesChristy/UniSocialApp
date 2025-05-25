<?php
/**
 * Admin Activate User API Endpoint
 * 
 * Activates a suspended user account (admin only)
 * Endpoint: PUT /api/admin/users/:userId/activate
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Only allow PUT and POST requests
if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'])) {
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
    
    // Check if already active
    if ($user['account_status'] === 'active') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User is already active']);
        exit();
    }
    
    // Check if user is suspended (can only activate suspended users)
    if ($user['account_status'] !== 'suspended') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Can only activate suspended users']);
        exit();
    }

    // Activate the user
    $result = $Database->execute(
        "UPDATE users SET account_status = 'active' WHERE user_id = ?",
        [$userId]
    );
    
    if ($result === false) {
        throw new Exception('Failed to activate user: ' . $Database->getLastError());
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'User activated successfully',
        'user' => [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'account_status' => 'active'
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}
