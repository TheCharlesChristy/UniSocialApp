-- Table structure for users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    profile_picture VARCHAR(255) NULL,
    bio TEXT NULL,
    date_of_birth DATE NOT NULL,
    registration_date DATETIME NOT NULL,
    last_login DATETIME NULL,
    account_status VARCHAR(20) NOT NULL,
    role VARCHAR(20) NOT NULL,
    CONSTRAINT chk_account_status CHECK (account_status IN ('active', 'suspended', 'deleted')),
    CONSTRAINT chk_role CHECK (role IN ('user', 'admin'))
);

-- Create indexes for fast authentication lookups
CREATE INDEX idx_username ON users(username);
CREATE INDEX idx_email ON users(email);
