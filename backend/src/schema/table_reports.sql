-- Table structure for reports table
CREATE TABLE reports (
    report_id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT NOT NULL,
    reported_id INT NOT NULL,
    content_type VARCHAR(20) NOT NULL,
    content_id INT NOT NULL,
    reason VARCHAR(100) NOT NULL,
    description TEXT NULL,
    created_at DATETIME NOT NULL,
    status VARCHAR(20) NOT NULL,
    admin_notes TEXT NULL,
    reviewed_by INT NULL,
    reviewed_at DATETIME NULL,
    CONSTRAINT fk_reporter_id FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_reported_id FOREIGN KEY (reported_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_reviewed_by FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL,
    CONSTRAINT chk_content_type CHECK (content_type IN ('user', 'post', 'comment')),
    CONSTRAINT chk_report_status CHECK (status IN ('pending', 'reviewed', 'action_taken', 'dismissed'))
);

-- Create indexes for faster queries
CREATE INDEX idx_reporter_id ON reports(reporter_id);
CREATE INDEX idx_reported_id ON reports(reported_id);
CREATE INDEX idx_report_status ON reports(status);
CREATE INDEX idx_content ON reports(content_type, content_id);
