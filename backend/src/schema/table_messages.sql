-- Table structure for messages table
CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    is_read BOOLEAN NOT NULL,
    read_at DATETIME NULL,
    CONSTRAINT fk_message_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(conversation_id) ON DELETE CASCADE,
    CONSTRAINT fk_message_sender FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT chk_read_timestamp CHECK (
        (is_read = FALSE AND read_at IS NULL) OR 
        (is_read = TRUE AND read_at IS NOT NULL)
    )
);

-- Create indexes for faster message retrieval
CREATE INDEX idx_message_conversation ON messages(conversation_id, created_at);
CREATE INDEX idx_sender_messages ON messages(sender_id);
