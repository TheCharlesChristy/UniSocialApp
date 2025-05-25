-- Table structure for privacy_settings table
CREATE TABLE privacy_settings (
    privacy_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    post_default_privacy VARCHAR(20) NOT NULL DEFAULT 'public',
    profile_visibility VARCHAR(20) NOT NULL DEFAULT 'public',
    friend_list_visibility VARCHAR(20) NOT NULL DEFAULT 'friends',
    who_can_send_requests VARCHAR(20) NOT NULL DEFAULT 'everyone',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_privacy_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT chk_post_default_privacy CHECK (post_default_privacy IN ('public', 'friends', 'private')),
    CONSTRAINT chk_profile_visibility CHECK (profile_visibility IN ('public', 'friends', 'private')),
    CONSTRAINT chk_friend_list_visibility CHECK (friend_list_visibility IN ('public', 'friends', 'private')),
    CONSTRAINT chk_who_can_send_requests CHECK (who_can_send_requests IN ('everyone', 'friends_of_friends', 'nobody'))
);

-- Create indexes for faster privacy lookups
CREATE INDEX idx_privacy_user ON privacy_settings(user_id);
