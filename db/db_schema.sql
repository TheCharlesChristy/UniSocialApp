-- Users Table: Stores user information
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for each user
    username VARCHAR(50) NOT NULL UNIQUE, -- Username, must be unique
    password_hash VARCHAR(255) NOT NULL, -- Hashed password for security
    email VARCHAR(100) NOT NULL UNIQUE, -- User's email, must be unique
    is_admin BOOLEAN DEFAULT FALSE, -- Flag to identify administrators
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Timestamp of account creation
);

-- Posts Table: Stores user posts
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for each post
    user_id INT NOT NULL, -- Reference to the user who created the post
    content TEXT, -- Text content of the post
    image_url VARCHAR(255), -- URL of the image in the post
    location VARCHAR(255), -- Location data using Google Maps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp of post creation
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE -- Foreign key constraint
);

-- Comments Table: Stores comments on posts
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for each comment
    post_id INT NOT NULL, -- Reference to the post being commented on
    user_id INT NOT NULL, -- Reference to the user who made the comment
    content TEXT NOT NULL, -- Text content of the comment
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp of comment creation
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE, -- Foreign key constraint
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE -- Foreign key constraint
);

-- Likes Table: Tracks likes on posts
CREATE TABLE likes (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for each like
    post_id INT NOT NULL, -- Reference to the post being liked
    user_id INT NOT NULL, -- Reference to the user who liked the post
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp of like
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE, -- Foreign key constraint
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, -- Foreign key constraint
    UNIQUE(post_id, user_id) -- Ensure a user can like a post only once
);

-- Messages Table: Handles messaging between users
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for each message
    sender_id INT NOT NULL, -- Reference to the sender
    receiver_id INT NOT NULL, -- Reference to the receiver
    content TEXT NOT NULL, -- Content of the message
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp when the message was sent
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE, -- Foreign key constraint
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE -- Foreign key constraint
);

-- Blocks Table: Manages user blocks
CREATE TABLE blocks (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for each block
    blocker_id INT NOT NULL, -- Reference to the user who is blocking
    blocked_id INT NOT NULL, -- Reference to the user being blocked
    blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp when the block occurred
    FOREIGN KEY (blocker_id) REFERENCES users(id) ON DELETE CASCADE, -- Foreign key constraint
    FOREIGN KEY (blocked_id) REFERENCES users(id) ON DELETE CASCADE, -- Foreign key constraint
    UNIQUE(blocker_id, blocked_id) -- Ensure a user can block another user only once
);

-- Reports Table: Allows users to report others
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for each report
    reporter_id INT NOT NULL, -- Reference to the user who made the report
    reported_id INT NOT NULL, -- Reference to the user being reported
    reason VARCHAR(255) NOT NULL, -- Reason for the report
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Timestamp of the report
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE, -- Foreign key constraint
    FOREIGN KEY (reported_id) REFERENCES users(id) ON DELETE CASCADE -- Foreign key constraint
);