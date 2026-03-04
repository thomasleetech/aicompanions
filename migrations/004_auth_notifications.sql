-- Auth and notification columns for users table

ALTER TABLE users ADD COLUMN email_verified TINYINT DEFAULT 0;
ALTER TABLE users ADD COLUMN email_verify_token VARCHAR(100);
ALTER TABLE users ADD COLUMN password_reset_token VARCHAR(100);
ALTER TABLE users ADD COLUMN password_reset_expires DATETIME;
ALTER TABLE users ADD COLUMN notification_email TINYINT DEFAULT 1;
ALTER TABLE users ADD COLUMN notification_push TINYINT DEFAULT 1;
ALTER TABLE users ADD COLUMN notification_frequency VARCHAR(20) DEFAULT 'daily';

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(200),
    content TEXT,
    link VARCHAR(500),
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_read (user_id, is_read),
    INDEX idx_user_time (user_id, created_at)
);
