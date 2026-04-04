<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql1 = "ALTER TABLE `settings_marksheet` ADD `hide_top_rankers` TINYINT(1) NOT NULL DEFAULT '0' AFTER `section_based_ranking`, ADD `hide_position_in_class` TINYINT(1) NOT NULL DEFAULT '0' AFTER `hide_top_rankers`";
	
	$pdo->exec($sql1);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>