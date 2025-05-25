<?php
/**
 * Admin Dashboard API Endpoint
 * 
 * Provides overview statistics for admin dashboard
 * Endpoint: GET /api/admin/dashboard
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

// Get dashboard statistics
try {
    // Get total users count
    $totalUsers = $Database->query("SELECT COUNT(*) as total FROM users WHERE account_status != 'deleted'");
    if ($totalUsers === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    $totalUsersCount = $totalUsers[0]['total'];

    // Get active users count
    $activeUsers = $Database->query("SELECT COUNT(*) as total FROM users WHERE account_status = 'active'");
    if ($activeUsers === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    $activeUsersCount = $activeUsers[0]['total'];

    // Get suspended users count
    $suspendedUsers = $Database->query("SELECT COUNT(*) as total FROM users WHERE account_status = 'suspended'");
    if ($suspendedUsers === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    $suspendedUsersCount = $suspendedUsers[0]['total'];

    // Get total posts count
    $totalPosts = $Database->query("SELECT COUNT(*) as total FROM posts");
    if ($totalPosts === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    $totalPostsCount = $totalPosts[0]['total'];

    // Get pending reports count
    $pendingReports = $Database->query("SELECT COUNT(*) as total FROM reports WHERE status = 'pending'");
    if ($pendingReports === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    $pendingReportsCount = $pendingReports[0]['total'];

    // Get new users this week
    $newUsersThisWeek = $Database->query(
        "SELECT COUNT(*) as total FROM users WHERE registration_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
    );
    if ($newUsersThisWeek === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    $newUsersThisWeekCount = $newUsersThisWeek[0]['total'];

    // Get new posts this week
    $newPostsThisWeek = $Database->query(
        "SELECT COUNT(*) as total FROM posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
    );
    if ($newPostsThisWeek === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    $newPostsThisWeekCount = $newPostsThisWeek[0]['total'];

    // Get recent activity (last 10 users)
    $recentUsers = $Database->query(
        "SELECT user_id, username, first_name, last_name, registration_date, account_status 
         FROM users 
         WHERE account_status != 'deleted'
         ORDER BY registration_date DESC 
         LIMIT 10"
    );
    if ($recentUsers === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Return dashboard data
    echo json_encode([
        'success' => true,
        'message' => 'Dashboard data retrieved successfully',
        'dashboard' => [
            'statistics' => [
                'total_users' => (int)$totalUsersCount,
                'active_users' => (int)$activeUsersCount,
                'suspended_users' => (int)$suspendedUsersCount,
                'total_posts' => (int)$totalPostsCount,
                'pending_reports' => (int)$pendingReportsCount,
                'new_users_this_week' => (int)$newUsersThisWeekCount,
                'new_posts_this_week' => (int)$newPostsThisWeekCount
            ],
            'recent_users' => $recentUsers
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}
