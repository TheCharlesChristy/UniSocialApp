<?php
/**
 * Live Reports using Server-Sent Events (SSE)
 * Provides real-time report updates to admin clients
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

require_once dirname(dirname(dirname(__FILE__))) . '/db_handler/connection.php';

// Simple authentication for SSE
$user = null;
$authUser = null;

// Method 1: Try to get token from Authorization header
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
    $token = $matches[1];
    
    // Validate token and get user
    $db_temp = DatabaseHandler::getInstance();
    $query = "SELECT s.user_id, u.role FROM sessions s 
              JOIN users u ON s.user_id = u.user_id 
              WHERE s.session_token = ? AND s.expires_at > NOW()";
    $result = $db_temp->query($query, [$token]);
    
    if ($result && count($result) > 0) {
        $authUser = [
            'user_id' => $result[0]['user_id'],
            'role' => $result[0]['role']
        ];
    }
}

// Method 2: Fallback to query parameter
if (!$authUser && isset($_GET['token'])) {
    $token = $_GET['token'];
    $db_temp = DatabaseHandler::getInstance();
    
    $query = "SELECT s.user_id, u.role FROM sessions s 
              JOIN users u ON s.user_id = u.user_id 
              WHERE s.session_token = ? AND s.expires_at > NOW()";
    $result = $db_temp->query($query, [$token]);
    
    if ($result && count($result) > 0) {
        $authUser = [
            'user_id' => $result[0]['user_id'],
            'role' => $result[0]['role']
        ];
    }
}

// Method 3: Session fallback
if (!$authUser) {
    session_start();
    if (isset($_SESSION['user_id'])) {
        $db_temp = DatabaseHandler::getInstance();
        $query = "SELECT role FROM users WHERE user_id = ?";
        $result = $db_temp->query($query, [$_SESSION['user_id']]);
        
        if ($result && count($result) > 0) {
            $authUser = [
                'user_id' => $_SESSION['user_id'],
                'role' => $result[0]['role']
            ];
        }
    }
}

// Check if user is admin
if (!isset($authUser) || $authUser['role'] !== 'admin') {
    echo "event: error\n";
    echo "data: " . json_encode(['message' => 'Admin access required']) . "\n\n";
    exit;
}

$db = DatabaseHandler::getInstance();
$admin_user_id = $authUser['user_id'];

// Keep track of last report ID to avoid duplicates
$last_report_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

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
sendSSEMessage('connected', ['message' => 'Connected to live reports']);

// Main loop for checking reports
while (true) {
    try {
        // Check for new reports
        $query = "SELECT 
                    r.report_id,
                    r.reporter_id,
                    r.reported_id,
                    r.content_type,
                    r.content_id,
                    r.reason,
                    r.description,
                    r.created_at,
                    r.status,
                    r.admin_notes,
                    r.reviewed_by,
                    r.reviewed_at,
                    reporter.username as reporter_username,
                    reporter.first_name as reporter_first_name,
                    reporter.last_name as reporter_last_name,
                    reported.username as reported_username,
                    reported.first_name as reported_first_name,
                    reported.last_name as reported_last_name
                  FROM reports r
                  INNER JOIN users reporter ON r.reporter_id = reporter.user_id
                  INNER JOIN users reported ON r.reported_id = reported.user_id
                  WHERE r.report_id > ?
                  ORDER BY r.created_at DESC";
        
        $reports = $db->query($query, [$last_report_id]);
        
        if (!empty($reports)) {
            foreach ($reports as $report) {
                // Send each new report
                sendSSEMessage('new_report', [
                    'report_id' => $report['report_id'],
                    'reporter_id' => $report['reporter_id'],
                    'reported_id' => $report['reported_id'],
                    'content_type' => $report['content_type'],
                    'content_id' => $report['content_id'],
                    'reason' => $report['reason'],
                    'description' => $report['description'],
                    'created_at' => $report['created_at'],
                    'status' => $report['status'],
                    'admin_notes' => $report['admin_notes'],
                    'reviewed_by' => $report['reviewed_by'],
                    'reviewed_at' => $report['reviewed_at'],
                    'reporter' => [
                        'username' => $report['reporter_username'],
                        'first_name' => $report['reporter_first_name'],
                        'last_name' => $report['reporter_last_name']
                    ],
                    'reported' => [
                        'username' => $report['reported_username'],
                        'first_name' => $report['reported_first_name'],
                        'last_name' => $report['reported_last_name']
                    ]
                ]);
                
                // Update last report ID
                $last_report_id = max($last_report_id, $report['report_id']);
            }
            
            // Send updated pending reports count
            $count_query = "SELECT COUNT(*) as count FROM reports WHERE status = 'pending'";
            $count_result = $db->query($count_query);
            $pending_count = ($count_result && count($count_result) > 0) ? $count_result[0]['count'] : 0;
            
            sendSSEMessage('pending_count_update', ['count' => $pending_count]);
            
            // Send updated dashboard statistics
            $stats_query = "SELECT 
                              (SELECT COUNT(*) FROM users WHERE account_status = 'active') as total_users,
                              (SELECT COUNT(*) FROM posts) as total_posts,
                              (SELECT COUNT(*) FROM reports WHERE status = 'pending') as pending_reports,
                              (SELECT COUNT(*) FROM users WHERE registration_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as new_users_this_week,
                              (SELECT COUNT(*) FROM posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as new_posts_this_week";
            
            $stats_result = $db->query($stats_query);
            if ($stats_result && count($stats_result) > 0) {
                sendSSEMessage('dashboard_stats_update', $stats_result[0]);
            }
        }
        
        // Check for report status updates
        $updated_reports_query = "SELECT 
                                    r.report_id,
                                    r.status,
                                    r.admin_notes,
                                    r.reviewed_by,
                                    r.reviewed_at,
                                    reviewer.username as reviewer_username
                                  FROM reports r
                                  LEFT JOIN users reviewer ON r.reviewed_by = reviewer.user_id
                                  WHERE r.reviewed_at >= DATE_SUB(NOW(), INTERVAL 10 SECOND)
                                  ORDER BY r.reviewed_at DESC";
        
        $updated_reports = $db->query($updated_reports_query);
        
        if (!empty($updated_reports)) {
            foreach ($updated_reports as $updated_report) {
                sendSSEMessage('report_status_update', [
                    'report_id' => $updated_report['report_id'],
                    'status' => $updated_report['status'],
                    'admin_notes' => $updated_report['admin_notes'],
                    'reviewed_by' => $updated_report['reviewed_by'],
                    'reviewed_at' => $updated_report['reviewed_at'],
                    'reviewer_username' => $updated_report['reviewer_username']
                ]);
            }
        }
        
        // Check if client is still connected
        if (connection_aborted()) {
            break;
        }
        
        // Wait 3 seconds before checking again
        sleep(3);
        
    } catch (Exception $e) {
        sendSSEMessage('error', ['message' => 'Server error: ' . $e->getMessage()]);
        break;
    }
}
?>
