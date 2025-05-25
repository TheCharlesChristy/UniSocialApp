<?php
// Reject Friend Request Endpoint - PUT /api/friends/reject/:userId
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$requireAuth = true;
$Database = require_once '../../db_handler/connection.php';
require_once '../auth/auth_middleware.php';

// Get user ID from URL path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', $path);
$friendUserId = end($pathParts);

if (!$friendUserId || !is_numeric($friendUserId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

$friendUserId = (int)$friendUserId;
$currentUserId = $authUser['user_id']; // From auth middleware

try {
    // Check if there's a pending friend request from the friend to current user
    $request = $Database->query("
        SELECT friendship_id FROM friendships 
        WHERE user_id_1 = ? AND user_id_2 = ? AND status = 'pending'
    ", [$friendUserId, $currentUserId]);    
    if (!$request || empty($request)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No pending friend request found']);
        exit;
    }
    
    // Delete the friend request
    $result = $Database->execute("
        DELETE FROM friendships 
        WHERE user_id_1 = ? AND user_id_2 = ? AND status = 'pending'
    ", [$friendUserId, $currentUserId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Friend request rejected successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Reject friend request error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
