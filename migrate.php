<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql1 = "ALTER TABLE `website_config` ADD `show_teachers_on_front_page` TINYINT(1) NOT NULL DEFAULT '0' AFTER `allow_online_payment`";
	$sql2 = "ALTER TABLE `drivers` DROP `driver_id`";
	$sql3 = "ALTER TABLE `teachers` DROP `teacher_id`";
	$sql4 = "ALTER TABLE `drivers` ADD `aadhaar_number` VARCHAR(100) NULL DEFAULT NULL AFTER `route_id`";
	
	$pdo->exec($sql1);
	$pdo->exec($sql2);
	$pdo->exec($sql3);
	$pdo->exec($sql4);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>