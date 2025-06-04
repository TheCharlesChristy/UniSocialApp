<?php
/**
 * Live Notifications using Server-Sent Events (SSE)
 * Provides real-time notification updates to clients
 */

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Prevent timeout
set_time_limit(0);
ini_set('max_execution_time', 0);

require_once __DIR__ . '/../../db_handler/connection.php';

// Simple authentication for SSE - using session or token parameter
session_start();

// Try to get user from session or token parameter
$user = null;
$user_id = null;

// Method 1: Check if user is logged in via session
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user = ['user_id' => $user_id];
}

// Method 2: Check for token in URL parameter (fallback)
if (!$user && isset($_GET['token'])) {
    $db_temp = DatabaseHandler::getInstance();
    $token = $_GET['token'];
    
    // Validate token and get user
    $query = "SELECT s.user_id, u.role FROM sessions s 
              JOIN users u ON s.user_id = u.user_id 
              WHERE s.session_token = ? AND s.expires_at > NOW()";
    $result = $db_temp->query($query, [$token]);
    
    if ($result && count($result) > 0) {
        $user_id = $result[0]['user_id'];
        $user = [
            'user_id' => $user_id,
            'role' => $result[0]['role']
        ];
    }
}

// Method 3: For testing - allow admin user (you can remove this later)
if (!$user) {
    // For testing purposes, assume user_id = 1 (admin)
    // Remove this in production!
    $user_id = 1;
    $user = ['user_id' => $user_id];
}

if (!$user) {
    echo "event: error\n";
    echo "data: " . json_encode(['message' => 'Unauthorized']) . "\n\n";
    exit;
}

$db = DatabaseHandler::getInstance();

// Keep track of last notification ID to avoid duplicates
$last_notification_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

// Function to send SSE message
function sendSSEMessage($event, $data) {
    echo "event: $event\n";
    echo "data: " . json_encode($data) . "\n\n";
    
    // Flush output buffer to send immediately
    if (ob_get_level()) {
        ob_end_flush();
    }
    flush();
}

// Send initial connection confirmation
sendSSEMessage('connected', ['message' => 'Connected to live notifications']);

// Main loop for checking notifications
while (true) {
    try {        // Check for new notifications
        $query = "SELECT 
                    n.*,
                    u.username as sender_username,
                    u.first_name as sender_first_name,
                    u.last_name as sender_last_name,
                    u.profile_picture as sender_profile_picture
                  FROM notifications n
                  LEFT JOIN users u ON n.sender_id = u.user_id
                  WHERE n.recipient_id = ? 
                  AND n.notification_id > ?
                  ORDER BY n.created_at DESC";
        
        $notifications = $db->query($query, [$user_id, $last_notification_id]);
        
        if (!empty($notifications)) {
            foreach ($notifications as $notification) {
                // Send each new notification
                sendSSEMessage('new_notification', [
                    'notification_id' => $notification['notification_id'],
                    'type' => $notification['type'],
                    'title' => ucfirst($notification['type']) . ' Notification',
                    'message' => 'You have a new ' . $notification['type'] . ' notification',
                    'sender' => [
                        'username' => $notification['sender_username'],
                        'first_name' => $notification['sender_first_name'],
                        'last_name' => $notification['sender_last_name'],
                        'profile_picture' => $notification['sender_profile_picture']
                    ],
                    'related_content_type' => $notification['related_content_type'],
                    'related_content_id' => $notification['related_content_id'],
                    'created_at' => $notification['created_at'],
                    'is_read' => $notification['is_read']
                ]);
                
                // Update last notification ID
                $last_notification_id = max($last_notification_id, $notification['notification_id']);
            }
              // Send updated count
            $count_query = "SELECT COUNT(*) as count FROM notifications WHERE recipient_id = ? AND is_read = 0";
            $count_result = $db->query($count_query, [$user_id]);
            $unread_count = ($count_result && count($count_result) > 0) ? $count_result[0]['count'] : 0;
            
            sendSSEMessage('count_update', ['count' => $unread_count]);
        }
        
        // Check if client is still connected
        if (connection_aborted()) {
            break;
        }
        
        // Wait 2 seconds before checking again
        sleep(2);
        
    } catch (Exception $e) {
        sendSSEMessage('error', ['message' => 'Server error: ' . $e->getMessage()]);
        break;    }
}
?>
