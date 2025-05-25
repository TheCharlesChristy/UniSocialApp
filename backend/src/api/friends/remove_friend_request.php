<?php
// Remove Friend Request Endpoint - DELETE /api/friends/request/:userId
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
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
$targetUserId = end($pathParts);

if (!$targetUserId || !is_numeric($targetUserId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

$targetUserId = (int)$targetUserId;
$currentUserId = $authUser['user_id']; // From auth middleware

if ($targetUserId === $currentUserId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cannot remove friend request to yourself']);
    exit;
}

try {
    // Check if there's a pending friend request from current user to target user
    $request = $Database->query("
        SELECT friendship_id FROM friendships 
        WHERE user_id_1 = ? AND user_id_2 = ? AND status = 'pending'
    ", [$currentUserId, $targetUserId]);
    
    if (!$request || empty($request)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No pending friend request found to remove']);
        exit;
    }
    
    // Remove the friend request
    $result = $Database->execute("
        DELETE FROM friendships 
        WHERE user_id_1 = ? AND user_id_2 = ? AND status = 'pending'
    ", [$currentUserId, $targetUserId]);
    
    if ($result === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to remove friend request']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Friend request removed successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Remove friend request error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
