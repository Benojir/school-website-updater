<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql = "ALTER TABLE `admission_fees_payment_history` CHANGE `partial_payment_ids_backup` `partial_payment_ids_backup` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;";
	
	$pdo->exec($sql);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>