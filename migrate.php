<?php

include_once("includes/db.php");

// Create the 'is_absent' and 'is_excluded' columns in 'subject_marks' table
$sql = "ALTER TABLE `subject_marks` 
        ADD `is_absent` TINYINT NOT NULL DEFAULT '0' AFTER `remarks`, 
        ADD `is_excluded` TINYINT NOT NULL DEFAULT '0' AFTER `is_absent`";

// Execute the query
if ($pdo->query($sql)) {
    echo "Columns created successfully.";
} else {
    $error = $pdo->errorInfo();
    echo "Error creating columns: " . $error[2];
}

?>
