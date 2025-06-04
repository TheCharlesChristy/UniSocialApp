<?php
/**
 * Get Comment Replies API Endpoint
 * 
 * Retrieves replies for a specific comment with pagination
 * Endpoint: GET /api/posts/get_comment_replies
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

// Include media API for formatting URLs
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/frontend/php/media-api.php';

// Include authentication middleware
require_once dirname(dirname(__FILE__)) . '/auth/auth_middleware.php';

// Get parameters
$commentId = isset($_GET['comment_id']) ? (int)$_GET['comment_id'] : null;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Validate comment ID
if (!$commentId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment ID is required']);
    exit();
}

// Validate pagination parameters
if ($page < 1) {
    $page = 1;
}
if ($limit < 1 || $limit > 50) {
    $limit = 10;
}

// Calculate offset
$offset = ($page - 1) * $limit;
$userId = $authUser['user_id'];

// Check if comment exists and user can view it
$checkSql = "
    SELECT c.comment_id, c.post_id, p.user_id as post_owner_id
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

$postId = $comment[0]['post_id'];

// Get total count of direct replies to this comment
$countSql = "SELECT COUNT(*) as total FROM comments WHERE parent_comment_id = ?";
$totalResult = $Database->query($countSql, [$commentId]);

if ($totalResult === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$totalReplies = $totalResult[0]['total'];
$totalPages = ceil($totalReplies / $limit);

// Get replies with pagination
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
    WHERE c.parent_comment_id = ?
    AND u.account_status = 'active'
    ORDER BY c.created_at ASC
    LIMIT ? OFFSET ?
";

$replies = $Database->query($repliesSql, [$userId, $commentId, $limit, $offset]);

if ($replies === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

// Function to count only direct replies (not nested replies)
function countDirectReplies($Database, $commentId) {
    $sql = "SELECT COUNT(*) as count FROM comments WHERE parent_comment_id = ?";
    $result = $Database->query($sql, [$commentId]);
    
    if ($result === false || empty($result)) {
        return 0;
    }
    
    return (int)$result[0]['count'];
}

// Function to recursively count all descendant comments (kept for compatibility)
function countAllDescendants($Database, $commentId) {
    $sql = "SELECT comment_id FROM comments WHERE parent_comment_id = ?";
    $directReplies = $Database->query($sql, [$commentId]);
    
    if ($directReplies === false) {
        return 0;
    }
    
    $count = count($directReplies);
    
    // Recursively count descendants of each direct reply
    foreach ($directReplies as $reply) {
        $count += countAllDescendants($Database, $reply['comment_id']);
    }
    
    return $count;
}

// Get direct reply counts for the fetched replies (count only direct replies, not nested)
$replyIds = array_column($replies, 'comment_id');
$replyCounts = [];

if (!empty($replyIds)) {
    foreach ($replyIds as $replyId) {
        $replyCounts[$replyId] = countDirectReplies($Database, $replyId);
    }
}

// Add reply counts to replies and format profile pictures
$mediaAPI = new MediaAPI();
foreach ($replies as &$reply) {
    $reply['reply_count'] = isset($replyCounts[$reply['comment_id']]) 
        ? $replyCounts[$reply['comment_id']] 
        : 0;
    $reply['replies'] = []; // Empty replies array for consistency
    $reply['has_more_replies'] = $reply['reply_count'] > 0;
    $reply['loaded_replies'] = 0;
    
    // Format profile picture URL
    $reply['profile_picture'] = $mediaAPI->formatMediaUrl($reply['profile_picture']);
}

// Format response
$response = [
    'success' => true,
    'replies' => $replies,
    'parent_comment_id' => $commentId,
    'total_replies' => (int)$totalReplies,
    'current_page' => $page,
    'total_pages' => $totalPages
];

echo json_encode($response);
