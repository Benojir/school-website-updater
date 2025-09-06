<?php

include_once("includes/db.php");

// Create the 'drivers' table
$sql = "ALTER TABLE `students` ADD `driver_id` INT NULL DEFAULT NULL AFTER `car_route`";
$sql2 = "ALTER TABLE `students` ADD `religion` VARCHAR(30) NULL DEFAULT NULL AFTER `email`, ADD `registration_no` VARCHAR(50) NULL DEFAULT NULL AFTER `religion`";

// Execute the query
if ($pdo->query($sql) === TRUE && $pdo->query($sql2) === TRUE) {
    echo "Columns created successfully.";
} else {
    echo "Error creating table";
}
?>
