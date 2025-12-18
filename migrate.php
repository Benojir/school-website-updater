<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
  
  //$pdo->exec("ALTER TABLE admin_auth_sessions CHANGE device_name device_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL");
  
  //$pdo->exec("ALTER TABLE admin_auth_sessions CHANGE device_id device_id VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL");
  
  //$pdo->exec("ALTER TABLE exam_routines ADD UNIQUE KEY unique_subject_routine (exam_id, class_id, subject_id)");
	
    //echo "Database migration successfully done!<br>";
	
	$delete_file_path = "../management/settings/school-information.php";
	
	if(file_exists($delete_file_path)){
		if (!unlink($delete_file_path)) {
			echo "File delete failed!<br>";
		} else {
			echo "File delete success!<br>File path $delete_file_path <br>";
		}
	}
} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>