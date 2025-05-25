<?php
/**
 * Create Report API Endpoint
 * 
 * Creates a new report for post, comment, or user
 * Endpoint: POST /api/reports
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
$reportedId = isset($input['reported_id']) ? (int)$input['reported_id'] : null;
$contentType = isset($input['content_type']) ? trim($input['content_type']) : '';
$contentId = isset($input['content_id']) ? (int)$input['content_id'] : null;
$reason = isset($input['reason']) ? trim($input['reason']) : '';
$description = isset($input['description']) ? trim($input['description']) : null;

// Validate required fields
if (!$reportedId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'reported_id is required and must be a valid integer']);
    exit();
}

if (!in_array($contentType, ['user', 'post', 'comment'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'content_type must be one of: user, post, comment']);
    exit();
}

if (!$contentId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'content_id is required and must be a valid integer']);
    exit();
}

if (empty($reason)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'reason is required and cannot be empty']);
    exit();
}

if (strlen($reason) > 100) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'reason cannot exceed 100 characters']);
    exit();
}

$reporterId = $authUser['user_id'];

// Prevent users from reporting themselves
if ($reporterId == $reportedId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You cannot report yourself']);
    exit();
}

try {
    $Database->beginTransaction();
    
    // Verify the reported user exists
    $reportedUser = $Database->query(
        "SELECT user_id FROM users WHERE user_id = ? AND account_status = 'active'",
        [$reportedId]
    );
    
    if (!$reportedUser || empty($reportedUser)) {
        $Database->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Reported user not found or inactive']);
        exit();
    }
    
    // Verify the content exists based on content type
    switch ($contentType) {
        case 'user':
            // For user reports, content_id should match reported_id
            if ($contentId != $reportedId) {
                $Database->rollBack();
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'For user reports, content_id must match reported_id']);
                exit();
            }
            break;
            
        case 'post':
            $post = $Database->query(
                "SELECT post_id, user_id FROM posts WHERE post_id = ?",
                [$contentId]
            );
            
            if (!$post || empty($post)) {
                $Database->rollBack();
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Post not found']);
                exit();
            }
            
            // Verify the post belongs to the reported user
            if ($post[0]['user_id'] != $reportedId) {
                $Database->rollBack();
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Post does not belong to the reported user']);
                exit();
            }
            break;
            
        case 'comment':
            $comment = $Database->query(
                "SELECT comment_id, user_id FROM comments WHERE comment_id = ?",
                [$contentId]
            );
            
            if (!$comment || empty($comment)) {
                $Database->rollBack();
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Comment not found']);
                exit();
            }
            
            // Verify the comment belongs to the reported user
            if ($comment[0]['user_id'] != $reportedId) {
                $Database->rollBack();
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Comment does not belong to the reported user']);
                exit();
            }
            break;
    }
      // Check if user has already reported this specific content for the same reason
    $existingReport = $Database->query(
        "SELECT report_id FROM reports 
         WHERE reporter_id = ? AND reported_id = ? AND content_type = ? AND content_id = ? AND reason = ?",
        [$reporterId, $reportedId, $contentType, $contentId, $reason]
    );
    
    if ($existingReport && !empty($existingReport)) {
        $Database->rollBack();
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'You have already reported this content for this reason']);
        exit();
    }
    
    // Create the report
    $result = $Database->execute(
        "INSERT INTO reports (reporter_id, reported_id, content_type, content_id, reason, description, created_at, status) 
         VALUES (?, ?, ?, ?, ?, ?, NOW(), 'pending')",
        [$reporterId, $reportedId, $contentType, $contentId, $reason, $description]
    );
    
    if ($result === false) {
        $Database->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create report']);
        exit();
    }
    
    // Get the ID of the created report
    $reportId = $Database->query("SELECT LAST_INSERT_ID() as report_id")[0]['report_id'];
    
    $Database->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'report_id' => (int)$reportId,
        'message' => 'Report created successfully'
    ]);
    
} catch (Exception $e) {
    $Database->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while creating the report']);
}
