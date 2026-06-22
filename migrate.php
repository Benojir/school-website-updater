<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql = "ALTER TABLE `website_config`
  DROP `admission_open`,
  DROP `teacher_application`,
  DROP `total_student_show`,
  DROP `admin_login_option_show`,
  DROP `allow_online_payment`,
  DROP `show_teachers_on_front_page`,
  DROP `id_card_style`;
  ALTER TABLE `website_config` DROP `country_code`;
  ALTER TABLE `website_config` ADD `fbase_service_account_key` TEXT NULL DEFAULT NULL AFTER `firebase_config`;";
	
	$pdo->exec($sql);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>