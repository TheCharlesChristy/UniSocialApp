<?php
/**
 * Get Friend Requests API Endpoint
 * 
 * Retrieves pending friend requests for current user
 * Endpoint: GET /api/friends/requests
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

// Get type parameter (received or sent)
$type = isset($_GET['type']) ? $_GET['type'] : 'received';

// Validate type parameter
if (!in_array($type, ['received', 'sent'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid type parameter. Must be "received" or "sent"']);
    exit();
}

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

// Validate pagination parameters
if ($page < 1) $page = 1;
if ($limit < 1) $limit = 20;
if ($limit > 50) $limit = 50;

$offset = ($page - 1) * $limit;
$userId = $authUser['user_id'];

if ($type === 'received') {
    // Get received friend requests (requests where current user is user_id_2)
    $countSql = "
        SELECT COUNT(*) as total
        FROM friendships f
        INNER JOIN users u ON u.user_id = f.user_id_1
        WHERE f.user_id_2 = ? AND f.status = 'pending'
        AND u.account_status = 'active'
    ";
    
    $requestsSql = "
        SELECT 
            u.user_id,
            u.username,
            u.first_name,
            u.last_name,
            u.profile_picture,
            u.bio,
            f.created_at as request_date
        FROM friendships f
        INNER JOIN users u ON u.user_id = f.user_id_1
        WHERE f.user_id_2 = ? AND f.status = 'pending'
        AND u.account_status = 'active'
        ORDER BY f.created_at DESC
        LIMIT ? OFFSET ?
    ";
} else {
    // Get sent friend requests (requests where current user is user_id_1)
    $countSql = "
        SELECT COUNT(*) as total
        FROM friendships f
        INNER JOIN users u ON u.user_id = f.user_id_2
        WHERE f.user_id_1 = ? AND f.status = 'pending'
        AND u.account_status = 'active'
    ";
    
    $requestsSql = "
        SELECT 
            u.user_id,
            u.username,
            u.first_name,
            u.last_name,
            u.profile_picture,
            u.bio,
            f.created_at as request_date
        FROM friendships f
        INNER JOIN users u ON u.user_id = f.user_id_2
        WHERE f.user_id_1 = ? AND f.status = 'pending'
        AND u.account_status = 'active'
        ORDER BY f.created_at DESC
        LIMIT ? OFFSET ?
    ";
}

// Get count
$countResult = $Database->query($countSql, [$userId]);

if ($countResult === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$total = $countResult[0]['total'];
$totalPages = ceil($total / $limit);

// Get requests
$requests = $Database->query($requestsSql, [$userId, $limit, $offset]);

if ($requests === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

// Return friend requests
echo json_encode([
    'success' => true,
    'type' => $type,
    'requests' => $requests ?: [],
    'total_requests' => $total,
    'current_page' => $page,
    'total_pages' => $totalPages
]);
