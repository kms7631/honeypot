-- attack_log 테이블
CREATE TABLE IF NOT EXISTS attack_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    attempt_id VARCHAR(50) NOT NULL,
    attempt_pw_raw VARCHAR(255) NOT NULL,
    attempt_pw_masked VARCHAR(255) NOT NULL,
    access_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_banned TINYINT(1) NOT NULL DEFAULT 0,
    INDEX idx_ip_time (ip_address, access_time)
);

-- ip_bans 테이블
CREATE TABLE IF NOT EXISTS ip_bans (
    ban_id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL UNIQUE,
    ban_reason VARCHAR(255) NOT NULL DEFAULT 'Honeypot Access',
    ban_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
