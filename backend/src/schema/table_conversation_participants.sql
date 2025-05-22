-- Table structure for conversation_participants table
CREATE TABLE conversation_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at DATETIME NOT NULL,
    left_at DATETIME NULL,
    CONSTRAINT fk_conversation_id FOREIGN KEY (conversation_id) REFERENCES conversations(conversation_id) ON DELETE CASCADE,
    CONSTRAINT fk_participant_user_id FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT uq_active_participant UNIQUE (conversation_id, user_id, left_at)
);

-- Create indexes for faster queries
CREATE INDEX idx_conversation_participants ON conversation_participants(conversation_id);
CREATE INDEX idx_user_conversations ON conversation_participants(user_id);
