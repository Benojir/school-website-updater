<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql1 = "ALTER TABLE `drivers` ADD `serial_number` VARCHAR(20) NULL DEFAULT NULL AFTER `phone`";
	$sql2 = "ALTER TABLE `drivers` CHANGE `vehicle_number` `vehicle_number` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL";
	$sql3 = "ALTER TABLE `drivers` CHANGE `route` `route` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL";
	$sql4 = "ALTER TABLE `drivers` ADD `village` VARCHAR(100) NULL DEFAULT NULL AFTER `driver_image`, ADD `post_office` VARCHAR(100) NULL DEFAULT NULL AFTER `village`, ADD `police_station` VARCHAR(100) NULL DEFAULT NULL AFTER `post_office`, ADD `district` VARCHAR(100) NULL DEFAULT NULL AFTER `police_station`, ADD `pincode` VARCHAR(100) NULL DEFAULT NULL AFTER `district`";
  
	$pdo->exec($sql1);
	$pdo->exec($sql2);
	$pdo->exec($sql3);
	$pdo->exec($sql4);
	
	echo "Database migration successfully done!<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>