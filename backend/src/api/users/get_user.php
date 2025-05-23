<?php
/**
 * Get User Profile API Endpoint
 * 
 * Retrieves specific user's profile data
 * Endpoint: GET /api/users/:userId
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
require_once dirname(dirname(__FILE__)) . '/auth/auth_middleware.php';

// If code execution reaches here, user is authenticated
// $authUser contains the authenticated user data

// Get the user ID from URL or query parameters
$userId = null;

// Method 1: Check URL path (when nice URLs are working with .htaccess)
$requestUri = $_SERVER['REQUEST_URI'];
$pattern = '/\/api\/users\/(\d+)$/';
if (preg_match($pattern, $requestUri, $matches)) {
    $userId = $matches[1];
}

// Method 2: Check if user ID is passed as a GET parameter (alternative method)
if (!$userId && isset($_GET['userId'])) {
    $userId = $_GET['userId'];
}

// Method 3: Last segment of the URL path (when accessed directly)
if (!$userId) {
    $pathSegments = explode('/', trim($requestUri, '/'));
    $lastSegment = end($pathSegments);
    if (is_numeric($lastSegment)) {
        $userId = $lastSegment;
    }
}

// If we still don't have a user ID, return an error
if (!$userId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'User ID is required'
    ]);
    exit;
}

// Validate that the requested ID is an integer
if (!filter_var($userId, FILTER_VALIDATE_INT)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid user ID format'
    ]);
    exit;
}

// Query database for user information
$user = $Database->query(
    "SELECT user_id, username, first_name, last_name, profile_picture, bio 
     FROM users 
     WHERE user_id = ? AND account_status != 'deleted'",
    [$userId]
);

if (!$user || empty($user)) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'User not found'
    ]);
    exit;
}

$user = $user[0];

// If the user being viewed is the authenticated user, no need to check friendship status
$friendshipStatus = null;
if ($userId != $authUser['user_id']) {
    // Check friendship status between the authenticated user and the requested user
    $friendship = $Database->query(
        "SELECT status FROM friendships 
         WHERE (user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?)",
        [$authUser['user_id'], $userId, $userId, $authUser['user_id']]
    );

    if ($friendship && !empty($friendship)) {
        $friendshipStatus = $friendship[0]['status'];
    }
}

// Return user profile data
echo json_encode([
    'success' => true,
    'user' => [
        'user_id' => $user['user_id'],
        'username' => $user['username'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'profile_picture' => $user['profile_picture'],
        'bio' => $user['bio'],
        'friendship_status' => $friendshipStatus
    ]
]);
