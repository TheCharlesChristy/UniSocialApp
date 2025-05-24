<?php
/**
 * Unlike Post API Endpoint
 * 
 * Removes a like from a post
 * Endpoint: DELETE /api/posts/unlike_post
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Allow DELETE and POST requests
if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'])) {
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

// Validate post ID
$postId = isset($input['post_id']) ? (int)$input['post_id'] : null;
if (!$postId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Post ID is required']);
    exit();
}

$userId = $authUser['user_id'];

// Check if like exists
$existingLike = $Database->query(
    "SELECT like_id FROM likes WHERE post_id = ? AND user_id = ?",
    [$postId, $userId]
);

if ($existingLike === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

if (empty($existingLike)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Post not liked yet']);
    exit();
}

// Remove like
$deleteSql = "DELETE FROM likes WHERE post_id = ? AND user_id = ?";
$result = $Database->execute($deleteSql, [$postId, $userId]);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to unlike post']);
    exit();
}

// Get updated like count
$countSql = "SELECT COUNT(*) as likes_count FROM likes WHERE post_id = ?";
$countResult = $Database->query($countSql, [$postId]);
$likesCount = $countResult ? $countResult[0]['likes_count'] : 0;

echo json_encode([
    'success' => true,
    'message' => 'Post unliked successfully',
    'likes_count' => (int)$likesCount
]);
