<?php
/**
 * User Login Endpoint
 * 
 * Authenticates user and returns access token
 * Requires: email/username (string), password (string)
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
require_once dirname(dirname(dirname(__FILE__))) . '/db_handler/config.php';

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

// Check if login identifier (email or username) is provided
if ((!isset($data['email']) || empty(trim($data['email']))) && 
    (!isset($data['username']) || empty(trim($data['username'])))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email or username is required']);
    exit();
}

// Check if password is provided
if (!isset($data['password']) || empty(trim($data['password']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password is required']);
    exit();
}

// Prepare query based on login identifier (email or username)
if (isset($data['email']) && !empty(trim($data['email']))) {
    $loginField = 'email';
    $loginValue = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);
    
    if (!$loginValue) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit();
    }
} else {
    $loginField = 'username';
    $loginValue = trim($data['username']);
}

// Retrieve user by email or username
$sql = "SELECT user_id, username, email, password, account_status, last_login FROM users WHERE {$loginField} = ?";
$user = $Database->query($sql, [$loginValue]);

if ($user === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $Database->getLastError()]);
    exit();
}

if (empty($user)) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    exit();
}

// Get the first user (should be only one)
$user = $user[0];

// Verify account status
if ($user['account_status'] !== 'active') {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'Account is ' . $user['account_status'] . '. Please contact support.'
    ]);
    exit();
}

// Verify password
if (!AuthUtils::verifyPassword($data['password'], $user['password'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    exit();
}

// Update last login time
$updateLoginTimeResult = $Database->execute(
    "UPDATE users SET last_login = NOW() WHERE user_id = ?", 
    [$user['user_id']]
);

// Generate JWT token
$token = AuthUtils::generateToken($user['user_id']);

// Get token expiry from configuration
$config = new DatabaseConfig(__DIR__ . '/../../db_handler/config.txt');
$tokenExpiry = (int)$config->get('JWT_ACCESS_TOKEN_EXPIRE', 86400); // Default 24 hours
$expirationTime = time() + $tokenExpiry;

// Return success with token
echo json_encode([
    'success' => true,
    'token' => $token,
    'user_id' => $user['user_id'],
    'expiration' => date('c', $expirationTime)
]);
