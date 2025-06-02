<?php
/**
 * Check if current user has liked a specific post
 * 
 * Endpoint: GET/POST /api/posts/has_liked.php
 * Authentication: Required (Bearer token)
 * 
 * GET Request:
 * /api/posts/has_liked.php?post_id=123
 * 
 * POST Request Body:
 * {
 *   "post_id": 123
 * }
 * 
 * Response:
 * {
 *   "success": true,
 *   "has_liked": true,
 *   "like_id": 456,
 *   "liked_at": "2024-01-15T10:30:00Z"
 * }
 */

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET and POST requests
if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

// Include required files
require_once '../../db_handler/connection.php';
require_once '../auth/auth_middleware.php';

try {
    // Authenticate user using the middleware
    $currentUser = authorizeRequest(true);

    // Get and validate input based on request method
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // GET request - get post_id from query parameters
        $postId = isset($_GET['post_id']) ? $_GET['post_id'] : null;
    } else {
        // POST request - get post_id from JSON body
        $input = json_decode(file_get_contents('php://input'), true);
        $postId = isset($input['post_id']) ? $input['post_id'] : null;
    }
    
    // Validate post_id parameter
    if (!$postId || !is_numeric($postId)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Valid post_id is required'
        ]);
        exit();
    }

    $postId = (int)$postId;
    $userId = $currentUser['user_id'];

    // Check if database connection is available
    if (!$Database->isConnected()) {
        throw new Exception('Database connection failed');
    }

    // First verify that the post exists and the user has permission to view it
    $postQuery = "
        SELECT p.post_id, p.user_id, p.privacy_level, u.account_status
        FROM posts p
        JOIN users u ON p.user_id = u.user_id
        WHERE p.post_id = ? AND u.account_status = 'active'
    ";
    
    $postResult = $Database->query($postQuery, [$postId]);
    
    if (!$postResult || empty($postResult)) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Post not found'
        ]);
        exit();
    }

    $post = $postResult[0];

    // Check if user has permission to view this post based on privacy settings
    $canViewPost = false;
    
    if ($post['privacy_level'] === 'public') {
        $canViewPost = true;
    } elseif ($post['user_id'] == $userId) {
        // User can always view their own posts
        $canViewPost = true;
    } elseif ($post['privacy_level'] === 'friends') {
        // Check if users are friends
        $friendshipQuery = "
            SELECT friendship_id 
            FROM friendships 
            WHERE ((user_id_1 = ? AND user_id_2 = ?) OR (user_id_1 = ? AND user_id_2 = ?))
            AND status = 'accepted'
        ";
        
        $friendshipResult = $Database->query($friendshipQuery, [
            $userId, $post['user_id'], $post['user_id'], $userId
        ]);
        
        if ($friendshipResult && !empty($friendshipResult)) {
            $canViewPost = true;
        }
    }

    if (!$canViewPost) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Access denied'
        ]);
        exit();
    }

    // Check if user has liked this post
    $likeQuery = "
        SELECT like_id, created_at
        FROM likes
        WHERE post_id = ? AND user_id = ? AND comment_id IS NULL
    ";
    
    $likeResult = $Database->query($likeQuery, [$postId, $userId]);
    
    if ($likeResult && !empty($likeResult)) {
        // User has liked the post
        $like = $likeResult[0];
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'has_liked' => true,
            'like_id' => (int)$like['like_id'],
            'liked_at' => $like['created_at'],
            'post_id' => $postId
        ]);
    } else {
        // User has not liked the post
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'has_liked' => false,
            'like_id' => null,
            'liked_at' => null,
            'post_id' => $postId
        ]);
    }

} catch (Exception $e) {
    // Log error for debugging
    error_log("Has liked check error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to check like status'
    ]);
}
?>