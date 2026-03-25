<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql1 = "ALTER TABLE `teachers` ADD `aadhaar_number` VARCHAR(50) NULL DEFAULT NULL AFTER `position_in_school`, ADD `monthly_salary` DECIMAL NOT NULL DEFAULT '0' AFTER `aadhaar_number`, ADD `assigned_sections` VARCHAR(255) NULL DEFAULT NULL AFTER `monthly_salary`";
	
	$pdo->exec($sql1);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>