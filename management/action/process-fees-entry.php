<?php
// Check if user is logged in
include_once("../../includes/auth-check.php");
include_once("../../includes/permission-check.php");

header('Content-Type: application/json');

// Check if user has permission to perform this action
if (!hasPermission(PERM_MANAGE_FEES)) {
    echo json_encode([
        'success' => false,
        'message' => 'You do not have permission to perform this action.',
        'error' => true
    ]);
    die();
}

// Check for required fields
$required = ['studentIDs', 'month_year'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required',
            'field' => $field
        ]);
        exit;
    }
}

try {
    $student_ids_string = sanitize_input($_POST['studentIDs']);
    $month_year = sanitize_input($_POST['month_year']);
    $discount = sanitize_input($_POST['discount']);

    if (empty($discount)) {
        $discount = 0;
    }

    if (!is_numeric(($discount))) {
        throw new Exception('Invalid discount input.');
    }

    // Get Student IDs Array
    $studentIDs = explode(',', $student_ids_string);

    if (count($studentIDs) <= 0) {
        throw new Exception('Invalid student ids');
    }

    $results = [];
    $processedCount = 0;
    $skippedCount = 0;
    $last_proccessed_sid;
    $processedStudentIds = [];

    foreach ($studentIDs as $student_id) {
        // Start transaction for each student
        $pdo->beginTransaction();

        try {
            // Fetch student info
            $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
            $stmt->execute([$student_id]);
            $student_info = $stmt->fetch(PDO::FETCH_ASSOC);

            // Skip if not found or student has left
            if (!$student_info || strtolower($student_info['status']) === 'left' || strtolower($student_info['status']) === 'alumni') {
                $results[] = [
                    'student_id' => $student_id,
                    'success' => false,
                    'message' => 'Student not found or has left or alumni',
                    'action' => 'skipped'
                ];
                $skippedCount++;
                $pdo->commit();
                continue;
            }

            $car_fee = $student_info['car_fee'] ?? 0;
            $hostel_fee = $student_info['hostel_fee'] ?? 0;
            $custom_class_fee = $student_info['custom_class_fee'] ?? 0;
            $class_id = $student_info['class_id'];
            $remark = "Monthly fee";

            if ($discount > 0) {
                $remark = $remark . ' with ' . $discount . '/- discount applied ';
            }

            // Fetch class-wise fee
            $stmt = $pdo->prepare("SELECT amount FROM class_wise_fee WHERE class_id = ?");
            $stmt->execute([$class_id]);
            $class_fee_row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$class_fee_row) {
                throw new Exception('No class wise fee added in database. Please add class wise fees from the manage fees page in general settings.');
            }

            $class_fee = $custom_class_fee > 0 ? $custom_class_fee : $class_fee_row['amount'];

            // Build remark based on fees
            $includes = [];
            if ($custom_class_fee > 0) $includes[] = 'custom class fee';
            if ($car_fee > 0) $includes[] = 'car';
            if ($hostel_fee > 0) $includes[] = 'hostel';

            if ($includes) {
                $remark .= ' (included ' . implode(' and ', $includes) . ' fees)';
            }

            // Calculate total fees
            $actual_fees = ($class_fee + $car_fee + $hostel_fee);
            $total_fees_with_discount = $actual_fees - $discount;

            // Check if this month's fee already exists in full_paid_fees
            $stmt = $pdo->prepare("SELECT id FROM student_full_paid_fees WHERE student_id = ? AND month_year = ?");
            $stmt->execute([$student_id, $month_year]);
            if ($stmt->fetch()) {
                $results[] = [
                    'student_id' => $student_id,
                    'success' => false,
                    'message' => "Fee for $month_year already marked as fully paid",
                    'action' => 'skipped'
                ];
                $skippedCount++;
                $pdo->commit();
                continue;
            }

            // Check for existing unpaid fee for this month
            $stmt = $pdo->prepare("
                SELECT *
                FROM student_unpaid_fees 
                WHERE student_id = ? AND month_year = ?
                LIMIT 1
            ");
            $stmt->execute([$student_id, $month_year]);
            $existing_fee = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_fee) {
                // If existing unpaid fee found, check if it's the same amount
                if ($existing_fee['unpaid_amount'] == $total_fees_with_discount && $existing_fee['discount_amount'] == $discount) {
                    $results[] = [
                        'student_id' => $student_id,
                        'success' => false,
                        'message' => "Unpaid fee for $month_year already exists with same amount",
                        'action' => 'skipped'
                    ];
                    $skippedCount++;
                    $pdo->commit();
                    continue;
                }

                // Update existing record
                $updateStmt = $pdo->prepare("
                    UPDATE student_unpaid_fees 
                    SET unpaid_amount = ?, discount_amount = ?, remark = ? 
                    WHERE id = ?
                ");
                $updateStmt->execute([
                    $total_fees_with_discount,
                    $discount,
                    $remark,
                    $existing_fee['id']
                ]);

                $action = 'updated';
                $processedStudentIds[] = $student_id;
            } else {
                // Insert new record
                $insertStmt = $pdo->prepare("
                    INSERT INTO student_unpaid_fees
                    (student_id, month_year, actual_amount, unpaid_amount, discount_amount, remark, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $insertStmt->execute([
                    $student_id,
                    $month_year,
                    $actual_fees,
                    $total_fees_with_discount,
                    $discount,
                    $remark
                ]);

                $action = 'created';
                $processedStudentIds[] = $student_id;
            }

            $results[] = [
                'student_id' => $student_id,
                'success' => true,
                'message' => "Fee record {$action} successfully",
                'action' => $action,
                'amount' => $total_fees_with_discount
            ];
            $processedCount++;
            $last_proccessed_sid = $student_id;

            $pdo->commit();

            if (count($processedStudentIds) > 0) {
                $fcmTokens = getFCMTokensFromDatabase($pdo, $processedStudentIds);
                if ($fcmTokens) {
                    $notificationTitle = 'Unpaid Fee (' . $month_year . ')';
                    $notificationBody = 'Your monthly fee has been updated successfully. Please check your account for details.';
                    $data = [
                        'title' => $notificationTitle,
                        'message' => $notificationBody
                    ];
                    try {
                        sendFirebaseNotification($fcmTokens, $notificationTitle, $notificationBody, $data);
                    } catch (Exception $e) {
                        // Log the error if needed
                        // error_log("Failed to send notification: " . $e->getMessage());
                    }
                }
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    echo json_encode([
        'success' => true,
        'message' => "Processed {$processedCount} students, skipped {$skippedCount}",
        'total_students' => count($studentIDs),
        'processed' => $processedCount,
        'skipped' => $skippedCount,
        'results' => $results
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => true,
        'processed_up_to' => $last_proccessed_sid ?? null
    ]);
}
