<?php
require_once __DIR__ . '/../backend/src/db_handler/config.php';

// Create sample notifications for testing
try {
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Get some user IDs (assuming users exist)
    $stmt = $pdo->query("SELECT user_id FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($users) < 2) {
        echo "Not enough users in database. Please create at least 2 users first.\n";
        exit;
    }
    
    $recipient_id = $users[0];
    $sender_id = $users[1];
    
    // Sample notifications data
    $notifications = [
        [
            'recipient_id' => $recipient_id,
            'sender_id' => $sender_id,
            'type' => 'like',
            'related_content_type' => 'post',
            'related_content_id' => 1,
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
            'is_read' => false,
            'read_at' => null
        ],
        [
            'recipient_id' => $recipient_id,
            'sender_id' => $sender_id,
            'type' => 'comment',
            'related_content_type' => 'post',
            'related_content_id' => 1,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'is_read' => false,
            'read_at' => null
        ],
        [
            'recipient_id' => $recipient_id,
            'sender_id' => $sender_id,
            'type' => 'friend_request',
            'related_content_type' => 'user',
            'related_content_id' => $sender_id,
            'created_at' => date('Y-m-d H:i:s', strtotime('-30 minutes')),
            'is_read' => true,
            'read_at' => date('Y-m-d H:i:s', strtotime('-20 minutes'))
        ],
        [
            'recipient_id' => $recipient_id,
            'sender_id' => $sender_id,
            'type' => 'mention',
            'related_content_type' => 'comment',
            'related_content_id' => 2,
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
            'is_read' => false,
            'read_at' => null
        ]
    ];
    
    // Insert sample notifications
    $sql = "INSERT INTO notifications (recipient_id, sender_id, type, related_content_type, related_content_id, created_at, is_read, read_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    foreach ($notifications as $notification) {
        $stmt->execute([
            $notification['recipient_id'],
            $notification['sender_id'],
            $notification['type'],
            $notification['related_content_type'],
            $notification['related_content_id'],
            $notification['created_at'],
            $notification['is_read'],
            $notification['read_at']
        ]);
    }
    
    echo "Sample notifications created successfully!\n";
    echo "Recipient ID: $recipient_id\n";
    echo "Sender ID: $sender_id\n";
    echo "Created " . count($notifications) . " sample notifications.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
