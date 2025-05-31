-- Create database if not exists
CREATE DATABASE IF NOT EXISTS auth_api_system;
USE auth_api_system;

-- Developer accounts table
CREATE TABLE IF NOT EXISTS dev_accounts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_email_verified BOOLEAN DEFAULT FALSE
);

-- API clients table (for developer systems)
CREATE TABLE IF NOT EXISTS api_clients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dev_id INT NOT NULL,
    system_name VARCHAR(255) NOT NULL,
    api_key VARCHAR(255) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (dev_id) REFERENCES dev_accounts(id)
);

-- Email OTPs table
CREATE TABLE IF NOT EXISTS email_otps (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dev_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    otp VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dev_id) REFERENCES dev_accounts(id) ON DELETE CASCADE,
    INDEX idx_email_otp (email, dev_id)
);

-- API users table (end users of client systems)
CREATE TABLE IF NOT EXISTS api_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    dev_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    is_email_verified BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (dev_id) REFERENCES dev_accounts(id),
    UNIQUE KEY unique_email_per_dev (email, dev_id)
);

-- Authentication tokens table
CREATE TABLE IF NOT EXISTS auth_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES api_users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_expiry (user_id, expires_at)
);

-- Password reset OTPs for API users
CREATE TABLE IF NOT EXISTS password_reset_otps (
    id INT PRIMARY KEY AUTO_INCREMENT,
    api_user_id INT NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (api_user_id) REFERENCES api_users(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_dev_email ON dev_accounts(email);
CREATE INDEX idx_api_key ON api_clients(api_key);
CREATE INDEX idx_api_user_email ON api_users(email); 