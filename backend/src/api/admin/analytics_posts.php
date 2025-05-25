<?php
/**
 * Admin Analytics Posts API Endpoint
 * 
 * Provides post analytics and statistics for admin dashboard
 * Endpoint: GET /api/admin/analytics/posts
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

// Include authentication middleware (this will exit if not authorized)
require_once dirname(dirname(__FILE__)) . '/auth/auth_middleware.php';

// Check if user is admin
if ($authUser['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Admin access required']);
    exit();
}

// Get query parameters for date range
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid date format. Use YYYY-MM-DD']);
    exit();
}

try {
    // Get total posts in date range
    $totalPosts = $Database->query(
        "SELECT COUNT(*) as total FROM posts WHERE DATE(created_at) BETWEEN ? AND ?",
        [$startDate, $endDate]
    );
    if ($totalPosts === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    $totalPostsCount = $totalPosts[0]['total'];

    // Get posts by type
    $postsByType = $Database->query(
        "SELECT post_type, COUNT(*) as count 
         FROM posts 
         WHERE DATE(created_at) BETWEEN ? AND ? 
         GROUP BY post_type",
        [$startDate, $endDate]
    );
    if ($postsByType === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Get posts by privacy level
    $postsByPrivacy = $Database->query(
        "SELECT privacy_level, COUNT(*) as count 
         FROM posts 
         WHERE DATE(created_at) BETWEEN ? AND ? 
         GROUP BY privacy_level",
        [$startDate, $endDate]
    );
    if ($postsByPrivacy === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Get daily post creation stats
    $dailyPosts = $Database->query(
        "SELECT DATE(created_at) as date, COUNT(*) as count 
         FROM posts 
         WHERE DATE(created_at) BETWEEN ? AND ? 
         GROUP BY DATE(created_at) 
         ORDER BY date",
        [$startDate, $endDate]
    );
    if ($dailyPosts === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Get most liked posts in period
    $topLikedPosts = $Database->query(
        "SELECT 
            p.post_id,
            p.caption,
            p.post_type,
            p.created_at,
            u.username,
            u.first_name,
            u.last_name,
            COUNT(l.like_id) as likes_count
         FROM posts p
         INNER JOIN users u ON p.user_id = u.user_id
         LEFT JOIN likes l ON p.post_id = l.post_id
         WHERE DATE(p.created_at) BETWEEN ? AND ?
         GROUP BY p.post_id
         ORDER BY likes_count DESC
         LIMIT 10",
        [$startDate, $endDate]
    );
    if ($topLikedPosts === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Get most commented posts in period
    $topCommentedPosts = $Database->query(
        "SELECT 
            p.post_id,
            p.caption,
            p.post_type,
            p.created_at,
            u.username,
            u.first_name,
            u.last_name,
            COUNT(c.comment_id) as comments_count
         FROM posts p
         INNER JOIN users u ON p.user_id = u.user_id
         LEFT JOIN comments c ON p.post_id = c.post_id
         WHERE DATE(p.created_at) BETWEEN ? AND ?
         GROUP BY p.post_id
         ORDER BY comments_count DESC
         LIMIT 10",
        [$startDate, $endDate]
    );
    if ($topCommentedPosts === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Get top posting users
    $topPosters = $Database->query(
        "SELECT 
            u.user_id,
            u.username,
            u.first_name,
            u.last_name,
            COUNT(p.post_id) as posts_count
         FROM users u
         INNER JOIN posts p ON u.user_id = p.user_id
         WHERE DATE(p.created_at) BETWEEN ? AND ?
         GROUP BY u.user_id
         ORDER BY posts_count DESC
         LIMIT 10",
        [$startDate, $endDate]
    );
    if ($topPosters === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Get engagement statistics
    $engagementStats = $Database->query(
        "SELECT 
            COUNT(DISTINCT p.post_id) as total_posts,
            COUNT(DISTINCT l.like_id) as total_likes,
            COUNT(DISTINCT c.comment_id) as total_comments,
            ROUND(COUNT(DISTINCT l.like_id) / COUNT(DISTINCT p.post_id), 2) as avg_likes_per_post,
            ROUND(COUNT(DISTINCT c.comment_id) / COUNT(DISTINCT p.post_id), 2) as avg_comments_per_post
         FROM posts p
         LEFT JOIN likes l ON p.post_id = l.post_id
         LEFT JOIN comments c ON p.post_id = c.post_id
         WHERE DATE(p.created_at) BETWEEN ? AND ?",
        [$startDate, $endDate]
    );
    if ($engagementStats === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Return analytics data
    echo json_encode([
        'success' => true,
        'message' => 'Post analytics retrieved successfully',
        'analytics' => [
            'date_range' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'overview' => [
                'total_posts' => (int)$totalPostsCount,
                'engagement' => $engagementStats[0] ?? []
            ],
            'posts_by_type' => $postsByType,
            'posts_by_privacy' => $postsByPrivacy,
            'daily_posts' => $dailyPosts,
            'top_liked_posts' => $topLikedPosts,
            'top_commented_posts' => $topCommentedPosts,
            'top_posters' => $topPosters
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}
