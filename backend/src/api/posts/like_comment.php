<?php
/**
 * Like Comment API Endpoint
 * 
 * Adds a like to a comment
 * Endpoint: POST /api/posts/like_comment
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

// Validate comment ID
$commentId = isset($input['comment_id']) ? (int)$input['comment_id'] : null;
if (!$commentId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment ID is required']);
    exit();
}

$userId = $authUser['user_id'];

// Check if comment exists and user can view it (via post privacy)
$checkSql = "
    SELECT 
        c.comment_id,
        c.post_id,
        p.user_id as post_user_id,
        p.privacy_level
    FROM comments c
    INNER JOIN posts p ON c.post_id = p.post_id
    INNER JOIN users u ON p.user_id = u.user_id
    LEFT JOIN friendships f1 ON (f1.user_id_1 = ? AND f1.user_id_2 = p.user_id AND f1.status = 'accepted')
    LEFT JOIN friendships f2 ON (f2.user_id_2 = ? AND f2.user_id_1 = p.user_id AND f2.status = 'accepted')
    WHERE c.comment_id = ?
    AND u.account_status = 'active'
    AND (
        p.user_id = ? OR  -- User's own posts
        p.privacy_level = 'public' OR  -- Public posts
        (p.privacy_level = 'friends' AND (f1.friendship_id IS NOT NULL OR f2.friendship_id IS NOT NULL))  -- Friends-only posts from accepted friends
    )
";

$comment = $Database->query($checkSql, [$userId, $userId, $commentId, $userId]);

if ($comment === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

if (empty($comment)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Comment not found or access denied']);
    exit();
}

// Check if user already liked this comment
$existingLike = $Database->query(
    "SELECT like_id FROM likes WHERE comment_id = ? AND user_id = ?",
    [$commentId, $userId]
);

if ($existingLike === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

if (!empty($existingLike)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment already liked']);
    exit();
}

// Add like
$insertSql = "INSERT INTO likes (comment_id, user_id, created_at) VALUES (?, ?, ?)";
$result = $Database->execute($insertSql, [$commentId, $userId, date('Y-m-d H:i:s')]);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to like comment']);
    exit();
}

// Get updated like count
$countSql = "SELECT COUNT(*) as likes_count FROM likes WHERE comment_id = ?";
$countResult = $Database->query($countSql, [$commentId]);
$likesCount = $countResult ? $countResult[0]['likes_count'] : 0;

echo json_encode([
    'success' => true,
    'message' => 'Comment liked successfully',
    'likes_count' => (int)$likesCount
]);
