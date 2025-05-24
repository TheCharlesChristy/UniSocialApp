<?php
/**
 * Delete Comment API Endpoint
 * 
 * Deletes a comment (only author or post owner can delete)
 * Endpoint: DELETE /api/posts/delete_comment
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Allow DELETE and POST requests
if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'])) {
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

// Get comment ID from input or URL
$commentId = null;
if (isset($input['comment_id'])) {
    $commentId = (int)$input['comment_id'];
} elseif (isset($_GET['comment_id'])) {
    $commentId = (int)$_GET['comment_id'];
} elseif (isset($_GET['id'])) {
    $commentId = (int)$_GET['id'];
}

if (!$commentId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Comment ID is required']);
    exit();
}

$userId = $authUser['user_id'];

// Check if comment exists and get post owner info
$checkSql = "
    SELECT 
        c.user_id as comment_user_id,
        c.post_id,
        p.user_id as post_user_id
    FROM comments c
    INNER JOIN posts p ON c.post_id = p.post_id
    WHERE c.comment_id = ?
";

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

$commentData = $comment[0];

// Check permissions (comment author, post owner, or admin)
$canDelete = ($commentData['comment_user_id'] == $userId) || 
             ($commentData['post_user_id'] == $userId) || 
             ($authUser['role'] === 'admin');

if (!$canDelete) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You can only delete your own comments or comments on your posts']);
    exit();
}

// Delete comment (cascade will handle replies and likes)
$deleteSql = "DELETE FROM comments WHERE comment_id = ?";
$result = $Database->execute($deleteSql, [$commentId]);

if ($result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete comment']);
    exit();
}

echo json_encode([
    'success' => true,
    'message' => 'Comment deleted successfully'
]);
