<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql = "ALTER TABLE `students` CHANGE `car_fee` `car_fee` DECIMAL(10,2) NOT NULL DEFAULT '0';
ALTER TABLE `students` CHANGE `hostel_fee` `hostel_fee` DECIMAL(10,2) NOT NULL DEFAULT '0';
ALTER TABLE `students` CHANGE `custom_class_fee` `custom_class_fee` DECIMAL(10,2) NOT NULL DEFAULT '0';
ALTER TABLE `students` ADD `coaching_fee` DECIMAL(10,2) NULL DEFAULT '0' AFTER `hostel_fee`, ADD `tiffin_fee` DECIMAL(10,2) NULL DEFAULT '0' AFTER `coaching_fee`;";
	
	$pdo->exec($sql);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>