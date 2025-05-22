-- Table structure for likes table
CREATE TABLE likes (
    like_id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NULL,
    comment_id INT NULL,
    user_id INT NOT NULL,
    created_at DATETIME NOT NULL,
    CONSTRAINT fk_post_like FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE,
    CONSTRAINT fk_comment_like FOREIGN KEY (comment_id) REFERENCES comments(comment_id) ON DELETE CASCADE,
    CONSTRAINT fk_user_like FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT chk_like_target CHECK (
        (post_id IS NOT NULL AND comment_id IS NULL) OR 
        (post_id IS NULL AND comment_id IS NOT NULL)
    ),
    CONSTRAINT uq_user_post_like UNIQUE (user_id, post_id),
    CONSTRAINT uq_user_comment_like UNIQUE (user_id, comment_id)
);

-- Create indexes for counting likes
CREATE INDEX idx_post_likes ON likes(post_id);
CREATE INDEX idx_comment_likes ON likes(comment_id);
