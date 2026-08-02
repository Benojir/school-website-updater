<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql = "CREATE TABLE parent_submitted_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone_number VARCHAR(20) NOT NULL,
    request_type VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    telegram_file_id VARCHAR(255) DEFAULT NULL,
    file_type ENUM('image', 'video', 'audio', 'document') DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
ALTER TABLE students
    ADD COLUMN father_pan_no VARCHAR(20) NULL AFTER student_image,
    ADD COLUMN father_voter_no VARCHAR(20) NULL AFTER father_pan_no,
    ADD COLUMN mother_pan_no VARCHAR(20) NULL AFTER father_voter_no,
    ADD COLUMN mother_voter_no VARCHAR(20) NULL AFTER mother_pan_no,
    ADD COLUMN father_image VARCHAR(255) NULL AFTER mother_voter_no,
    ADD COLUMN mother_image VARCHAR(255) NULL AFTER father_image,
    ADD COLUMN father_aadhaar_image TEXT NULL AFTER mother_image,
    ADD COLUMN father_voter_image TEXT NULL AFTER father_aadhaar_image,
    ADD COLUMN father_pan_image TEXT NULL AFTER father_voter_image,
    ADD COLUMN mother_aadhaar_image TEXT NULL AFTER father_pan_image,
    ADD COLUMN mother_voter_image TEXT NULL AFTER mother_aadhaar_image,
    ADD COLUMN mother_pan_image TEXT NULL AFTER mother_voter_image,
    ADD COLUMN student_birth_certificate TEXT NULL AFTER mother_pan_image,
    ADD COLUMN student_aadhaar_image TEXT NULL AFTER student_birth_certificate,
    ADD COLUMN student_transfer_certificate TEXT NULL AFTER student_aadhaar_image;";

	echo "<span class='text-info'>Executing $sql</span><br>";
	
	$pdo->exec($sql);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>