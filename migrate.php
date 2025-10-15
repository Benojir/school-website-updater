<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Database Migration...<br>";

try {
    $pdo->exec("ALTER TABLE `students` ADD `village` VARCHAR(50) NULL DEFAULT NULL AFTER `address`, ADD `post_office` VARCHAR(50) NULL DEFAULT NULL AFTER `village`, ADD `police_station` VARCHAR(50) NULL DEFAULT NULL AFTER `post_office`, ADD `district` VARCHAR(50) NULL DEFAULT NULL AFTER `police_station`, ADD `pin_code` VARCHAR(15) NULL DEFAULT NULL AFTER `district`, ADD `student_adhaar_no` VARCHAR(20) NULL DEFAULT NULL AFTER `pin_code`, ADD `father_adhaar_no` VARCHAR(20) NULL DEFAULT NULL AFTER `student_adhaar_no`, ADD `mother_adhaar_no` VARCHAR(20) NULL DEFAULT NULL AFTER `father_adhaar_no`");

    echo "Database migration successfully done!<br>";
} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
}

?>