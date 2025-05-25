<?php
// Get Outgoing Friend Requests Endpoint - GET /api/friends/get_outgoing_requests
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

// Pagination parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 10;
$offset = ($page - 1) * $limit;

try {
    $currentUserId = $authUser['user_id']; // From auth middleware
    
    // Get total count of outgoing requests
    $countResult = $Database->query("
        SELECT COUNT(*) as total
        FROM friendships f
        INNER JOIN users u ON u.user_id = f.user_id_2
        WHERE f.user_id_1 = ? AND f.status = 'pending'
        AND u.account_status = 'active'
    ", [$currentUserId]);
    
    $totalRequests = $countResult[0]['total'] ?? 0;
    
    // Get outgoing friend requests with recipient details
    $outgoingRequests = $Database->query("
        SELECT 
            f.friendship_id,
            f.user_id_2 as recipient_id,
            f.created_at,
            f.status,
            u.username,
            u.email,
            u.profile_picture,
            u.first_name,
            u.last_name,
            u.bio
        FROM friendships f
        INNER JOIN users u ON u.user_id = f.user_id_2
        WHERE f.user_id_1 = ? AND f.status = 'pending'
        AND u.account_status = 'active'
        ORDER BY f.created_at DESC
        LIMIT ? OFFSET ?
    ", [$currentUserId, $limit, $offset]);
    
    // Format the response
    $formattedRequests = array_map(function($request) {
        return [
            'friendship_id' => (int)$request['friendship_id'],
            'recipient' => [
                'user_id' => (int)$request['recipient_id'],
                'username' => $request['username'],
                'email' => $request['email'],
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'profile_picture' => $request['profile_picture'],
                'bio' => $request['bio']
            ],
            'created_at' => $request['created_at'],
            'status' => $request['status']
        ];
    }, $outgoingRequests ?: []);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'requests' => $formattedRequests,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalRequests / $limit),
                'total_requests' => (int)$totalRequests,
                'per_page' => $limit
            ]
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Get outgoing requests error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
