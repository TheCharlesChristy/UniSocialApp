<?php
/**
 * Reset Password Endpoint
 * 
 * Updates password using reset token
 * Requires: token (string), new_password (string)
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed']);
    exit();
}

// Get database connection
$Database = require_once dirname(dirname(dirname(__FILE__))) . '/db_handler/connection.php';
require_once 'auth_utils.php';

// Check database connection
if (!$Database->isConnected()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit();
}

// Get request body
$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody, true);

// Check if data is valid JSON
if ($data === null) {
    http_response_code(400); // Bad request
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit();
}

// Check if token is provided
if (!isset($data['token']) || empty(trim($data['token']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Token is required']);
    exit();
}

// Check if new password is provided
if (!isset($data['new_password']) || empty(trim($data['new_password']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'New password is required']);
    exit();
}

$token = trim($data['token']);
$newPassword = $data['new_password'];

// Validate token
$tokenData = AuthUtils::validateToken($token, 'reset');
if ($tokenData === false) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Invalid or expired reset token']);
    exit();
}

// Check if token is blacklisted
if (AuthUtils::isTokenBlacklisted($token, $Database)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'This reset token has already been used']);
    exit();
}

// Get user ID from token
$userId = $tokenData['user_id'];

// Get user from database
$user = $Database->query("SELECT user_id, account_status FROM users WHERE user_id = ?", [$userId]);

if ($user === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $Database->getLastError()]);
    exit();
}

if (empty($user)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

// Get the user data
$user = $user[0];

// Check account status
if ($user['account_status'] !== 'active') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Account is not active']);
    exit();
}

// Validate password strength (at least 8 characters with letters and numbers)
if (strlen($newPassword) < 8 || !preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Password must be at least 8 characters long and contain both letters and numbers'
    ]);
    exit();
}

// Hash new password
$hashedPassword = AuthUtils::hashPassword($newPassword);

// Begin transaction
$Database->beginTransaction();

try {
    // Update user password
    $updateResult = $Database->execute(
        "UPDATE users SET password = ? WHERE user_id = ?", 
        [$hashedPassword, $userId]
    );

    if ($updateResult === false) {
        throw new Exception('Failed to update password: ' . $Database->getLastError());
    }
    
    // Blacklist the used token
    $blacklistResult = AuthUtils::blacklistToken($token, $Database);
    
    if ($blacklistResult === false) {
        throw new Exception('Failed to invalidate token: ' . $Database->getLastError());
    }
    
    // Commit transaction
    $Database->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Password has been reset successfully'
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $Database->rollBack();
    
    // Return error response
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
