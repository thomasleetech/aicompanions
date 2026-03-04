-- Endless Memory feature: conversation summaries cache
CREATE TABLE IF NOT EXISTS conversation_summaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL UNIQUE,
    summary TEXT NOT NULL,
    message_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_conv (conversation_id)
);
