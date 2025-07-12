<?php
$config_file_path = 'includes/config.php';
if (file_exists($config_file_path)) {
    include_once($config_file_path);
}

try {
    $sql = "ALTER TABLE website_config 
            ADD admin_login_option_show VARCHAR(5) NULL DEFAULT 'no' AFTER total_student_show,
            ADD allow_online_payment VARCHAR(5) NULL DEFAULT 'no' AFTER admin_login_option_show";

    $pdo->exec($sql);
    echo "Schema updated successfully.";
} catch (PDOException $e) {
    echo "Error updating database schema: " . $e->getMessage();
}
