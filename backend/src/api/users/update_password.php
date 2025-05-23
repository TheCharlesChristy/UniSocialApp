<?php
/**
 * Update User Password API Endpoint
 * 
 * Updates current user's password
 * Endpoint: PUT /api/users/me/password
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

// Only allow PUT/POST requests (use POST as a fallback for clients that don't support PUT)
if ($_SERVER['REQUEST_METHOD'] != 'PUT' && $_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Require authentication
$requireAuth = true;

// Get database connection
$Database = require_once dirname(dirname(dirname(__FILE__))) . '/db_handler/connection.php';

// Include authentication middleware (this will exit if not authorized)
require_once dirname(dirname(__FILE__)) . '/auth/auth_middleware.php';

// If code execution reaches here, user is authenticated
// $authUser contains the authenticated user data

// Parse input data
$input = json_decode(file_get_contents('php://input'), true);

// Check if required fields are provided
if (!isset($input['current_password']) || !isset($input['new_password'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Current password and new password are required'
    ]);
    exit;
}

// Trim whitespace
$currentPassword = trim($input['current_password']);
$newPassword = trim($input['new_password']);

// Validate that fields are not empty
if (empty($currentPassword) || empty($newPassword)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Current password and new password cannot be empty'
    ]);
    exit;
}

// Validate new password requirements
if (strlen($newPassword) < 8) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'New password must be at least 8 characters long'
    ]);
    exit;
}

// Fetch the user's current password hash from the database
$user = $Database->query(
    "SELECT password FROM users WHERE user_id = ?",
    [$authUser['user_id']]
);

if (!$user || empty($user)) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'User not found'
    ]);
    exit;
}

// Verify current password
if (!password_verify($currentPassword, $user[0]['password'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Current password is incorrect'
    ]);
    exit;
}

// Generate new password hash
$newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

// Update the password in the database
$result = $Database->execute(
    "UPDATE users SET password = ? WHERE user_id = ?",
    [$newPasswordHash, $authUser['user_id']]
);

if ($result !== false) {
    echo json_encode([
        'success' => true,
        'message' => 'Password updated successfully'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update password'
    ]);
}
