<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$stmt = $pdo->prepare("SELECT * FROM drivers");
	$stmt->execute();
	
	$drivers = $stmt->fetchAll(PDO::FETCH_ASSOC);

	foreach($drivers as $driver) {
		$route_id = $driver['route_id'];
		$driver_id = $driver['driver_id'];

		if (!empty($route_id)) {
			$json_route = json_encode($route_id);
			$stmt = $pdo->prepare("UPDATE drivers SET route_ids = :route_ids WHERE id = :driver_id");
			$stmt->bindParam(':route_ids', $json_route);
			$stmt->bindParam(':driver_id', $driver_id);
			
			if (!$stmt->execute()) {
				echo "<span style='color: red;'>Failed to update route_ids for driver ID: " . $driver_id . "</span><br>";
			} else {
				echo "<span style='color: green;'>Updated route_ids for driver ID: " . $driver_id . "</span><br>";
			}
		}
	}
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>