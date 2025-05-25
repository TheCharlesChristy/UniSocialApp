<?php
// Unblock User Endpoint - DELETE /api/users/:userId/block
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

// Get user ID from query parameter (as the test page sends it)
$unblockUserId = isset($_GET['userId']) ? (int)$_GET['userId'] : null;

if (!$unblockUserId || !is_numeric($unblockUserId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

$unblockUserId = (int)$unblockUserId;
$currentUserId = $authUser['user_id']; // From auth middleware

try {
    // Check if user is actually blocked
    $block = $Database->query("
        SELECT block_id FROM blocks 
        WHERE blocker_id = ? AND blocked_id = ?
    ", [$currentUserId, $unblockUserId]);
    
    if (!$block || empty($block)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User is not blocked']);
        exit;
    }
    
    // Unblock the user
    $Database->execute("
        DELETE FROM blocks 
        WHERE blocker_id = ? AND blocked_id = ?
    ", [$currentUserId, $unblockUserId]);
    
    echo json_encode([
        'success' => true,
        'message' => 'User unblocked successfully'
    ]);
    
} catch (Exception $e) {
    error_log("Unblock user error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
