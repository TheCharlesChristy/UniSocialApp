-- Table structure for blocks table
CREATE TABLE blocks (
    block_id INT AUTO_INCREMENT PRIMARY KEY,
    blocker_id INT NOT NULL,
    blocked_id INT NOT NULL,
    created_at DATETIME NOT NULL,
    reason TEXT NULL,
    CONSTRAINT fk_blocker_id FOREIGN KEY (blocker_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_blocked_id FOREIGN KEY (blocked_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT chk_prevent_self_block CHECK (blocker_id != blocked_id),
    CONSTRAINT uq_block UNIQUE (blocker_id, blocked_id)
);

-- Create indexes for faster block status checks
CREATE INDEX idx_blocker_id ON blocks(blocker_id);
CREATE INDEX idx_blocked_id ON blocks(blocked_id);
