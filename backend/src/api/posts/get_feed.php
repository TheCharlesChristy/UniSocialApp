<?php
/**
 * Get Feed Posts API Endpoint
 * 
 * Retrieves posts for user's feed with pagination and filtering
 * Endpoint: GET /api/posts/get_feed
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

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';

// Validate pagination parameters
if ($page < 1) {
    $page = 1;
}
if ($limit < 1 || $limit > 50) {
    $limit = 10;
}

// Calculate offset
$offset = ($page - 1) * $limit;

// Get user ID
$userId = $authUser['user_id'];

// Build WHERE clause for posts visible to user
$whereClause = "
    WHERE u.account_status = 'active'
    AND (
        p.user_id = ? OR  -- User's own posts
        p.privacy_level = 'public' OR  -- Public posts from all users
        (p.privacy_level = 'friends' AND (f1.friendship_id IS NOT NULL OR f2.friendship_id IS NOT NULL))  -- Friends-only posts from accepted friends
    )
";

// Add filter condition if provided
$filterCondition = '';
$params = [$userId];
if (!empty($filter)) {
    $filterCondition = " AND (p.caption LIKE ? OR p.location_name LIKE ?)";
    $params[] = "%$filter%";
    $params[] = "%$filter%";
}

$fullWhereClause = $whereClause . $filterCondition;

// Get total count
$countSql = "
    SELECT COUNT(DISTINCT p.post_id) as total 
    FROM posts p
    INNER JOIN users u ON p.user_id = u.user_id
    LEFT JOIN friendships f1 ON (f1.user_id_1 = ? AND f1.user_id_2 = p.user_id AND f1.status = 'accepted')
    LEFT JOIN friendships f2 ON (f2.user_id_2 = ? AND f2.user_id_1 = p.user_id AND f2.status = 'accepted')
    " . $fullWhereClause;

$countParams = array_merge([$userId, $userId], $params);
$totalResult = $Database->query($countSql, $countParams);

if ($totalResult === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$totalPosts = $totalResult[0]['total'];
$totalPages = ceil($totalPosts / $limit);

// Get posts with engagement data
$postsSql = "
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
    " . $fullWhereClause . "
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
";

// Build parameters for posts query
$postsParams = array_merge([$userId, $userId, $userId], $params, [$limit, $offset]);

$posts = $Database->query($postsSql, $postsParams);

if ($posts === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

// Format response
$response = [
    'success' => true,
    'posts' => $posts,
    'total_posts' => (int)$totalPosts,
    'current_page' => $page,
    'total_pages' => $totalPages
];

echo json_encode($response);
