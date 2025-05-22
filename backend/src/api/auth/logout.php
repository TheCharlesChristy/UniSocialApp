<?php
/**
 * User Logout Endpoint
 * 
 * Invalidates current user token by adding it to blacklist
 * Requires: token (string)
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

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

$token = trim($data['token']);

// Validate token
$tokenData = AuthUtils::validateToken($token);
if ($tokenData === false) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Invalid or expired token']);
    exit();
}

// Check if token is already blacklisted
if (AuthUtils::isTokenBlacklisted($token, $Database)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Token already invalidated']);
    exit();
}

// Add token to blacklist
$blacklistResult = AuthUtils::blacklistToken($token, $Database);

if ($blacklistResult === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to invalidate token: ' . $Database->getLastError()]);
    exit();
}

// Occasionally cleanup expired blacklisted tokens
if (mt_rand(1, 10) === 1) { // ~10% probability to run cleanup
    AuthUtils::cleanupBlacklist($Database);
}

// Return success
echo json_encode([
    'success' => true,
    'message' => 'Successfully logged out'
]);
