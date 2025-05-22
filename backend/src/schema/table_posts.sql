-- Table structure for posts table
CREATE TABLE posts (
    post_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    caption TEXT NULL,
    post_type VARCHAR(20) NOT NULL,
    media_url VARCHAR(255) NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    privacy_level VARCHAR(20) NOT NULL,
    location_lat DECIMAL(10,8) NULL,
    location_lng DECIMAL(10,8) NULL,
    location_name VARCHAR(255) NULL,
    CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT chk_post_type CHECK (post_type IN ('text', 'photo', 'video')),
    CONSTRAINT chk_privacy_level CHECK (privacy_level IN ('public', 'friends', 'private')),
    CONSTRAINT chk_media_url CHECK (
        (post_type = 'text') OR 
        (post_type IN ('photo', 'video') AND media_url IS NOT NULL)
    )
);

-- Create indexes for faster retrieval and sorting
CREATE INDEX idx_user_id ON posts(user_id);
CREATE INDEX idx_created_at ON posts(created_at);
