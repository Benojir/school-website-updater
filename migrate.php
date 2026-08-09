<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql = "ALTER TABLE teachers
    ADD COLUMN date_of_birth DATE NULL AFTER application_date,
    ADD COLUMN gender ENUM('Male','Female','Other') NULL AFTER date_of_birth,
    ADD COLUMN blood_group VARCHAR(5) NULL AFTER gender,
    ADD COLUMN caste_category ENUM('General','OBC-A','OBC-B','SC','ST','Other') NULL AFTER blood_group,
    ADD COLUMN religion VARCHAR(50) NULL AFTER caste_category,
    ADD COLUMN nationality VARCHAR(50) NULL DEFAULT 'Indian' AFTER religion,
    ADD COLUMN father_name VARCHAR(100) NULL AFTER nationality,
    ADD COLUMN father_phone VARCHAR(15) NULL AFTER father_name,
    ADD COLUMN mother_name VARCHAR(100) NULL AFTER father_phone,
    ADD COLUMN mother_phone VARCHAR(15) NULL AFTER mother_name,
    ADD COLUMN pan_number VARCHAR(10) NULL AFTER mother_phone,
    ADD COLUMN voter_epic_number VARCHAR(20) NULL AFTER pan_number,
    ADD COLUMN bank_name VARCHAR(100) NULL AFTER voter_epic_number,
    ADD COLUMN bank_branch VARCHAR(100) NULL AFTER bank_name,
    ADD COLUMN ifsc_code VARCHAR(20) NULL AFTER bank_branch,
    ADD COLUMN account_number VARCHAR(30) NULL AFTER ifsc_code,
    ADD COLUMN documents JSON NULL AFTER account_number;

UPDATE teachers SET date_of_birth = '2000-01-01' WHERE date_of_birth IS NULL;
UPDATE teachers SET gender = 'Other' WHERE gender IS NULL;

ALTER TABLE teachers
    MODIFY date_of_birth DATE NOT NULL,
    MODIFY gender ENUM('Male','Female','Other') NOT NULL;

ALTER TABLE teachers
    ADD UNIQUE KEY uq_teachers_aadhaar (aadhaar_number),
    ADD UNIQUE KEY uq_teachers_pan (pan_number),
    ADD UNIQUE KEY uq_teachers_voter_epic (voter_epic_number);";

	echo "<span class='text-info'>Executing:<br>$sql</span><br>";
	
	$pdo->exec($sql);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>