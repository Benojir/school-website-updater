<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql = "ALTER TABLE students 
ADD COLUMN banglar_shiksha_code VARCHAR(255) NULL UNIQUE AFTER mother_occupation,
ADD COLUMN caste VARCHAR(50) NULL AFTER banglar_shiksha_code,
ADD COLUMN father_annual_income DECIMAL(10,2) NULL AFTER caste,
ADD COLUMN mother_annual_income DECIMAL(10,2) NULL AFTER father_annual_income,
ADD COLUMN bank_name VARCHAR(150) NULL AFTER mother_annual_income,
ADD COLUMN bank_account_no VARCHAR(100) NULL AFTER bank_name,
ADD COLUMN ifsc_code VARCHAR(50) NULL AFTER bank_account_no;
ALTER TABLE students
ADD COLUMN bank_branch_name VARCHAR(255) DEFAULT NULL AFTER bank_name;";

	echo "<span class='text-info'>Executing $sql</span><br>";
	
	$pdo->exec($sql);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>