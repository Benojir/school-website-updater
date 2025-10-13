<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "<h3>Starting Database Migration...</h3><br>";

try {
    $query1 = "ALTER TABLE `students` ADD `academic_year` VARCHAR(20) NULL DEFAULT NULL AFTER `promoted_date`";
    $pdo->exec($query1);
    echo "Database migration successfully done!<br>";
} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
}

?>