-- Image generation tracking for rate limiting and cost analysis

CREATE TABLE IF NOT EXISTS image_generation_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gig_id INT,
    is_nsfw TINYINT DEFAULT 0,
    image_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_time (user_id, created_at),
    INDEX idx_gig (gig_id)
);

-- Chat time tracking (for time-based billing)
CREATE TABLE IF NOT EXISTS chat_time_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gig_id INT NOT NULL,
    time_purchase_id INT,
    seconds_used INT DEFAULT 0,
    message_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_gig (user_id, gig_id)
);
