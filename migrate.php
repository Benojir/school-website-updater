<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql = "ALTER TABLE `students` ADD `landmark` VARCHAR(255) NULL DEFAULT NULL AFTER `address`;";

	echo "<span class='text-info'>Executing:<br>$sql</span><br>";
	
	$pdo->exec($sql);
	
	echo "Database migration successfully done! 😅<br>";
	// Delete the .sql file now
	if (unlink($file_path)) {
		echo "SQL file deleted successfully.<br>";
	}

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>