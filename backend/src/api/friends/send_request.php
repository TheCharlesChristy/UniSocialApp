<?php
/**
 * Send Friend Request API Endpoint
 * 
 * Sends a friend request to another user
 * Endpoint: POST /api/friends/request
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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Require authentication
$requireAuth = true;

// Get database connection
$Database = require_once dirname(dirname(dirname(__FILE__))) . '/db_handler/connection.php';

// Include authentication middleware
require_once dirname(dirname(__FILE__)) . '/auth/auth_middleware.php';

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Get target user ID from URL parameter or input data
$targetUserId = null;

// Check URL path for user ID
$requestUri = $_SERVER['REQUEST_URI'];
$pathParts = explode('/', trim($requestUri, '/'));
$requestIndex = array_search('request', $pathParts);
if ($requestIndex !== false && isset($pathParts[$requestIndex + 1])) {
    $targetUserId = (int)$pathParts[$requestIndex + 1];
}

// Fallback to input data
if (!$targetUserId && isset($input['user_id'])) {
    $targetUserId = (int)$input['user_id'];
}

// Also check query parameter
if (!$targetUserId && isset($_GET['userId'])) {
    $targetUserId = (int)$_GET['userId'];
}

if (!$targetUserId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Target user ID is required']);
    exit();
}

$currentUserId = $authUser['user_id'];

// Check if trying to send request to self
if ($currentUserId == $targetUserId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cannot send friend request to yourself']);
    exit();
}

// Check if target user exists and is active
$targetUser = $Database->query(
    "SELECT user_id, username FROM users WHERE user_id = ? AND account_status = 'active'",
    [$targetUserId]
);

if ($targetUser === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

if (empty($targetUser)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

// Check if user is blocked
$blockCheck = $Database->query(
    "SELECT block_id FROM blocks WHERE (blocker_id = ? AND blocked_id = ?) OR (blocker_id = ? AND blocked_id = ?)",
    [$currentUserId, $targetUserId, $targetUserId, $currentUserId]
);

if ($blockCheck && !empty($blockCheck)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Cannot send friend request']);
    exit();
}

// Check if friendship already exists
$existingFriendship = $Database->query(
    "SELECT friendship_id, status FROM friendships 
     WHERE (user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?)",
    [$currentUserId, $targetUserId, $targetUserId, $currentUserId]
);

if ($existingFriendship && !empty($existingFriendship)) {
    $status = $existingFriendship[0]['status'];
    if ($status === 'accepted') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'You are already friends with this user']);
        exit();
    } elseif ($status === 'pending') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Friend request already exists']);
        exit();
    }
}

// Create friend request
$result = $Database->execute(
    "INSERT INTO friendships (user_id_1, user_id_2, status, created_at) VALUES (?, ?, 'pending', NOW())",
    [$currentUserId, $targetUserId]
);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send friend request']);
    exit();
}

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Friend request sent successfully',
    'target_user' => [
        'user_id' => $targetUser[0]['user_id'],
        'username' => $targetUser[0]['username']
    ]
]);
