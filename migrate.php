<?php

include_once("includes/db.php");

echo "<h3>Starting Database Migration...</h3><br>";

// Start a transaction to ensure all queries succeed or none do.
$pdo->beginTransaction();

try {
    // 1. Create the 'driving_routes' table in a single, efficient query.
    $createQuery = "CREATE TABLE `driving_routes` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `route_name` varchar(100) DEFAULT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    
    $pdo->exec($createQuery);
    echo "✅ Successfully executed: <strong>Created 'driving_routes' table</strong><br>";

    // 2. Alter the 'drivers' table to add the route_id column.
    echo "<h4>Altering 'drivers' table...</h4><br>";
    $queryDriversCol = "ALTER TABLE `drivers` ADD `route_id` INT NULL DEFAULT NULL AFTER `vehicle_number`";
    $pdo->exec($queryDriversCol);
    echo "✅ Successfully executed: <strong>Added 'route_id' column to 'drivers'</strong><br>";

    // 3. Alter the 'students' table to rename the column.
    echo "<h4>Altering 'students' table...</h4><br>";
    $queryStudentsCol = "ALTER TABLE `students` CHANGE `car_route` `driving_route_id` INT NULL DEFAULT NULL";
    $pdo->exec($queryStudentsCol);
    echo "✅ Successfully executed: <strong>Renamed column in 'students' table</strong><br>";
    
    // If all queries were successful, commit the changes to the database.
    $pdo->commit();
    echo "<hr><h3>✅ Migration Complete. All changes have been committed.</h3>";

} catch (PDOException $e) {
    // If any query failed, roll back all previous changes from this transaction.
    $pdo->rollBack();
    echo "<hr><h3>❌ Migration Failed. All changes have been rolled back.</h3>";
    echo "<strong>Error:</strong> " . $e->getMessage() . "<br>";
}

?>