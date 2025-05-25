<?php
// Block User Endpoint - POST /api/users/:userId/block
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$requireAuth = true;
$Database = require_once '../../db_handler/connection.php';
require_once '../auth/auth_middleware.php';

// Get user ID from query parameter (as the test page sends it)
$blockUserId = isset($_GET['userId']) ? (int)$_GET['userId'] : null;

if (!$blockUserId || !is_numeric($blockUserId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

$blockUserId = (int)$blockUserId;
$currentUserId = $authUser['user_id']; // From auth middleware

if ($blockUserId === $currentUserId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cannot block yourself']);
    exit;
}

try {
    $Database->beginTransaction();
    
    // Check if user exists
    $user = $Database->query("SELECT user_id FROM users WHERE user_id = ?", [$blockUserId]);
    if (!$user || empty($user)) {
        $Database->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }
    
    // Check if already blocked
    $existing = $Database->query("
        SELECT block_id FROM blocks 
        WHERE blocker_id = ? AND blocked_id = ?
    ", [$currentUserId, $blockUserId]);
    if ($existing && !empty($existing)) {
        $Database->rollBack();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User is already blocked']);
        exit;
    }
    
    // Remove any existing friendship
    $Database->execute("
        DELETE FROM friendships 
        WHERE ((user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?))
    ", [$currentUserId, $blockUserId, $blockUserId, $currentUserId]);
    
    // Block the user
    $Database->execute("
        INSERT INTO blocks (blocker_id, blocked_id, created_at)
        VALUES (?, ?, NOW())
    ", [$currentUserId, $blockUserId]);
    
    $Database->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'User blocked successfully'
    ]);
    
} catch (Exception $e) {
    $Database->rollBack();
    error_log("Block user error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
