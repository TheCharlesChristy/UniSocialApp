<?php
/**
 * Get User Posts API Endpoint
 * 
 * Retrieves posts from a specific user
 * Endpoint: GET /api/users/:userId/posts
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
$pattern = '/\/api\/users\/(\d+)\/posts/';
if (preg_match($pattern, $requestUri, $matches)) {
    $userId = $matches[1];
}

// Method 2: Check if user ID is passed as a GET parameter (alternative method)
if (!$userId && isset($_GET['userId'])) {
    $userId = $_GET['userId'];
}

// Method 3: Extract from URL path segments
if (!$userId) {
    $pathSegments = explode('/', trim($requestUri, '/'));
    $userIdIndex = array_search('users', $pathSegments);
    if ($userIdIndex !== false && isset($pathSegments[$userIdIndex + 1]) && is_numeric($pathSegments[$userIdIndex + 1])) {
        $userId = $pathSegments[$userIdIndex + 1];
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

// Check if the user exists and is not deleted
$user = $Database->query(
    "SELECT user_id FROM users WHERE user_id = ? AND account_status != 'deleted'",
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

// Handle pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Validate pagination parameters
if ($page < 1) $page = 1;
if ($limit < 1) $limit = 10;
if ($limit > 50) $limit = 50; // Maximum limit to prevent overload

$offset = ($page - 1) * $limit;

// Determine which posts the authenticated user can see based on privacy settings and friendship status
$privacyCondition = "";

// If the user is viewing their own posts, they can see all posts
if ($userId == $authUser['user_id']) {
    $privacyCondition = ""; // No additional condition needed
} else {
    // Check friendship status
    $friendship = $Database->query(
        "SELECT status FROM friendships 
         WHERE ((user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?))
         AND status = 'accepted'",
        [$authUser['user_id'], $userId, $userId, $authUser['user_id']]
    );

    $isFriend = ($friendship && !empty($friendship));
    
    if ($isFriend) {
        // Friends can see public and friends posts
        $privacyCondition = "AND privacy_level IN ('public', 'friends')";
    } else {
        // Non-friends can only see public posts
        $privacyCondition = "AND privacy_level = 'public'";
    }
}

// Get total count of posts
$totalQuery = "SELECT COUNT(*) as total FROM posts 
               WHERE user_id = ? {$privacyCondition}";
               
$totalResult = $Database->query($totalQuery, [$userId]);
$totalPosts = $totalResult[0]['total'];

// Calculate total pages
$totalPages = ceil($totalPosts / $limit);

// Get posts with pagination
$postsQuery = "SELECT p.post_id, p.user_id, p.caption, p.post_type, p.media_url, 
                     p.created_at, p.updated_at, p.privacy_level, 
                     p.location_lat, p.location_lng, p.location_name,
                     u.username, u.first_name, u.last_name, u.profile_picture
               FROM posts p
               JOIN users u ON p.user_id = u.user_id
               WHERE p.user_id = ? {$privacyCondition}
               ORDER BY p.created_at DESC
               LIMIT ? OFFSET ?";

$posts = $Database->query(
    $postsQuery, 
    [$userId, $limit, $offset]
);

// Add like counts to each post
if ($posts && !empty($posts)) {
    foreach ($posts as $key => $post) {
        $likesCount = $Database->query(
            "SELECT COUNT(*) as count FROM likes WHERE post_id = ?",
            [$post['post_id']]
        );
        
        $commentCount = $Database->query(
            "SELECT COUNT(*) as count FROM comments WHERE post_id = ?",
            [$post['post_id']]
        );
        
        // Check if the authenticated user has liked this post
        $userLiked = $Database->query(
            "SELECT * FROM likes WHERE post_id = ? AND user_id = ?",
            [$post['post_id'], $authUser['user_id']]
        );
        
        $posts[$key]['likes_count'] = $likesCount[0]['count'];
        $posts[$key]['comments_count'] = $commentCount[0]['count'];
        $posts[$key]['user_has_liked'] = !empty($userLiked);
    }
}

// Return the posts data
echo json_encode([
    'success' => true,
    'posts' => $posts ?: [],
    'total_posts' => $totalPosts,
    'current_page' => $page,
    'total_pages' => $totalPages
]);
