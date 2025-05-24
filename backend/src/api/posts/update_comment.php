<?php
/**
 * Update Comment API Endpoint
 * 
 * Updates a comment (only author can update)
 * Endpoint: PUT /api/posts/update_comment
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Allow PUT and POST requests
if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'])) {
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

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Validate required fields
$commentId = isset($input['comment_id']) ? (int)$input['comment_id'] : null;
$content = isset($input['content']) ? trim($input['content']) : '';

if (!$commentId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment ID is required']);
    exit();
}

if (empty($content)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment content is required']);
    exit();
}

// Validate content length (max 1000 characters)
if (strlen($content) > 1000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment content too long. Maximum 1000 characters allowed']);
    exit();
}

$userId = $authUser['user_id'];

// Check if comment exists and user owns it
$checkSql = "SELECT user_id, post_id FROM comments WHERE comment_id = ?";
$comment = $Database->query($checkSql, [$commentId]);

if ($comment === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

if (empty($comment)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Comment not found']);
    exit();
}

if ($comment[0]['user_id'] != $userId) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You can only update your own comments']);
    exit();
}

// Update comment
$updateSql = "UPDATE comments SET content = ?, updated_at = ? WHERE comment_id = ?";
$result = $Database->execute($updateSql, [$content, date('Y-m-d H:i:s'), $commentId]);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to update comment']);
    exit();
}

echo json_encode([
    'success' => true,
    'message' => 'Comment updated successfully'
]);
