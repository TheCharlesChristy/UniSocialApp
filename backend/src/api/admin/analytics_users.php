<?php
/**
 * Admin Analytics Users API Endpoint
 * 
 * Provides user analytics and statistics for admin dashboard
 * Endpoint: GET /api/admin/analytics/users
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
    // Get total users registered in date range
    $newUsers = $Database->query(
        "SELECT COUNT(*) as total FROM users WHERE DATE(registration_date) BETWEEN ? AND ? AND account_status != 'deleted'",
        [$startDate, $endDate]
    );
    if ($newUsers === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    $newUsersCount = $newUsers[0]['total'];

    // Get users by account status
    $usersByStatus = $Database->query(
        "SELECT account_status, COUNT(*) as count 
         FROM users 
         WHERE account_status != 'deleted'
         GROUP BY account_status"
    );
    if ($usersByStatus === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Get users by role
    $usersByRole = $Database->query(
        "SELECT role, COUNT(*) as count 
         FROM users 
         WHERE account_status != 'deleted'
         GROUP BY role"
    );
    if ($usersByRole === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Get daily user registration stats
    $dailyRegistrations = $Database->query(
        "SELECT DATE(registration_date) as date, COUNT(*) as count 
         FROM users 
         WHERE DATE(registration_date) BETWEEN ? AND ? AND account_status != 'deleted'
         GROUP BY DATE(registration_date) 
         ORDER BY date",
        [$startDate, $endDate]
    );
    if ($dailyRegistrations === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Get user age distribution (approximate from date_of_birth)
    $ageDistribution = $Database->query(
        "SELECT 
            CASE 
                WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 18 THEN 'Under 18'
                WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 18 AND 24 THEN '18-24'
                WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 25 AND 34 THEN '25-34'
                WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 35 AND 44 THEN '35-44'
                WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 45 AND 54 THEN '45-54'
                WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) >= 55 THEN '55+'
                ELSE 'Unknown'
            END as age_group,
            COUNT(*) as count
         FROM users 
         WHERE account_status != 'deleted' AND date_of_birth IS NOT NULL
         GROUP BY age_group"
    );
    if ($ageDistribution === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Get most active users (by posts)
    $mostActiveUsers = $Database->query(
        "SELECT 
            u.user_id,
            u.username,
            u.first_name,
            u.last_name,
            u.registration_date,
            COUNT(p.post_id) as posts_count
         FROM users u
         LEFT JOIN posts p ON u.user_id = p.user_id
         WHERE u.account_status != 'deleted'
         GROUP BY u.user_id
         ORDER BY posts_count DESC
         LIMIT 10"
    );
    if ($mostActiveUsers === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Get users with most friends
    $mostConnectedUsers = $Database->query(
        "SELECT 
            u.user_id,
            u.username,
            u.first_name,
            u.last_name,
            COUNT(f.friendship_id) as friends_count
         FROM users u
         LEFT JOIN friendships f ON (u.user_id = f.user_id_1 OR u.user_id = f.user_id_2) AND f.status = 'accepted'
         WHERE u.account_status != 'deleted'
         GROUP BY u.user_id
         ORDER BY friends_count DESC
         LIMIT 10"
    );
    if ($mostConnectedUsers === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Get login activity (users who logged in recently)
    $recentLoginActivity = $Database->query(
        "SELECT 
            DATE(last_login) as login_date,
            COUNT(DISTINCT user_id) as users_count
         FROM users 
         WHERE last_login IS NOT NULL 
         AND DATE(last_login) BETWEEN ? AND ?
         AND account_status != 'deleted'
         GROUP BY DATE(last_login)
         ORDER BY login_date DESC",
        [$startDate, $endDate]
    );
    if ($recentLoginActivity === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Get overall user engagement stats
    $engagementStats = $Database->query(
        "SELECT 
            COUNT(DISTINCT u.user_id) as total_active_users,
            COUNT(DISTINCT p.user_id) as users_with_posts,
            COUNT(DISTINCT l.user_id) as users_with_likes,
            COUNT(DISTINCT c.user_id) as users_with_comments,
            COUNT(DISTINCT f.user_id_1) as users_with_friends
         FROM users u
         LEFT JOIN posts p ON u.user_id = p.user_id
         LEFT JOIN likes l ON u.user_id = l.user_id
         LEFT JOIN comments c ON u.user_id = c.user_id
         LEFT JOIN friendships f ON (u.user_id = f.user_id_1 OR u.user_id = f.user_id_2) AND f.status = 'accepted'
         WHERE u.account_status = 'active'"
    );
    if ($engagementStats === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // Return analytics data
    echo json_encode([
        'success' => true,
        'message' => 'User analytics retrieved successfully',
        'analytics' => [
            'date_range' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ],
            'overview' => [
                'new_users_in_period' => (int)$newUsersCount,
                'engagement' => $engagementStats[0] ?? []
            ],
            'users_by_status' => $usersByStatus,
            'users_by_role' => $usersByRole,
            'age_distribution' => $ageDistribution,
            'daily_registrations' => $dailyRegistrations,
            'most_active_users' => $mostActiveUsers,
            'most_connected_users' => $mostConnectedUsers,
            'recent_login_activity' => $recentLoginActivity
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}
