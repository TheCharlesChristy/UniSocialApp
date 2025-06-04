<?php
/**
 * Live Users SSE Endpoint
 * 
 * Server-Sent Events for real-time user management updates
 * Endpoint: GET /api/admin/live_users
 */

// Set headers for SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Cache-Control');

// Get auth token from query parameter (EventSource doesn't support custom headers)
$authToken = $_GET['token'] ?? null;

if (!$authToken) {
    echo "event: error\n";
    echo "data: " . json_encode(['error' => 'Authentication token required']) . "\n\n";
    exit();
}

// Validate token and get user
try {
    // Get database connection
    $Database = require_once dirname(dirname(dirname(__FILE__))) . '/db_handler/connection.php';
    require_once dirname(dirname(__FILE__)) . '/auth/auth_utils.php';
      // Validate token
    $tokenData = AuthUtils::validateToken($authToken);
    if ($tokenData === false) {
        throw new Exception('Invalid token');
    }
    
    // Check if token is blacklisted
    if (AuthUtils::isTokenBlacklisted($authToken, $Database)) {
        throw new Exception('Token has been invalidated');
    }
    
    // Get user details
    $user = $Database->query(
        "SELECT user_id, role FROM users WHERE user_id = ? AND account_status = 'active'",
        [$tokenData['user_id']]
    );
    
    if (!$user || $user[0]['role'] !== 'admin') {
        throw new Exception('Admin access required');
    }
    
    $adminUserId = $user[0]['user_id'];
    
} catch (Exception $e) {
    echo "event: error\n";
    echo "data: " . json_encode(['error' => 'Authentication failed: ' . $e->getMessage()]) . "\n\n";
    exit();
}

// Send initial connection confirmation
echo "event: connected\n";
echo "data: " . json_encode([
    'message' => 'Connected to live users stream',
    'admin_id' => $adminUserId,
    'timestamp' => date('Y-m-d H:i:s')
]) . "\n\n";
flush();

// Keep track of last check time
$lastCheck = time();

// Main SSE loop
while (true) {
    // Check if client is still connected
    if (connection_aborted()) {
        break;
    }
    
    try {
        $currentTime = time();
        
        // Send user statistics update every 30 seconds
        if ($currentTime - $lastCheck >= 30) {
            // Get current user counts
            $totalUsers = $Database->query("SELECT COUNT(*) as count FROM users WHERE account_status != 'deleted'");
            $activeUsers = $Database->query("SELECT COUNT(*) as count FROM users WHERE account_status = 'active'");
            $suspendedUsers = $Database->query("SELECT COUNT(*) as count FROM users WHERE account_status = 'suspended'");
            
            // Get reported users count (users with reports against them)
            $reportedUsers = $Database->query("
                SELECT COUNT(DISTINCT reported_id) as count 
                FROM reports 
                WHERE content_type = 'user' AND status = 'pending'
            ");
            
            $stats = [
                'total_users' => $totalUsers ? (int)$totalUsers[0]['count'] : 0,
                'active_users' => $activeUsers ? (int)$activeUsers[0]['count'] : 0,
                'suspended_users' => $suspendedUsers ? (int)$suspendedUsers[0]['count'] : 0,
                'reported_users' => $reportedUsers ? (int)$reportedUsers[0]['count'] : 0,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            echo "event: user_stats_update\n";
            echo "data: " . json_encode($stats) . "\n\n";
            flush();
            
            $lastCheck = $currentTime;
        }
        
        // Check for recent user status changes
        $recentChanges = $Database->query("
            SELECT user_id, username, account_status, 
                   UNIX_TIMESTAMP(updated_at) as updated_timestamp
            FROM users 
            WHERE updated_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
              AND account_status != 'deleted'
            ORDER BY updated_at DESC
        ");
        
        if ($recentChanges) {
            foreach ($recentChanges as $change) {
                // Only send if the change is very recent (within last 30 seconds)
                if ($currentTime - $change['updated_timestamp'] <= 30) {
                    echo "event: user_status_change\n";
                    echo "data: " . json_encode([
                        'user_id' => $change['user_id'],
                        'username' => $change['username'],
                        'new_status' => $change['account_status'],
                        'timestamp' => date('Y-m-d H:i:s', $change['updated_timestamp'])
                    ]) . "\n\n";
                    flush();
                }
            }
        }
        
        // Send heartbeat every 15 seconds
        if ($currentTime % 15 == 0) {
            echo "event: heartbeat\n";
            echo "data: " . json_encode(['timestamp' => date('Y-m-d H:i:s')]) . "\n\n";
            flush();
        }
        
    } catch (Exception $e) {
        echo "event: error\n";
        echo "data: " . json_encode(['error' => 'Server error: ' . $e->getMessage()]) . "\n\n";
        flush();
        break;
    }
    
    // Sleep for 1 second before next iteration
    sleep(1);
}

// Connection ended
echo "event: disconnected\n";
echo "data: " . json_encode(['message' => 'Connection closed']) . "\n\n";
flush();
?>
