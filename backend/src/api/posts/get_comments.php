<?php
/**
 * Get Comments API Endpoint
 * 
 * Retrieves comments for a specific post
 * Endpoint: GET /api/posts/get_comments
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

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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

// Get parameters
$postId = isset($_GET['post_id']) ? (int)$_GET['post_id'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

// Validate post ID
if (!$postId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Post ID is required']);
    exit();
}

// Validate pagination parameters
if ($page < 1) {
    $page = 1;
}
if ($limit < 1 || $limit > 50) {
    $limit = 20;
}

// Calculate offset
$offset = ($page - 1) * $limit;
$userId = $authUser['user_id'];

// Check if post exists and user can view it
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

// Get total count of top-level comments only (for pagination)
$countSql = "SELECT COUNT(*) as total FROM comments WHERE post_id = ? AND parent_comment_id IS NULL";
$totalResult = $Database->query($countSql, [$postId]);

if ($totalResult === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$totalTopLevelComments = $totalResult[0]['total'];
$totalPages = ceil($totalTopLevelComments / $limit);

// First, get the top-level comments with pagination
$topLevelSql = "
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
        COALESCE(like_counts.likes_count, 0) as likes_count,
        CASE WHEN user_likes.like_id IS NOT NULL THEN 1 ELSE 0 END as user_has_liked
    FROM comments c
    INNER JOIN users u ON c.user_id = u.user_id
    LEFT JOIN (
        SELECT comment_id, COUNT(*) as likes_count
        FROM likes
        WHERE comment_id IS NOT NULL
        GROUP BY comment_id
    ) like_counts ON c.comment_id = like_counts.comment_id
    LEFT JOIN likes user_likes ON c.comment_id = user_likes.comment_id AND user_likes.user_id = ?
    WHERE c.post_id = ?
    AND c.parent_comment_id IS NULL
    AND u.account_status = 'active'
    ORDER BY c.created_at ASC
    LIMIT ? OFFSET ?
";

$topLevelComments = $Database->query($topLevelSql, [$userId, $postId, $limit, $offset]);

if ($topLevelComments === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

// If no top-level comments, return empty result
if (empty($topLevelComments)) {
    $response = [
        'success' => true,
        'comments' => [],
        'total_comments' => (int)$totalTopLevelComments,
        'current_page' => $page,
        'total_pages' => $totalPages
    ];
    echo json_encode($response);
    exit();
}

// Get all comment IDs from the top-level comments
$topLevelCommentIds = array_column($topLevelComments, 'comment_id');
$placeholders = str_repeat('?,', count($topLevelCommentIds) - 1) . '?';

// Now get ALL replies for these top-level comments (no pagination on replies)
$repliesSql = "
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
        COALESCE(like_counts.likes_count, 0) as likes_count,
        CASE WHEN user_likes.like_id IS NOT NULL THEN 1 ELSE 0 END as user_has_liked
    FROM comments c
    INNER JOIN users u ON c.user_id = u.user_id
    LEFT JOIN (
        SELECT comment_id, COUNT(*) as likes_count
        FROM likes
        WHERE comment_id IS NOT NULL
        GROUP BY comment_id
    ) like_counts ON c.comment_id = like_counts.comment_id
    LEFT JOIN likes user_likes ON c.comment_id = user_likes.comment_id AND user_likes.user_id = ?
    WHERE c.post_id = ?
    AND c.parent_comment_id IN ($placeholders)
    AND u.account_status = 'active'
    ORDER BY c.created_at ASC
";

$queryParams = array_merge([$userId, $postId], $topLevelCommentIds);
$replies = $Database->query($repliesSql, $queryParams);

if ($replies === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

// Combine top-level comments and replies
$comments = array_merge($topLevelComments, $replies);

// Organize comments into nested structure
$commentTree = [];
$commentMap = [];

// First pass: create map and identify top-level comments
foreach ($comments as $comment) {
    $commentMap[$comment['comment_id']] = $comment;
    $commentMap[$comment['comment_id']]['replies'] = [];
    
    if ($comment['parent_comment_id'] === null) {
        $commentTree[] = &$commentMap[$comment['comment_id']];
    }
}

// Second pass: add replies to their parent comments
foreach ($comments as $comment) {
    if ($comment['parent_comment_id'] !== null && 
        isset($commentMap[$comment['parent_comment_id']])) {
        $commentMap[$comment['parent_comment_id']]['replies'][] = &$commentMap[$comment['comment_id']];
    }
}

// Format response
$response = [
    'success' => true,
    'comments' => $commentTree,
    'total_comments' => (int)$totalTopLevelComments,
    'current_page' => $page,
    'total_pages' => $totalPages
];

echo json_encode($response);
