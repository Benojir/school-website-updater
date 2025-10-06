<?php

include_once("includes/db.php");

echo "<h3>Starting Database Migration...</h3><br>";

// Start a transaction
$pdo->beginTransaction();

try {
    // 1. Process the SQL file to create the 'driving_routes' table
    echo "<h4>Processing SQL files...</h4><br>";
    $sql_file = 'driving_routes.sql';

    if (!file_exists($sql_file)) {
        throw new Exception("File '$sql_file' not found.");
    }
    
    $sql = file_get_contents($sql_file);
    $pdo->exec($sql);
    echo "✅ Successfully executed: <strong>$sql_file</strong><br>";

    // 2. Alter the 'drivers' table and add a foreign key
    echo "<h4>Altering tables...</h4><br>";
    $query1 = "ALTER TABLE `drivers` ADD `route_id` INT NULL DEFAULT NULL AFTER `vehicle_number`";
    $pdo->exec($query1);
    echo "✅ Successfully executed: <strong>Added route_id column to drivers</strong><br>";

    // Add the foreign key constraint
    $query2 = "ALTER TABLE `drivers` ADD CONSTRAINT `fk_driver_route` FOREIGN KEY (`route_id`) REFERENCES `driving_routes`(`id`) ON DELETE SET NULL ON UPDATE CASCADE";
    $pdo->exec($query2);
    echo "✅ Successfully executed: <strong>Added foreign key to drivers.route_id</strong><br>";


    // 3. Alter the 'students' table and add a foreign key
    $query3 = "ALTER TABLE `students` CHANGE `car_route` `driving_route_id` INT NULL DEFAULT NULL";
    $pdo->exec($query3);
    echo "✅ Successfully executed: <strong>Renamed column in students table</strong><br>";
    
    // Add the foreign key constraint
    $query4 = "ALTER TABLE `students` ADD CONSTRAINT `fk_student_route` FOREIGN KEY (`driving_route_id`) REFERENCES `driving_routes`(`id`) ON DELETE SET NULL ON UPDATE CASCADE";
    $pdo->exec($query4);
    echo "✅ Successfully executed: <strong>Added foreign key to students.driving_route_id</strong><br>";

    // If everything was successful, commit the changes
    $pdo->commit();
    echo "<hr><h3>✅ Migration Complete. All changes have been committed.</h3>";

} catch (Exception $e) {
    // If any step failed, roll back all changes
    $pdo->rollBack();
    echo "<hr><h3>❌ Migration Failed. All changes have been rolled back.</h3>";
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
}

?>