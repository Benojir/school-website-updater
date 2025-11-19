<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Database Migration...<br>";

try {
    $pdo->exec("ALTER TABLE `website_config` ADD `student_id_prefix` VARCHAR(10) NULL DEFAULT 'STU' AFTER `country_code`");
	
	$pdo->exec("ALTER TABLE `school_information` ADD `meta_description` TEXT NULL DEFAULT NULL AFTER `description`, ADD `meta_keywords` TEXT NULL DEFAULT NULL AFTER `meta_description`");

    echo "Database migration successfully done!<br>";
} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
}

?>