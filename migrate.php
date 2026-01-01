<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
  
  //$pdo->exec("ALTER TABLE `results` ADD `section_id` INT NULL DEFAULT NULL AFTER `class_id`");
  
  $pdo->exec("CREATE TABLE `student_id_card_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `template` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `school_address` text COLLATE utf8mb4_general_ci,
  `school_contacts` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `colors` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;");
	
  echo "Database migration successfully done!<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>