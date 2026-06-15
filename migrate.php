<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql1 = "ALTER TABLE `school_information` ADD `alternate_phone` VARCHAR(20) NULL DEFAULT NULL AFTER `phone`;
ALTER TABLE `school_information` ADD `village` TEXT NULL DEFAULT NULL AFTER `address`, ADD `post_office` TEXT NULL DEFAULT NULL AFTER `village`, ADD `police_station` TEXT NULL DEFAULT NULL AFTER `post_office`, ADD `district` TEXT NULL DEFAULT NULL AFTER `police_station`, ADD `pincode` VARCHAR(20) NULL DEFAULT NULL AFTER `district`;
ALTER TABLE `school_information` ADD `google_map_embed_link` TEXT NULL DEFAULT NULL AFTER `google_map_link`;
ALTER TABLE `school_information` ADD `campus_name` TEXT NULL DEFAULT NULL AFTER `pincode`;";
	
	$pdo->exec($sql1);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>