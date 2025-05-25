<?php
/**
 * Admin Get Users API Endpoint
 * 
 * Retrieves paginated list of all users with management options
 * Endpoint: GET /api/admin/users
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

// Get query parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Validate pagination parameters
if ($page < 1) {
    $page = 1;
}
if ($limit < 1 || $limit > 100) {
    $limit = 20;
}

// Calculate offset
$offset = ($page - 1) * $limit;

// Build WHERE clause
$whereConditions = ["account_status != 'deleted'"];
$params = [];

// Add status filter if provided
if (!empty($status) && in_array($status, ['active', 'suspended'])) {
    $whereConditions[] = "account_status = ?";
    $params[] = $status;
}

// Add search filter if provided
if (!empty($search)) {
    $whereConditions[] = "(username LIKE ? OR email LIKE ? OR CONCAT(first_name, ' ', last_name) LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$whereClause = "WHERE " . implode(" AND ", $whereConditions);

try {
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM users $whereClause";
    $totalResult = $Database->query($countSql, $params);
    
    if ($totalResult === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }
    
    $totalUsers = $totalResult[0]['total'];
    $totalPages = ceil($totalUsers / $limit);

    // Get users with pagination
    $usersSql = "
        SELECT 
            user_id,
            username,
            email,
            first_name,
            last_name,
            profile_picture,
            bio,
            date_of_birth,
            registration_date,
            last_login,
            account_status,
            role
        FROM users 
        $whereClause
        ORDER BY registration_date DESC
        LIMIT ? OFFSET ?
    ";

    $usersParams = array_merge($params, [$limit, $offset]);
    $users = $Database->query($usersSql, $usersParams);
    
    if ($users === false) {
        throw new Exception('Database error: ' . $Database->getLastError());
    }

    // For each user, get additional statistics
    foreach ($users as &$user) {
        // Get posts count
        $postsCount = $Database->query(
            "SELECT COUNT(*) as total FROM posts WHERE user_id = ?",
            [$user['user_id']]
        );
        $user['posts_count'] = $postsCount ? (int)$postsCount[0]['total'] : 0;

        // Get reports count (reports against this user)
        $reportsCount = $Database->query(
            "SELECT COUNT(*) as total FROM reports WHERE reported_id = ? AND content_type = 'user'",
            [$user['user_id']]
        );
        $user['reports_count'] = $reportsCount ? (int)$reportsCount[0]['total'] : 0;

        // Get friends count
        $friendsCount = $Database->query(
            "SELECT COUNT(*) as total FROM friendships 
             WHERE (user_id_1 = ? OR user_id_2 = ?) AND status = 'accepted'",
            [$user['user_id'], $user['user_id']]
        );
        $user['friends_count'] = $friendsCount ? (int)$friendsCount[0]['total'] : 0;
    }

    // Return users data
    echo json_encode([
        'success' => true,
        'message' => 'Users retrieved successfully',
        'users' => $users,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_users' => (int)$totalUsers,
            'users_per_page' => $limit
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}
