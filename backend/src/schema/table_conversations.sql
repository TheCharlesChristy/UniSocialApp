-- Table structure for conversations table
CREATE TABLE conversations (
    conversation_id INT AUTO_INCREMENT PRIMARY KEY,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    is_group_chat BOOLEAN NOT NULL,
    group_name VARCHAR(100) NULL,
    CONSTRAINT chk_group_chat CHECK (
        (is_group_chat = FALSE) OR 
        (is_group_chat = TRUE AND group_name IS NOT NULL)
    )
);

-- Create index for sorting conversations by recent activity
CREATE INDEX idx_updated_at ON conversations(updated_at);
