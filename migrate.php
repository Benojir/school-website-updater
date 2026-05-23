<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql1 = "DELETE s FROM subjects s LEFT JOIN classes c ON s.class_id = c.id WHERE c.id IS NULL";
	$sql2 = "ALTER TABLE subjects ADD CONSTRAINT fk_subjects_delete FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE";
	
	$pdo->exec($sql1);
	$pdo->exec($sql2);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>