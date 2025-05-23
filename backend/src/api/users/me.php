<?php
/**
 * Example Protected Endpoint
 * 
 * Demonstrates how to use auth_middleware.php to protect API endpoints
 * This endpoint requires authentication
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Require authentication
$requireAuth = true;

// Get database connection
$Database = require_once dirname(dirname(dirname(__FILE__))) . '/db_handler/connection.php';

// Include authentication middleware (this will exit if not authorized)
require_once dirname(__FILE__) . '/../auth/auth_middleware.php';

// If code execution reaches here, user is authenticated
// $authUser contains the authenticated user data

// Return user profile data (excluding sensitive fields)
echo json_encode([
    'success' => true,
    'message' => 'Authentication successful',
    'user' => [
        'id' => $authUser['user_id'],
        'username' => $authUser['username'],
        'email' => $authUser['email'],
        'role' => $authUser['role']
    ]
]);
