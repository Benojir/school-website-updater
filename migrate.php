<?php

include_once("includes/db.php");

echo "<h3>Starting Database Migration...</h3><br>";

$query = "DROP TABLE old_admission_fees_table_ta_jetar_name_vule_gechhi";
    
$pdo->exec($query);
?>