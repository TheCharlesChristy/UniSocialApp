<?php
/**
 * Forgot Password Endpoint
 * 
 * Initiates password reset process by sending email with reset token
 * Requires: email (string)
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

// Check if email is provided
if (!isset($data['email']) || empty(trim($data['email']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit();
}

// Validate email
$email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
if (!$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit();
}

// Check if user exists
$user = $Database->query("SELECT user_id, account_status FROM users WHERE email = ?", [$email]);

if ($user === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $Database->getLastError()]);
    exit();
}

// For security reasons, always return success even if email does not exist
// This prevents email enumeration attacks
if (empty($user)) {
    // We don't tell the client that the email doesn't exist
    echo json_encode([
        'success' => true,
        'message' => 'If your email is registered, you will receive password reset instructions'
    ]);
    exit();
}

// Get the user data
$user = $user[0];

// Check account status
if ($user['account_status'] !== 'active') {
    // We don't tell the client about the account status for security
    echo json_encode([
        'success' => true,
        'message' => 'If your email is registered, you will receive password reset instructions'
    ]);
    exit();
}

// Generate reset token
$resetToken = AuthUtils::generateToken($user['user_id'], 'reset');

// Send password reset email
$emailSent = AuthUtils::sendPasswordResetEmail($email, $resetToken);

if (!$emailSent) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send reset email']);
    exit();
}

// Return success
echo json_encode([
    'success' => true,
    'message' => 'If your email is registered, you will receive password reset instructions'
]);
