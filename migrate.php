<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

// echo "<h3>Starting Database Migration...</h3><br>";

// $query1 = "DROP TABLE student_admission_fees";
// $query2 = "ALTER TABLE admission_enquiries ADD preferred_by VARCHAR(100) NULL DEFAULT NULL AFTER class_id";
// $query3 = "ALTER TABLE admission_enquiries ADD religion VARCHAR(30) NULL DEFAULT NULL AFTER blood_group";
    
// $pdo->exec($query1);
// $pdo->exec($query2);
// $pdo->exec($query3);


echo "Deleting old receipt download file<br>";

$file_path = "../parent/ajax/download-paid-receipt.php";

if (file_exists($file_path)) {
    unlink($file_path);
    echo "Deleted old receipt download file successfully<br>";
} else {
    echo "Old receipt download file does not exist<br>";
}

?>