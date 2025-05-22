<?php
/**
 * Verify Email Endpoint
 * 
 * Verifies user email using token
 * Requires: token (URL parameter)
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Only GET requests are allowed']);
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

// Get token from URL
$requestUri = $_SERVER['REQUEST_URI'];
$parts = explode('/', $requestUri);
$token = end($parts);

// Clean up token (remove query string if present)
if (strpos($token, '?') !== false) {
    $token = substr($token, 0, strpos($token, '?'));
}

// Check if token is provided
if (empty($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Verification token is required']);
    exit();
}

// Validate token
$tokenData = AuthUtils::validateToken($token, 'verify');
if ($tokenData === false) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Invalid or expired verification token']);
    exit();
}

// Check if token is blacklisted
if (AuthUtils::isTokenBlacklisted($token, $Database)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'This verification token has already been used']);
    exit();
}

// Get user ID from token
$userId = $tokenData['user_id'];

// Get user from database
$user = $Database->query(
    "SELECT user_id, email, account_status FROM users WHERE user_id = ?", 
    [$userId]
);

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

// Begin transaction
$Database->beginTransaction();

try {
    // For this implementation we're assuming email verification just marks the account as active
    // If you need a separate email_verified field, add it to the users table
    
    // Update user status to active if not already
    if ($user['account_status'] !== 'active') {
        $updateResult = $Database->execute(
            "UPDATE users SET account_status = 'active' WHERE user_id = ?", 
            [$userId]
        );

        if ($updateResult === false) {
            throw new Exception('Failed to verify email: ' . $Database->getLastError());
        }
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
        'message' => 'Email verified successfully'
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $Database->rollBack();
    
    // Return error response
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
