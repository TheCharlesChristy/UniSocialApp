<?php
/**
 * Unlike Comment API Endpoint
 * 
 * Removes a like from a comment
 * Endpoint: DELETE /api/posts/unlike_comment
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

// Validate comment ID
$commentId = isset($input['comment_id']) ? (int)$input['comment_id'] : null;
if (!$commentId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment ID is required']);
    exit();
}

$userId = $authUser['user_id'];

// Verify comment exists and check if the user can access it (through post privacy)
$commentCheckSql = "SELECT c.comment_id, c.post_id, p.user_id as post_user_id, p.privacy_level 
                    FROM comments c 
                    JOIN posts p ON c.post_id = p.post_id 
                    WHERE c.comment_id = ?";
$commentResult = $Database->query($commentCheckSql, [$commentId]);

if ($commentResult === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

if (empty($commentResult)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Comment not found']);
    exit();
}

$comment = $commentResult[0];

// Check if user can access this comment based on post privacy
if ($comment['privacy_level'] === 'private' && $comment['post_user_id'] != $userId) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// Check if like exists
$existingLike = $Database->query(
    "SELECT like_id FROM likes WHERE comment_id = ? AND user_id = ?",
    [$commentId, $userId]
);

if ($existingLike === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

if (empty($existingLike)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment not liked yet']);
    exit();
}

// Remove like
$deleteSql = "DELETE FROM likes WHERE comment_id = ? AND user_id = ?";
$result = $Database->execute($deleteSql, [$commentId, $userId]);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to unlike comment']);
    exit();
}

// Get updated like count
$countSql = "SELECT COUNT(*) as likes_count FROM likes WHERE comment_id = ?";
$countResult = $Database->query($countSql, [$commentId]);
$likesCount = $countResult ? $countResult[0]['likes_count'] : 0;

echo json_encode([
    'success' => true,
    'message' => 'Comment unliked successfully',
    'likes_count' => (int)$likesCount
]);
