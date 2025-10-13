<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Database Migration...<br>";

try {
    $pdo->exec("RENAME TABLE class_wise_fee TO class_wise_monthly_fees");
    $pdo->exec("RENAME TABLE class_wise_admission_fees TO class_wise_new_admission_fees");

    $pdo->exec("
        CREATE TABLE `class_wise_re_admission_fees` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `class_id` INT(11) NOT NULL,
            `amount` DECIMAL(10,2) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");

    $pdo->exec("
        ALTER TABLE `class_wise_re_admission_fees`
        ADD CONSTRAINT `fk_class_wise_re_admission_fees_class_id`
        FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
    ");

    echo "Database migration successfully done!<br>";
} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
}

?>