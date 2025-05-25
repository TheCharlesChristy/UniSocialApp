<?php
/**
 * Admin Suspend User API Endpoint
 * 
 * Suspends a user account (admin only)
 * Endpoint: PUT /api/admin/users/:userId/suspend
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

// Prevent admin from suspending themselves
if ($userId === $authUser['user_id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Cannot suspend your own account']);
    exit();
}

// Parse input data for reason (optional)
$input = json_decode(file_get_contents('php://input'), true);
if (empty($input) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = $_POST;
}

$reason = isset($input['reason']) ? trim($input['reason']) : 'Suspended by admin';

try {
    // Check if user exists and is not already suspended
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
    
    // Prevent admin from suspending other admin accounts
    if ($user['role'] === 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Cannot suspend admin accounts']);
        exit();
    }
    
    // Check if already suspended
    if ($user['account_status'] === 'suspended') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User is already suspended']);
        exit();
    }

    // Suspend the user
    $result = $Database->execute(
        "UPDATE users SET account_status = 'suspended' WHERE user_id = ?",
        [$userId]
    );
    
    if ($result === false) {
        throw new Exception('Failed to suspend user: ' . $Database->getLastError());
    }
    
    // Log the suspension (optional - create admin action log if needed)
    // For now, we'll just return success
    
    echo json_encode([
        'success' => true,
        'message' => 'User suspended successfully',
        'user' => [
            'user_id' => $user['user_id'],
            'username' => $user['username'],
            'account_status' => 'suspended',
            'suspension_reason' => $reason
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}
