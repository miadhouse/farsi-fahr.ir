-- ایجاد دیتابیس
CREATE DATABASE IF NOT EXISTS `login_system` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `login_system`;

-- جدول کاربران
CREATE TABLE `users` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `google_id` VARCHAR(255) DEFAULT NULL,
    `email_verified` TINYINT(1) DEFAULT 0,
    `verification_token` VARCHAR(100) DEFAULT NULL,
    `reset_token` VARCHAR(100) DEFAULT NULL,
    `reset_expires` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_email` (`email`),
    KEY `idx_google_id` (`google_id`),
    KEY `idx_reset_token` (`reset_token`),
    KEY `idx_verification_token` (`verification_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول لاگ‌های کاربران
CREATE TABLE `user_logs` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `action` VARCHAR(50) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT,
    `status` ENUM('success', 'failed') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_action` (`action`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_user_logs_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول تلاش‌های ورود
CREATE TABLE `login_attempts` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_email_ip` (`email`, `ip_address`),
    KEY `idx_attempted_at` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول سشن‌ها
CREATE TABLE `sessions` (
    `id` VARCHAR(128) NOT NULL,
    `user_id` INT NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT,
    `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_last_activity` (`last_activity`),
    CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) 
        REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ایجاد کاربر ادمین پیش‌فرض
-- رمز عبور: Admin@123
INSERT INTO `users` (`email`, `password`, `name`, `role`, `email_verified`) 
VALUES (
    'admin@example.com', 
    '$2y$10$YourHashedPasswordHere', -- باید با hash_password('Admin@123') جایگزین شود
    'مدیر سیستم',
    'admin',
    1
);

-- نمایی برای گزارش‌گیری از آخرین ورودهای موفق
CREATE VIEW `recent_successful_logins` AS
SELECT 
    u.id,
    u.name,
    u.email,
    ul.ip_address,
    ul.created_at as login_time
FROM user_logs ul
JOIN users u ON ul.user_id = u.id
WHERE ul.action = 'login' 
    AND ul.status = 'success'
ORDER BY ul.created_at DESC;

-- تابع ذخیره شده برای پاکسازی لاگ‌های قدیمی
DELIMITER $$
CREATE PROCEDURE `cleanup_old_logs`()
BEGIN
    -- حذف لاگ‌های قدیمی‌تر از 90 روز
    DELETE FROM user_logs 
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
    
    -- حذف تلاش‌های ورود قدیمی‌تر از 30 روز
    DELETE FROM login_attempts 
    WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
    
    -- حذف سشن‌های منقضی شده
    DELETE FROM sessions 
    WHERE last_activity < DATE_SUB(NOW(), INTERVAL 7 DAY);
END$$
DELIMITER ;

-- Event برای اجرای خودکار پاکسازی (هر روز ساعت 2 صبح)
CREATE EVENT IF NOT EXISTS `auto_cleanup`
ON SCHEDULE EVERY 1 DAY
STARTS (TIMESTAMP(CURRENT_DATE) + INTERVAL 1 DAY + INTERVAL 2 HOUR)
DO CALL cleanup_old_logs();

-- فعال کردن Event Scheduler
SET GLOBAL event_scheduler = ON;