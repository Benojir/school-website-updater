<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Database Migration...<br>";

try {
    $pdo->exec("RENAME TABLE `parent_mobile_sessions` TO `parent_auth_sessions`");
	
	$pdo->exec("ALTER TABLE `parent_auth_sessions` ADD COLUMN `session_source` VARCHAR(20) DEFAULT 'mobile' AFTER `fcm_token`");
	
	$pdo->exec("CREATE TABLE `mobile_notification_logs` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `student_ids` TEXT DEFAULT NULL,
  `notification_title` TEXT DEFAULT NULL,
  `notification_body` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    echo "Database migration successfully done!<br>";
} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
}

?>