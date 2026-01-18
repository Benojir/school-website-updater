<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql1 = "ALTER TABLE `admission_fees_payment_history` ADD `entry_by` INT NULL DEFAULT NULL AFTER `method`";
	$pdo->exec($sql1);
	
	echo "Database migration successfully done!<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>