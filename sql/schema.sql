-- attack_log 테이블
CREATE TABLE IF NOT EXISTS attack_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    attempt_id VARCHAR(50) NOT NULL,
    attempt_pw_raw VARCHAR(255) NOT NULL,
    attempt_pw_masked VARCHAR(255) NOT NULL,
    request_path VARCHAR(255) NULL,
    user_agent TEXT NULL,
    method VARCHAR(10) NULL,
    access_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_banned TINYINT(1) NOT NULL DEFAULT 0,
    INDEX idx_ip_time (ip_address, access_time)
);

-- 기존 테이블에도 컬럼을 "추가"로 반영(이미 있으면 스킵)
SET @col := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'attack_log'
      AND COLUMN_NAME = 'request_path'
);
SET @sql := IF(@col = 0, 'ALTER TABLE attack_log ADD COLUMN request_path VARCHAR(255) NULL', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'attack_log'
      AND COLUMN_NAME = 'user_agent'
);
SET @sql := IF(@col = 0, 'ALTER TABLE attack_log ADD COLUMN user_agent TEXT NULL', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'attack_log'
      AND COLUMN_NAME = 'method'
);
SET @sql := IF(@col = 0, 'ALTER TABLE attack_log ADD COLUMN method VARCHAR(10) NULL', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ip_bans 테이블
CREATE TABLE IF NOT EXISTS ip_bans (
    ban_id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL UNIQUE,
    ban_reason VARCHAR(255) NOT NULL DEFAULT 'Honeypot Access',
    ban_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
