-- AI Companions Database Schema
-- Run this to set up the database from scratch

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(100),
    avatar_url TEXT,
    bio TEXT,
    personal_facts TEXT,
    birthday DATE,
    location VARCHAR(100),
    interests TEXT,
    relationship_status VARCHAR(50),
    occupation VARCHAR(100),
    is_provider TINYINT DEFAULT 0,
    stripe_customer_id VARCHAR(100),
    balance DECIMAL(10,2) DEFAULT 0,
    referral_code VARCHAR(20) UNIQUE,
    referred_by INT,
    referral_earnings DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_referral (referral_code)
);

CREATE TABLE IF NOT EXISTS gigs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    companion_type ENUM('boyfriend', 'girlfriend', 'non-binary') NOT NULL,
    category VARCHAR(50) NOT NULL,
    price_per_hour DECIMAL(10,2) NOT NULL,
    price_per_message DECIMAL(10,2) DEFAULT NULL,
    monthly_price DECIMAL(10,2) DEFAULT NULL,
    languages VARCHAR(255) DEFAULT 'English',
    availability VARCHAR(100) DEFAULT 'Flexible',
    response_time VARCHAR(50) DEFAULT 'Within 1 hour',
    image_url TEXT,
    tags VARCHAR(500),
    ai_persona TEXT,
    ai_voice_id VARCHAR(100),
    voice_provider VARCHAR(50) DEFAULT 'openai',
    base_appearance TEXT,
    persona_traits TEXT,
    persona_background TEXT,
    persona_speaking_style TEXT,
    rating DECIMAL(3,2) DEFAULT 0,
    review_count INT DEFAULT 0,
    total_orders INT DEFAULT 0,
    is_featured TINYINT DEFAULT 0,
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_type (companion_type),
    INDEX idx_active (is_active),
    INDEX idx_featured (is_featured)
);

CREATE TABLE IF NOT EXISTS conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gig_id INT NOT NULL,
    title VARCHAR(200),
    last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_conv (user_id, gig_id),
    INDEX idx_user (user_id)
);

CREATE TABLE IF NOT EXISTS chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    role ENUM('user', 'assistant', 'system') NOT NULL,
    content TEXT NOT NULL,
    tokens_used INT DEFAULT 0,
    audio_url TEXT,
    emotion_detected VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_conv (conversation_id),
    INDEX idx_created (created_at)
);

CREATE TABLE IF NOT EXISTS subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gig_id INT NOT NULL,
    plan_type ENUM('hourly', 'monthly', 'yearly') NOT NULL,
    stripe_subscription_id VARCHAR(100),
    status ENUM('active', 'cancelled', 'expired', 'past_due') DEFAULT 'active',
    current_period_start TIMESTAMP NULL,
    current_period_end TIMESTAMP NULL,
    minutes_remaining INT DEFAULT 0,
    messages_remaining INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_sub (user_id, gig_id)
);

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gig_id INT,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    payment_type ENUM('one_time', 'subscription', 'top_up') NOT NULL,
    stripe_payment_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gig_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_gig (gig_id)
);

CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gig_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_fav (user_id, gig_id)
);

CREATE TABLE IF NOT EXISTS user_memories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gig_id INT NOT NULL,
    memory_type ENUM('fact', 'preference', 'emotion', 'relationship', 'goal') NOT NULL,
    memory_key VARCHAR(100) NOT NULL,
    memory_value TEXT NOT NULL,
    confidence DECIMAL(3,2) DEFAULT 0.80,
    last_referenced TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_memory (user_id, gig_id, memory_key)
);

CREATE TABLE IF NOT EXISTS user_companions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gig_id INT NOT NULL,
    nickname VARCHAR(100),
    notifications_enabled TINYINT DEFAULT 1,
    notification_frequency ENUM('often', 'daily', 'weekly', 'off') DEFAULT 'daily',
    affection_level INT DEFAULT 50,
    last_interaction TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_uc (user_id, gig_id)
);

CREATE TABLE IF NOT EXISTS time_purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gig_id INT NOT NULL,
    minutes_purchased INT NOT NULL,
    minutes_remaining INT NOT NULL,
    price_paid DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'expired', 'exhausted') DEFAULT 'active',
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    INDEX idx_user_gig (user_id, gig_id)
);

CREATE TABLE IF NOT EXISTS companion_upgrades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gig_id INT NOT NULL,
    upgrade_type VARCHAR(50) NOT NULL,
    price_paid DECIMAL(10,2),
    status ENUM('active', 'cancelled', 'expired') DEFAULT 'active',
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    UNIQUE KEY unique_upgrade (user_id, gig_id, upgrade_type)
);

CREATE TABLE IF NOT EXISTS gift_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    purchaser_id INT,
    recipient_email VARCHAR(255),
    recipient_name VARCHAR(100),
    sender_name VARCHAR(100),
    personal_message TEXT,
    redeemed_by INT,
    redeemed_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    status ENUM('active', 'redeemed', 'expired', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_code (code)
);

CREATE TABLE IF NOT EXISTS inbox_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gig_id INT NOT NULL,
    message_type ENUM('text', 'image', 'voice', 'love_letter') DEFAULT 'text',
    content TEXT NOT NULL,
    image_url TEXT,
    audio_url TEXT,
    is_read TINYINT DEFAULT 0,
    is_from_user TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_user_gig (user_id, gig_id)
);

CREATE TABLE IF NOT EXISTS api_usage_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    api_type VARCHAR(50) NOT NULL,
    tokens_input INT DEFAULT 0,
    tokens_output INT DEFAULT 0,
    cost_estimate DECIMAL(10,6) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_time (created_at),
    INDEX idx_type (api_type)
);

CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    gig_id INT,
    session_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    session_end TIMESTAMP NULL,
    duration_seconds INT DEFAULT 0,
    message_count INT DEFAULT 0,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id)
);

CREATE TABLE IF NOT EXISTS custom_companion_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    name VARCHAR(100),
    gender ENUM('girlfriend', 'boyfriend', 'non-binary'),
    age INT,
    personality TEXT,
    appearance TEXT,
    backstory TEXT,
    speaking_style TEXT,
    interests TEXT,
    special_requests TEXT,
    generated_gig_id INT,
    amount_paid DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL
);
