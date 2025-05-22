-- Table structure for token blacklist table
CREATE TABLE token_blacklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token_id VARCHAR(255) NOT NULL UNIQUE,
    expiration DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token_id (token_id),
    INDEX idx_expiration (expiration)
);
