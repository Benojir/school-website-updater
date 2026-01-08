<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
  
  //$pdo->exec("ALTER TABLE `results` ADD `section_id` INT NULL DEFAULT NULL AFTER `class_id`");
  
  //$pdo->exec("DROP TABLE IF EXISTS class_roll_numbers, graduation_logs, promotion_logs");
	
  //echo "Database migration successfully done!<br>";
  $file_path = "../api/admin/put/student/export-students-data.php";
  if (file_exists($file_path)){
	  if (unlink($file_path)) {
		  echo "File deleted";
	  } else {
		  echo "File not deleted";
	  }
  } else {
	  echo "File not available";
  }

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>