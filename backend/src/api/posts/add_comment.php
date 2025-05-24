<?php
/**
 * Add Comment API Endpoint
 * 
 * Adds a comment to a post
 * Endpoint: POST /api/posts/add_comment
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

// Validate required fields
$postId = isset($input['post_id']) ? (int)$input['post_id'] : null;
$content = isset($input['content']) ? trim($input['content']) : '';
$parentCommentId = isset($input['parent_comment_id']) ? (int)$input['parent_comment_id'] : null;

if (!$postId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Post ID is required']);
    exit();
}

if (empty($content)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment content is required']);
    exit();
}

// Validate content length (max 1000 characters)
if (strlen($content) > 1000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment content too long. Maximum 1000 characters allowed']);
    exit();
}

$userId = $authUser['user_id'];

// Check if post exists and user can view/comment on it
$checkSql = "
    SELECT p.post_id
    FROM posts p
    INNER JOIN users u ON p.user_id = u.user_id
    LEFT JOIN friendships f1 ON (f1.user_id_1 = ? AND f1.user_id_2 = p.user_id AND f1.status = 'accepted')
    LEFT JOIN friendships f2 ON (f2.user_id_2 = ? AND f2.user_id_1 = p.user_id AND f2.status = 'accepted')
    WHERE p.post_id = ?
    AND u.account_status = 'active'
    AND (
        p.user_id = ? OR  -- User's own posts
        p.privacy_level = 'public' OR  -- Public posts
        (p.privacy_level = 'friends' AND (f1.friendship_id IS NOT NULL OR f2.friendship_id IS NOT NULL))  -- Friends-only posts from accepted friends
    )
";

$post = $Database->query($checkSql, [$userId, $userId, $postId, $userId]);

if ($post === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

if (empty($post)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Post not found or access denied']);
    exit();
}

// If this is a reply, validate parent comment exists and belongs to the same post
if ($parentCommentId) {
    $parentCheckSql = "SELECT comment_id FROM comments WHERE comment_id = ? AND post_id = ?";
    $parentComment = $Database->query($parentCheckSql, [$parentCommentId, $postId]);
    
    if ($parentComment === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit();
    }
    
    if (empty($parentComment)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Parent comment not found']);
        exit();
    }
}

// Insert comment
$currentDateTime = date('Y-m-d H:i:s');

$insertSql = "
    INSERT INTO comments (post_id, user_id, content, created_at, updated_at, parent_comment_id)
    VALUES (?, ?, ?, ?, ?, ?)
";

$params = [
    $postId,
    $userId,
    $content,
    $currentDateTime,
    $currentDateTime,
    $parentCommentId
];

$result = $Database->execute($insertSql, $params);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
    exit();
}

// Get the created comment ID
$commentId = $Database->query("SELECT LAST_INSERT_ID() as comment_id")[0]['comment_id'];

// Get the complete comment data with user information
$commentSql = "
    SELECT 
        c.comment_id,
        c.post_id,
        c.user_id,
        c.content,
        c.created_at,
        c.updated_at,
        c.parent_comment_id,
        u.username,
        u.first_name,
        u.last_name,
        u.profile_picture,
        0 as likes_count,
        0 as user_has_liked
    FROM comments c
    INNER JOIN users u ON c.user_id = u.user_id
    WHERE c.comment_id = ?
";

$commentData = $Database->query($commentSql, [$commentId]);

if ($commentData === false || empty($commentData)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve comment data']);
    exit();
}

echo json_encode([
    'success' => true,
    'message' => 'Comment added successfully',
    'comment' => $commentData[0]
]);
