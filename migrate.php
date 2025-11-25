<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Database Migration...<br>";

try {
    $pdo->exec("ALTER TABLE users
  DROP remember_token,
  DROP token_expiry");
	
    echo "Database migration successfully done!<br>";
} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
}

?>