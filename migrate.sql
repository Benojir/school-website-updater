CREATE TABLE IF NOT EXISTS `school_settings` (
    `id` int NOT NULL AUTO_INCREMENT,
    `auto_request_monthly_fees_day` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '28',
    `settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;