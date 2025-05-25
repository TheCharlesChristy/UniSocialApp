<?php
// Get Blocked Users Endpoint - GET /api/users/blocked
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$requireAuth = true;
$Database = require_once '../../db_handler/connection.php';
require_once '../auth/auth_middleware.php';

$currentUserId = $authUser['user_id']; // From auth middleware

// Get pagination parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 20;
$offset = ($page - 1) * $limit;

try {
    // Get total count of blocked users
    $totalResult = $Database->query("
        SELECT COUNT(*) as total
        FROM blocks b
        WHERE b.blocker_id = ?
    ", [$currentUserId]);
    $total = $totalResult[0]['total'];    // Get blocked users with pagination
    $blockedUsers = $Database->query("
        SELECT 
            u.user_id,
            u.username,
            CONCAT(u.first_name, ' ', u.last_name) as full_name,
            u.profile_picture,
            b.created_at as blocked_at
        FROM blocks b
        JOIN users u ON b.blocked_id = u.user_id
        WHERE b.blocker_id = ?
        ORDER BY b.created_at DESC
        LIMIT $limit OFFSET $offset
    ", [$currentUserId]);
    
    // Format the response
    $formattedUsers = array_map(function($user) {
        return [
            'user_id' => (int)$user['user_id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'profile_picture' => $user['profile_picture'],
            'blocked_at' => $user['blocked_at']
        ];
    }, $blockedUsers ?: []);
    
    echo json_encode([
        'success' => true,
        'data' => $formattedUsers,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_count' => (int)$total,
            'per_page' => $limit
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Get blocked users error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
