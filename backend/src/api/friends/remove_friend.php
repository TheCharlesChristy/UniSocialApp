<?php
// Remove Friend Endpoint - DELETE /api/friends/:userId
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
$friendUserId = end($pathParts);

if (!$friendUserId || !is_numeric($friendUserId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

$friendUserId = (int)$friendUserId;
$currentUserId = $authUser['user_id']; // From auth middleware

try {
    $Database->beginTransaction();
    
    // Check if they are actually friends
    $friendship = $Database->query("
        SELECT friendship_id FROM friendships 
        WHERE ((user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?)) 
        AND status = 'accepted'
    ", [$currentUserId, $friendUserId, $friendUserId, $currentUserId]);
    
    if (!$friendship || empty($friendship)) {
        $Database->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Friendship not found']);
        exit;
    }
    
    // Remove the friendship record
    $result = $Database->execute("
        DELETE FROM friendships 
        WHERE ((user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?))
        AND status = 'accepted'
    ", [$currentUserId, $friendUserId, $friendUserId, $currentUserId]);
    
    $Database->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Friend removed successfully'
    ]);
    
} catch (Exception $e) {
    $Database->rollBack();
    error_log("Remove friend error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
?>
