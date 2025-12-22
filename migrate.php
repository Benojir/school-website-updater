<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
  
  $pdo->exec("ALTER TABLE `results` ADD `total_minor_marks` DECIMAL NULL DEFAULT '0' AFTER `remarks`, ADD `obtained_minor_marks` DECIMAL NULL DEFAULT '0' AFTER `total_minor_marks`, ADD `percentage_without_minor` DECIMAL NULL DEFAULT '0' AFTER `obtained_minor_marks`, ADD `grade_without_minor` VARCHAR(5) NULL DEFAULT NULL AFTER `percentage_without_minor`, ADD `remarks_without_minor` VARCHAR(100) NULL DEFAULT NULL AFTER `grade_without_minor`");
  
  $pdo->exec("ALTER TABLE `results` ADD `section_id` INT NULL DEFAULT NULL AFTER `class_id`");
  
  $pdo->exec("ALTER TABLE `settings_marksheet` ADD `section_based_ranking` TINYINT NOT NULL DEFAULT '0' AFTER `show_text_watermark`");
	
  echo "Database migration successfully done!<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>