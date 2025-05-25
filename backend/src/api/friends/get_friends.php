<?php
/**
 * Get Friends API Endpoint
 * 
 * Retrieves current user's friends list
 * Endpoint: GET /api/friends
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
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

// Validate pagination parameters
if ($page < 1) $page = 1;
if ($limit < 1) $limit = 20;
if ($limit > 50) $limit = 50; // Maximum limit to prevent overload

$offset = ($page - 1) * $limit;
$userId = $authUser['user_id'];

// Get friends count
$countSql = "
    SELECT COUNT(*) as total
    FROM friendships f
    INNER JOIN users u ON (
        (f.user_id_1 = ? AND u.user_id = f.user_id_2) OR 
        (f.user_id_2 = ? AND u.user_id = f.user_id_1)
    )
    WHERE f.status = 'accepted'
    AND u.account_status = 'active'
";

$countResult = $Database->query($countSql, [$userId, $userId]);

if ($countResult === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$total = $countResult[0]['total'];
$totalPages = ceil($total / $limit);

// Get friends list
$friendsSql = "
    SELECT 
        u.user_id,
        u.username,
        u.first_name,
        u.last_name,
        u.profile_picture,
        u.bio,
        f.created_at as friends_since
    FROM friendships f
    INNER JOIN users u ON (
        (f.user_id_1 = ? AND u.user_id = f.user_id_2) OR 
        (f.user_id_2 = ? AND u.user_id = f.user_id_1)
    )
    WHERE f.status = 'accepted'
    AND u.account_status = 'active'
    ORDER BY f.created_at DESC
    LIMIT ? OFFSET ?
";

$friends = $Database->query($friendsSql, [$userId, $userId, $limit, $offset]);

if ($friends === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

// Return friends list
echo json_encode([
    'success' => true,
    'friends' => $friends ?: [],
    'total_friends' => $total,
    'current_page' => $page,
    'total_pages' => $totalPages
]);
