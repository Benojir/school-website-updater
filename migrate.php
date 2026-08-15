<?php

//include_once("includes/db.php"); No need to include this as the update.php already included
$file_path = __DIR__ . "/aaa.sql"; // Path to the database configuration file

// Check if the file exists
if (!file_exists($file_path)) {
    die("Database configuration file not found!");
}

$sql = file_get_contents($file_path);

echo "Starting Migration Script...<br>";

try {
	// $sql = "";

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