<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql1 = "ALTER TABLE `teachers` ADD `position_in_school` VARCHAR(100) NULL DEFAULT NULL AFTER `phone`, ADD `village` VARCHAR(100) NULL DEFAULT NULL AFTER `position_in_school`, ADD `post_office` VARCHAR(100) NULL DEFAULT NULL AFTER `village`, ADD `police_station` VARCHAR(100) NULL DEFAULT NULL AFTER `post_office`, ADD `district` VARCHAR(100) NULL DEFAULT NULL AFTER `police_station`, ADD `pincode` VARCHAR(100) NULL DEFAULT NULL AFTER `district`";
	
	$pdo->exec($sql1);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>