-- Table structure for friendships table
CREATE TABLE friendships (
    friendship_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id_1 INT NOT NULL,
    user_id_2 INT NOT NULL,
    status VARCHAR(20) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    CONSTRAINT fk_user_id_1 FOREIGN KEY (user_id_1) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_user_id_2 FOREIGN KEY (user_id_2) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT chk_friendship_status CHECK (status IN ('pending', 'accepted')),
    CONSTRAINT chk_prevent_self_friendship CHECK (user_id_1 != user_id_2),
    CONSTRAINT uq_friendship UNIQUE (user_id_1, user_id_2)
);

-- Create indexes for faster friendship lookups
CREATE INDEX idx_user_id_1 ON friendships(user_id_1);
CREATE INDEX idx_user_id_2 ON friendships(user_id_2);
