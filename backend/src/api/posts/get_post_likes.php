<?php
/**
 * Get Post Likes API Endpoint
 * 
 * Retrieves list of users who liked a post
 * Endpoint: GET /api/posts/get_post_likes
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

// Get total count of likes
$countSql = "SELECT COUNT(*) as total FROM likes WHERE post_id = ?";
$totalResult = $Database->query($countSql, [$postId]);

if ($totalResult === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$totalLikes = $totalResult[0]['total'];
$totalPages = ceil($totalLikes / $limit);

// Get likes with user information
$likesSql = "
    SELECT 
        l.like_id,
        l.created_at,
        u.user_id,
        u.username,
        u.first_name,
        u.last_name,
        u.profile_picture
    FROM likes l
    INNER JOIN users u ON l.user_id = u.user_id
    WHERE l.post_id = ?
    AND u.account_status = 'active'
    ORDER BY l.created_at DESC
    LIMIT ? OFFSET ?
";

$likes = $Database->query($likesSql, [$postId, $limit, $offset]);

if ($likes === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

// Format response
$response = [
    'success' => true,
    'likes' => $likes,
    'total_likes' => (int)$totalLikes,
    'current_page' => $page,
    'total_pages' => $totalPages
];

echo json_encode($response);
