<?php
/**
 * Create Notification API Endpoint
 * 
 * Creates a new notification
 * Endpoint: POST /api/notifications
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

// Check database connection
if (!$Database->isConnected()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection error']);
    exit();
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Validate required fields
$requiredFields = ['recipient_id', 'type', 'related_content_type', 'related_content_id'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Field '{$field}' is required"]);
        exit();
    }
}

$recipientId = (int)$input['recipient_id'];
$senderId = $authUser['user_id'];
$type = trim($input['type']);
$relatedContentType = trim($input['related_content_type']);
$relatedContentId = (int)$input['related_content_id'];

// Validate notification type
$validTypes = ['like', 'comment', 'friend_request', 'friend_accept', 'mention', 'tag'];
if (!in_array($type, $validTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid notification type. Valid types: ' . implode(', ', $validTypes)]);
    exit();
}

// Validate related content type
$validContentTypes = ['post', 'comment', 'user', 'message'];
if (!in_array($relatedContentType, $validContentTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid related content type. Valid types: ' . implode(', ', $validContentTypes)]);
    exit();
}

// Validate recipient exists and is not the sender
if ($recipientId === $senderId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cannot send notification to yourself']);
    exit();
}

try {
    $Database->beginTransaction();
    
    // Check if recipient user exists
    $recipient = $Database->query(
        "SELECT user_id FROM users WHERE user_id = ? AND account_status = 'active'",
        [$recipientId]
    );
    
    if (!$recipient || empty($recipient)) {
        $Database->rollBack();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Recipient user not found']);
        exit();
    }
    
    // Check for duplicate notification (prevent spam)
    $duplicateCheck = $Database->query(
        "SELECT notification_id FROM notifications 
         WHERE recipient_id = ? AND sender_id = ? AND type = ? 
         AND related_content_type = ? AND related_content_id = ? 
         AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
        [$recipientId, $senderId, $type, $relatedContentType, $relatedContentId]
    );
    
    if ($duplicateCheck && !empty($duplicateCheck)) {
        $Database->rollBack();
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Duplicate notification already exists']);
        exit();
    }
    
    // Create the notification
    $result = $Database->execute(
        "INSERT INTO notifications (recipient_id, sender_id, type, related_content_type, related_content_id, created_at, is_read) 
         VALUES (?, ?, ?, ?, ?, NOW(), FALSE)",
        [$recipientId, $senderId, $type, $relatedContentType, $relatedContentId]
    );    if ($result === false) {
        $Database->rollBack();
        throw new Exception('Failed to create notification');
    }
    
    // Get the created notification ID
    $notificationId = $Database->getLastInsertId();
    
    $Database->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Notification created successfully',
        'notification_id' => (int)$notificationId
    ]);

} catch (Exception $e) {
    $Database->rollBack();
    error_log("Create notification error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create notification']);
}
?>
