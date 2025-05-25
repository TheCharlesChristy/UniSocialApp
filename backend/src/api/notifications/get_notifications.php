<?php
/**
 * Get Notifications API Endpoint
 * 
 * Gets user's notifications with pagination and filtering
 * Endpoint: GET /api/notifications
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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

// Get query parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : 'all';

// Validate pagination parameters
if ($page < 1) {
    $page = 1;
}
if ($limit < 1 || $limit > 50) {
    $limit = 20;
}

// Validate filter parameter
if (!in_array($filter, ['all', 'unread'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid filter. Must be "all" or "unread"']);
    exit();
}

// Calculate offset
$offset = ($page - 1) * $limit;
$userId = $authUser['user_id'];

try {
    // Build WHERE clause based on filter
    $whereClause = "WHERE n.recipient_id = ?";
    $params = [$userId];
    
    if ($filter === 'unread') {
        $whereClause .= " AND n.is_read = FALSE";
    }
    
    // Get total count of notifications
    $countSql = "
        SELECT COUNT(*) as total
        FROM notifications n
        " . $whereClause;
    
    $countResult = $Database->query($countSql, $params);
    if ($countResult === false) {
        throw new Exception('Failed to get notifications count');
    }
    
    $totalNotifications = $countResult[0]['total'];
    $totalPages = ceil($totalNotifications / $limit);
    
    // Get unread count
    $unreadCountSql = "
        SELECT COUNT(*) as count
        FROM notifications
        WHERE recipient_id = ? AND is_read = FALSE";
    
    $unreadResult = $Database->query($unreadCountSql, [$userId]);
    if ($unreadResult === false) {
        throw new Exception('Failed to get unread count');
    }
    
    $unreadCount = $unreadResult[0]['count'];
    
    // Get notifications with sender details
    $notificationsSql = "
        SELECT 
            n.notification_id,
            n.sender_id,
            n.type,
            n.related_content_type,
            n.related_content_id,
            n.created_at,
            n.is_read,
            n.read_at,
            u.username as sender_username,
            u.first_name as sender_first_name,
            u.last_name as sender_last_name,
            u.profile_picture as sender_profile_picture
        FROM notifications n
        INNER JOIN users u ON n.sender_id = u.user_id
        " . $whereClause . "
        ORDER BY n.created_at DESC
        LIMIT ? OFFSET ?";
    
    $notificationsParams = array_merge($params, [$limit, $offset]);
    $notifications = $Database->query($notificationsSql, $notificationsParams);
    
    if ($notifications === false) {
        throw new Exception('Failed to retrieve notifications');
    }
    
    // Format notifications with additional context
    foreach ($notifications as &$notification) {
        // Add content preview based on type and related content
        $contentPreview = '';
        
        switch ($notification['type']) {
            case 'like':
                if ($notification['related_content_type'] === 'post') {
                    $contentPreview = 'liked your post';
                } elseif ($notification['related_content_type'] === 'comment') {
                    $contentPreview = 'liked your comment';
                }
                break;
            case 'comment':
                $contentPreview = 'commented on your post';
                break;
            case 'friend_request':
                $contentPreview = 'sent you a friend request';
                break;
            case 'friend_accept':
                $contentPreview = 'accepted your friend request';
                break;
            case 'mention':
                $contentPreview = 'mentioned you';
                break;
            case 'tag':
                $contentPreview = 'tagged you';
                break;
        }
        
        $notification['content_preview'] = $contentPreview;
        
        // Format sender name
        $notification['sender_name'] = trim($notification['sender_first_name'] . ' ' . $notification['sender_last_name']);
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'notifications' => $notifications ?: [],
        'total_notifications' => (int)$totalNotifications,
        'unread_count' => (int)$unreadCount,
        'current_page' => $page,
        'total_pages' => (int)$totalPages
    ]);

} catch (Exception $e) {
    error_log("Get notifications error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to retrieve notifications']);
}
?>
