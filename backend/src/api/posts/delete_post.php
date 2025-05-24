<?php
/**
 * Delete Post API Endpoint
 * 
 * Deletes a post (only owner or admin can delete)
 * Endpoint: DELETE /api/posts/delete_post
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

// Get post ID from input or URL
$postId = null;
if (isset($input['post_id'])) {
    $postId = (int)$input['post_id'];
} elseif (isset($_GET['post_id'])) {
    $postId = (int)$_GET['post_id'];
} elseif (isset($_GET['id'])) {
    $postId = (int)$_GET['id'];
}

if (!$postId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Post ID is required']);
    exit();
}

// Check if post exists and get details
$checkSql = "SELECT user_id, media_url FROM posts WHERE post_id = ?";
$post = $Database->query($checkSql, [$postId]);

if ($post === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

if (empty($post)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit();
}

$postData = $post[0];

// Check permissions (owner or admin)
if ($postData['user_id'] != $authUser['user_id'] && $authUser['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'You can only delete your own posts']);
    exit();
}

// Begin transaction
$Database->beginTransaction();

try {
    // Delete the post (cascade will handle likes and comments)
    $deleteSql = "DELETE FROM posts WHERE post_id = ?";
    $result = $Database->execute($deleteSql, [$postId]);
    
    if ($result === false) {
        throw new Exception('Failed to delete post from database');
    }
    
    // Remove associated media file if exists
    if (!empty($postData['media_url'])) {
        $mediaPath = dirname(dirname(dirname(dirname(__FILE__)))) . '/' . $postData['media_url'];
        if (file_exists($mediaPath)) {
            unlink($mediaPath);
        }
    }
    
    // Commit transaction
    $Database->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Post deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    $Database->rollBack();
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to delete post']);
}
