<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql = "ALTER TABLE teacher_auth_sessions ADD CONSTRAINT fk_teachers_auth FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE;
ALTER TABLE `teacher_auth_sessions` ADD `fcm_token` TEXT NULL DEFAULT NULL AFTER `device_name`;
ALTER TABLE `teacher_auth_sessions` ADD `app_version` VARCHAR(20) NULL DEFAULT NULL AFTER `ip_address`;
ALTER TABLE `teacher_auth_sessions` ADD `created_at` TIMESTAMP NOT NULL AFTER `app_version`;
ALTER TABLE `teacher_auth_sessions` CHANGE `updated_at` `last_activity` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `teacher_auth_sessions` DROP FOREIGN KEY `fk_teacher_accounts`;
DROP TABLE IF EXISTS `attendance`;
ALTER TABLE `mobile_notification_logs` CHANGE `id` `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;
RENAME TABLE mobile_notification_logs TO student_notifications_log;
ALTER TABLE `website_config` ADD `file_storage_channel_id` TEXT NULL DEFAULT NULL AFTER `telegram_bot_token`;
ALTER TABLE `student_homeworks` ADD `posted_by` VARCHAR(100) NULL DEFAULT NULL AFTER `status`;";

	echo "<span class='text-info'>Executing $sql</span><br>";
	
	$pdo->exec($sql);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>