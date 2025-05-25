<?php
/**
 * Admin Update User API Endpoint
 * 
 * Updates user profile information (admin only)
 * Endpoint: PUT /api/admin/users/:userId
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
        "SELECT user_id, username, email, account_status, role FROM users WHERE user_id = ?",
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
    
    // Prevent admin from modifying other admin accounts (unless they're super admin)
    if ($user['role'] === 'admin' && $user['user_id'] !== $authUser['user_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Cannot modify other admin accounts']);
        exit();
    }

    // Initialize update fields
    $updateFields = [];
    $updateParams = [];
    
    // Check and validate each field that can be updated
    if (isset($input['first_name']) && trim($input['first_name']) !== '') {
        $updateFields[] = "first_name = ?";
        $updateParams[] = trim($input['first_name']);
    }
    
    if (isset($input['last_name']) && trim($input['last_name']) !== '') {
        $updateFields[] = "last_name = ?";
        $updateParams[] = trim($input['last_name']);
    }
    
    if (isset($input['email']) && trim($input['email']) !== '') {
        $email = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);
        if (!$email) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email address']);
            exit();
        }
        
        // Check if email is already taken by another user
        $emailCheck = $Database->query(
            "SELECT user_id FROM users WHERE email = ? AND user_id != ?",
            [$email, $userId]
        );
        
        if ($emailCheck && !empty($emailCheck)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email address is already taken']);
            exit();
        }
        
        $updateFields[] = "email = ?";
        $updateParams[] = $email;
    }
    
    if (isset($input['username']) && trim($input['username']) !== '') {
        $username = trim($input['username']);
        
        // Check if username is already taken by another user
        $usernameCheck = $Database->query(
            "SELECT user_id FROM users WHERE username = ? AND user_id != ?",
            [$username, $userId]
        );
        
        if ($usernameCheck && !empty($usernameCheck)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Username is already taken']);
            exit();
        }
        
        $updateFields[] = "username = ?";
        $updateParams[] = $username;
    }
    
    if (isset($input['bio'])) {
        $updateFields[] = "bio = ?";
        $updateParams[] = trim($input['bio']);
    }
    
    if (isset($input['account_status']) && in_array($input['account_status'], ['active', 'suspended'])) {
        $updateFields[] = "account_status = ?";
        $updateParams[] = $input['account_status'];
    }
    
    if (isset($input['role']) && in_array($input['role'], ['user', 'admin'])) {
        // Only allow role changes if not changing own role from admin
        if ($user['user_id'] === $authUser['user_id'] && $user['role'] === 'admin' && $input['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Cannot remove admin role from yourself']);
            exit();
        }
        
        $updateFields[] = "role = ?";
        $updateParams[] = $input['role'];
    }
    
    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No valid fields provided for update']);
        exit();
    }
    
    // Perform the update
    $updateSql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE user_id = ?";
    $updateParams[] = $userId;
    
    $result = $Database->execute($updateSql, $updateParams);
    
    if ($result === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    
    // Get updated user data
    $updatedUser = $Database->query(
        "SELECT user_id, username, email, first_name, last_name, profile_picture, bio, 
                date_of_birth, registration_date, last_login, account_status, role 
         FROM users WHERE user_id = ?",
        [$userId]
    );
    
    if ($updatedUser === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'User updated successfully',
        'user' => $updatedUser[0]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}
