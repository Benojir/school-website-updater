<?php
require_once(__DIR__ . "/../includes/config.php");

// Ensure logs directory exists
$log_dir = __DIR__ . '/../logs';
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}

// Set log file path
$log_file = $log_dir . '/fee-cron-log-' . date('Y-m-d') . '.txt';

// Initialize logging
file_put_contents($log_file, "=== MONTHLY FEE PROCESSING STARTED ===\n", FILE_APPEND);
file_put_contents($log_file, "Timestamp: " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

try {
    // Dry run mode (for testing)
    $dry_run = isset($argv[1]) && $argv[1] == 'dry_run=1';
    
    if ($dry_run) {
        file_put_contents($log_file, "RUNNING IN DRY MODE - NO CHANGES WILL BE MADE\n", FILE_APPEND);
    }

    // Skip date check in dry run mode
    if (!$dry_run && date('j') != 28) {
        $msg = "ERROR: This job should only run on the 28th of the month. Today is " . date('j') . ".\n";
        file_put_contents($log_file, $msg, FILE_APPEND);
        die($msg);
    }

    $month_year = date('F Y');
    file_put_contents($log_file, "Processing fees for: $month_year\n\n", FILE_APPEND);

    // Get all active students
    $stmt = $pdo->prepare("SELECT * FROM students WHERE LOWER(status) NOT IN ('left', 'alumni')");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($students) <= 0) {
        $msg = "No active students found. Exiting.\n";
        file_put_contents($log_file, $msg, FILE_APPEND);
        die($msg);
    }

    $processedCount = 0;
    $skippedCount = 0;
    $errorsCount = 0;
    $processedStudentIds = [];

    foreach ($students as $student) {
        $student_id = $student['student_id'];
        $student_name = $student['name'] ?? 'Unknown';
        $log_prefix = "[ID:$student_id $student_name] ";

        try {
            $car_fee = $student['car_fee'] ?? 0;
            $hostel_fee = $student['hostel_fee'] ?? 0;
            $custom_class_fee = $student['custom_class_fee'] ?? 0;
            $class_id = $student['class_id'];

            // Fetch class-wise fee
            $stmt = $pdo->prepare("SELECT amount FROM class_wise_fee WHERE class_id = ?");
            $stmt->execute([$class_id]);
            $class_fee_row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$class_fee_row) {
                throw new Exception("No class fee configured for class ID: $class_id");
            }

            $class_fee = $custom_class_fee > 0 ? $custom_class_fee : $class_fee_row['amount'];
            $total_fees = $class_fee + $car_fee + $hostel_fee;

            // Check if already paid
            $stmt = $pdo->prepare("SELECT id FROM student_full_paid_fees WHERE student_id = ? AND month_year = ?");
            $stmt->execute([$student_id, $month_year]);
            if ($stmt->fetch()) {
                $log_msg = $log_prefix . "SKIPPED - Fee already marked as paid\n";
                file_put_contents($log_file, $log_msg, FILE_APPEND);
                $skippedCount++;
                continue;
            }

            // Check for existing unpaid fee
            $stmt = $pdo->prepare("SELECT id, unpaid_amount FROM student_unpaid_fees WHERE student_id = ? AND month_year = ?");
            $stmt->execute([$student_id, $month_year]);
            $existing_fee = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_fee) {
                if ($existing_fee['unpaid_amount'] == $total_fees) {
                    $log_msg = $log_prefix . "SKIPPED - Unpaid fee exists with same amount ($total_fees)\n";
                    file_put_contents($log_file, $log_msg, FILE_APPEND);
                    $skippedCount++;
                    continue;
                }

                // Don't update if admin manually entered fee
                // $action = $dry_run ? "WOULD UPDATE" : "UPDATED";
                // if (!$dry_run) {
                //     $pdo->beginTransaction();
                //     $updateStmt = $pdo->prepare("UPDATE student_unpaid_fees SET unpaid_amount = ? WHERE id = ?");
                //     $updateStmt->execute([$total_fees, $existing_fee['id']]);
                //     $pdo->commit();
                // }
            } else {
                $action = $dry_run ? "WOULD CREATE" : "CREATED";
                if (!$dry_run) {
                    $pdo->beginTransaction();
                    $insertStmt = $pdo->prepare("
                        INSERT INTO student_unpaid_fees
                        (student_id, month_year, actual_amount, unpaid_amount, discount_amount, remark, created_at) 
                        VALUES (?, ?, ?, ?, 0, 'Auto-generated monthly fee', NOW())
                    ");
                    $insertStmt->execute([$student_id, $month_year, $total_fees, $total_fees]);
                    $pdo->commit();

                    $processedStudentIds[] = $student_id;
                }
            }

            $log_msg = $log_prefix . "$action fee: $total_fees (Class: $class_fee";
            if ($car_fee > 0) $log_msg .= ", Car: $car_fee";
            if ($hostel_fee > 0) $log_msg .= ", Hostel: $hostel_fee";
            $log_msg .= ")\n";

            file_put_contents($log_file, $log_msg, FILE_APPEND);
            $processedCount++;

        } catch (Exception $e) {
            $error_msg = $log_prefix . "ERROR: " . $e->getMessage() . "\n";
            file_put_contents($log_file, $error_msg, FILE_APPEND);
            $errorsCount++;
            if (!$dry_run && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
        }
    }

    // Summary
    $summary = "\n=== PROCESSING SUMMARY ===\n";
    $summary .= "Total students: " . count($students) . "\n";
    $summary .= "Successfully processed: $processedCount\n";
    $summary .= "Skipped: $skippedCount\n";
    $summary .= "Errors: $errorsCount\n";
    $summary .= "Dry run: " . ($dry_run ? "YES" : "NO") . "\n";
    $summary .= "=== COMPLETED AT " . date('H:i:s') . " ===\n\n";
    file_put_contents($log_file, $summary, FILE_APPEND);

    echo $summary; // Print summary to stdout

    // Send notifications if not in dry run mode
    if (!$dry_run && count($processedStudentIds) > 0) {
        $fcmTokens = getFCMTokensFromDatabase($pdo, $processedStudentIds);
        if ($fcmTokens) {
            $notificationTitle = 'Unpaid Fee (' . $month_year . ')';
            $notificationBody = 'Your monthly fee has been updated successfully. Please check your account for details.';
            $data = [
                'title' => $notificationTitle,
                'message' => $notificationBody
            ];
            $notificationResult = sendFirebaseNotification($fcmTokens, $notificationTitle, $notificationBody, $data);
            if (!$notificationResult['success']) {
                $error_msg = "Failed to send notification: " . $notificationResult['message'] . "\n";
                file_put_contents($log_file, $error_msg, FILE_APPEND);
            }
        }
    }

} catch (Exception $e) {
    $error = "CRITICAL ERROR: " . $e->getMessage() . "\n";
    file_put_contents($log_file, $error, FILE_APPEND);
    die($error);
}