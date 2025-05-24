<?php
/**
 * Get Specific Post API Endpoint
 * 
 * Retrieves a specific post with full details
 * Endpoint: GET /api/posts/get_post/:postId
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

// Get the post ID from URL or query parameters
$postId = null;

// Method 1: Check URL path
$requestUri = $_SERVER['REQUEST_URI'];
$pattern = '/\/api\/posts\/get_post\/(\d+)$/';
if (preg_match($pattern, $requestUri, $matches)) {
    $postId = $matches[1];
}

// Method 2: Check if post ID is passed as a GET parameter
if (!$postId && isset($_GET['postId'])) {
    $postId = $_GET['postId'];
}

// Method 3: Check for 'id' parameter
if (!$postId && isset($_GET['id'])) {
    $postId = $_GET['id'];
}

// Method 4: Last segment of the URL path
if (!$postId) {
    $pathSegments = explode('/', trim($requestUri, '/'));
    $lastSegment = end($pathSegments);
    if (is_numeric($lastSegment)) {
        $postId = $lastSegment;
    }
}

// Validate post ID
if (!$postId || !is_numeric($postId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valid post ID is required']);
    exit();
}

$postId = (int)$postId;
$userId = $authUser['user_id'];

// Get post with privacy check and engagement data
$sql = "
    SELECT 
        p.post_id,
        p.user_id,
        p.caption,
        p.post_type,
        p.media_url,
        p.created_at,
        p.updated_at,
        p.privacy_level,
        p.location_lat,
        p.location_lng,
        p.location_name,
        u.username,
        u.first_name,
        u.last_name,
        u.profile_picture,
        COALESCE(like_counts.likes_count, 0) as likes_count,
        COALESCE(comment_counts.comments_count, 0) as comments_count,
        CASE WHEN user_likes.like_id IS NOT NULL THEN 1 ELSE 0 END as user_has_liked
    FROM posts p
    INNER JOIN users u ON p.user_id = u.user_id
    LEFT JOIN friendships f1 ON (f1.user_id_1 = ? AND f1.user_id_2 = p.user_id AND f1.status = 'accepted')
    LEFT JOIN friendships f2 ON (f2.user_id_2 = ? AND f2.user_id_1 = p.user_id AND f2.status = 'accepted')
    LEFT JOIN (
        SELECT post_id, COUNT(*) as likes_count
        FROM likes
        WHERE post_id IS NOT NULL
        GROUP BY post_id
    ) like_counts ON p.post_id = like_counts.post_id
    LEFT JOIN (
        SELECT post_id, COUNT(*) as comments_count
        FROM comments
        GROUP BY post_id
    ) comment_counts ON p.post_id = comment_counts.post_id
    LEFT JOIN likes user_likes ON p.post_id = user_likes.post_id AND user_likes.user_id = ?
    WHERE p.post_id = ?
    AND u.account_status = 'active'
    AND (
        p.user_id = ? OR  -- User's own posts
        p.privacy_level = 'public' OR  -- Public posts
        (p.privacy_level = 'friends' AND (f1.friendship_id IS NOT NULL OR f2.friendship_id IS NOT NULL))  -- Friends-only posts from accepted friends
    )
";

$post = $Database->query($sql, [$userId, $userId, $userId, $postId, $userId]);

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

// Return the post
echo json_encode([
    'success' => true,
    'post' => $post[0]
]);
