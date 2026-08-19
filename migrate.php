<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql_file = "migrate.sql";

	if (file_exists($sql_file)) {

		$sql = file_get_contents($sql_file);

		echo "<span class='text-info'>Executing:<br>$sql</span><br>";

		$pdo->exec($sql);

		unlink($sql_file);

		echo "<span class='text-success'>Database migration successfully done! 😅</span><br>";
	} else {
		echo "<span class='text-danger'>.sql file does not exist. Failed migration.</span><br>";
	}
} catch (PDOException $e) {
	echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}
