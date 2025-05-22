-- Table structure for notifications table
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    recipient_id INT NOT NULL,
    sender_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    related_content_type VARCHAR(20) NOT NULL,
    related_content_id INT NOT NULL,
    created_at DATETIME NOT NULL,
    is_read BOOLEAN NOT NULL,
    read_at DATETIME NULL,
    CONSTRAINT fk_notification_recipient FOREIGN KEY (recipient_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_notification_sender FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT chk_notification_type CHECK (type IN ('like', 'comment', 'friend_request', 'friend_accept', 'mention', 'tag')),
    CONSTRAINT chk_related_content_type CHECK (related_content_type IN ('post', 'comment', 'user', 'message')),
    CONSTRAINT chk_notification_read CHECK (
        (is_read = FALSE AND read_at IS NULL) OR 
        (is_read = TRUE AND read_at IS NOT NULL)
    )
);

-- Create indexes for faster notification retrieval
CREATE INDEX idx_recipient_unread ON notifications(recipient_id, is_read);
CREATE INDEX idx_notification_created ON notifications(created_at);
