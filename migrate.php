<?php

include_once("includes/db.php");

echo "<h3>Starting Database Migration...</h3><br>";

// Array of .sql files to be executed in order
$sql_files = [
    './admission_full_paid_fees.sql',
    './admission_unpaid_fees.sql',
    './admission_fees_payment_history.sql',
    './admission_partial_fees_payments.sql'
];

echo "<h4>Processing SQL files...</h4><br>";
foreach ($sql_files as $file) {
    try {
        // Check if the file exists before proceeding
        if (!file_exists($file)) {
            throw new Exception("File '$file' not found.<br>");
        }
        
        // Read the entire file content
        $sql = file_get_contents($file);
        
        // Execute the SQL queries from the file
        $pdo->exec($sql);
        
        echo "✅ Successfully executed: <strong>$file</strong><br>";
        
    } catch (Exception $e) {
        echo "❌ Error processing file <strong>$file</strong>: " . $e->getMessage() . "<br>";
    }
}

echo "<hr><h3>Migration Complete.</h3>";

?>