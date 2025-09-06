<?php

include_once("includes/db.php");

// Create the 'drivers' table
$sql = "CREATE TABLE `drivers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_id` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `vehicle_number` varchar(20) NOT NULL,
  `route` varchar(255) NOT NULL,
  `driver_image` varchar(255) DEFAULT 'default_driver_dp.jpg',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `driver_id` (`driver_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `vehicle_number` (`vehicle_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Execute the query
if ($conn->query($sql) === TRUE) {
    echo "Table 'drivers' created successfully.";
} else {
    echo "Error creating table: " . $conn->error;
}

// Close the connection
$conn->close();
?>
