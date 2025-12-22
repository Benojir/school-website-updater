<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
  
  //$pdo->exec("ALTER TABLE `results` ADD `section_id` INT NULL DEFAULT NULL AFTER `class_id`");
  
  $pdo->exec("ALTER TABLE results
MODIFY COLUMN total_minor_marks DECIMAL(10, 2) DEFAULT 0,
MODIFY COLUMN obtained_minor_marks DECIMAL(10, 2) DEFAULT 0,
MODIFY COLUMN percentage_without_minor DECIMAL(10, 2) DEFAULT 0");
	
  echo "Database migration successfully done!<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>