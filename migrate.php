<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql1 = "ALTER TABLE `student_payment_history` ADD `entry_by` INT NULL DEFAULT NULL AFTER `method`";
	$sql2 = "ALTER TABLE `users` ADD `security_pin` INT NOT NULL DEFAULT '123456' AFTER `email`";
	$sql3 = "ALTER TABLE `admin_auth_sessions` ADD `security_pin` INT NULL DEFAULT NULL AFTER `full_name`";
	$sql4 = "DELETE FROM admin_auth_sessions";
	
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