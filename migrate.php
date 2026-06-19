<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql1 = "ALTER TABLE `exam_routines` CHANGE `theory_exam_date` `theory_exam_date` DATE NULL DEFAULT NULL;
ALTER TABLE `exam_routines` CHANGE `theory_start_time` `theory_start_time` TIME NULL DEFAULT NULL;
ALTER TABLE `exam_routines` CHANGE `theory_end_time` `theory_end_time` TIME NULL DEFAULT NULL;
ALTER TABLE `parent_auth_sessions` CHANGE `created_at` `created_at` TIMESTAMP NOT NULL;
CREATE TABLE `driver_auth_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `phone` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `token_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `device_id` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `device_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `fcm_token` text COLLATE utf8mb4_general_ci NOT NULL,
  `session_source` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL,
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
ALTER TABLE `driver_auth_sessions` ADD `driver_id` INT NOT NULL AFTER `id`;
ALTER TABLE driver_auth_sessions ADD CONSTRAINT fk_drivers FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE;";
	
	$pdo->exec($sql1);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>