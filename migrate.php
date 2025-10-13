<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "<h3>Starting Database Migration...</h3><br>";

$query1 = "ALTER TABLE `students` ADD `academic_year` VARCHAR(20) NULL DEFAULT NULL AFTER `promoted_date`";
// $query2 = "ALTER TABLE admission_enquiries ADD preferred_by VARCHAR(100) NULL DEFAULT NULL AFTER class_id";
// $query3 = "ALTER TABLE admission_enquiries ADD religion VARCHAR(30) NULL DEFAULT NULL AFTER blood_group";
    
if ($pdo->exec($query1)) {
	echo "Database migration successfully done!<br>";
} else {
	echo "Database migration failed!<br>";
}
// $pdo->exec($query2);
// $pdo->exec($query3);

?>