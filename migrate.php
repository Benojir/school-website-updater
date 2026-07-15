<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql = "ALTER TABLE sections DROP COLUMN bot_token;
ALTER TABLE `sections` CHANGE `chat_id` `telegram_chat_id` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
CREATE TABLE `student_homeworks` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `message_id` bigint UNSIGNED NOT NULL,
  `media_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message_text` text COLLATE utf8mb4_general_ci,
  `file_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `file_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'text',
  `file_name` text COLLATE utf8mb4_general_ci,
  `file_size` text COLLATE utf8mb4_general_ci,
  `file_width` int DEFAULT NULL,
  `file_height` int DEFAULT NULL,
  `thumb_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `thumb_width` int DEFAULT NULL,
  `thumb_height` int DEFAULT NULL,
  `status` enum('active','deleted') COLLATE utf8mb4_general_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci AUTO_INCREMENT=10;
CREATE TABLE `homework_section_map` (
  `homework_id` bigint UNSIGNED NOT NULL,
  `section_id` int NOT NULL,
  PRIMARY KEY (`homework_id`, `section_id`),
  KEY `fk_homework_section_map` (`section_id`),
  CONSTRAINT `fk_homework_section_map` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_homework_section_map_homework_id` FOREIGN KEY (`homework_id`) REFERENCES `student_homeworks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

	echo "<span class='text-info'>Executing $sql</span>";
	
	$pdo->exec($sql);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>