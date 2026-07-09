<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql = "ALTER TABLE `website_config` ADD `auto_request_monthly_fees_day` VARCHAR(5) NULL DEFAULT NULL AFTER `timezone`;";
	
	$pdo->exec($sql);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>