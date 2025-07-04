<?php
// Check if user is logged in
include_once("../../includes/auth-check.php");
include_once("../../includes/permission-check.php");
require_once("../../includes/db.php");

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
$required = ['studentIDs', 'amount', 'payment_date'];
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
    $provided_payment_amount = sanitize_input($_POST['amount']);
    $payment_date = sanitize_input($_POST['payment_date']);
    // $discount = sanitize_input($_POST['discount']);
    // $admin = $_SESSION['user']['full_name']; // Get admin name from session

    // Get Student IDs Array
    $studentIDs = explode(',', $student_ids_string);

    if (count($studentIDs) <= 0) {
        throw new Exception('Invalid student ids');
    }

    foreach ($studentIDs as $student_id) {
        $payment_amount = $provided_payment_amount;
        // Fetch student info
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->execute([$student_id]);

        $student_info = $stmt->fetch(PDO::FETCH_ASSOC);

        // Skip if not found or student has left
        if (!$student_info || strtolower($student_info['status']) === 'left' || strtolower($student_info['status']) === 'alumni') {
            // Student not found or left or alumni so skipped
            continue;
        }

        $payment_history_remarks = [];

        $stmt = $pdo->prepare("SELECT * FROM student_unpaid_fees WHERE student_id = ? ORDER BY STR_TO_DATE(CONCAT('01 ', month_year), '%d %M %Y') ASC");
        $stmt->execute([$student_id]);
        $unpaid_fees_data = $stmt->fetchAll();

        $partial_payment_ids_backup = [];
        $full_paid_payment_ids = [];
        $unpaid_fee_rows_backup = [];

        foreach ($unpaid_fees_data as $unpaid_fee_data) {

            $unpaid_fee_rows_backup[] = $unpaid_fee_data;

            $unpaid_fee_id = $unpaid_fee_data['id'];
            $month_year = $unpaid_fee_data['month_year'];
            $actual_amount = $unpaid_fee_data['actual_amount'];
            $remark = $unpaid_fee_data['remark'];
            $discount_amount = $unpaid_fee_data['discount_amount'];
            $unpaid_amount = $unpaid_fee_data['unpaid_amount'];

            if ($payment_amount <= 0) break;

            if ($payment_amount < $unpaid_amount) { // make partial payment then
                $unpaid_amount = $unpaid_amount - $payment_amount;

                // Update the unpaid fees table
                $stmt = $pdo->prepare("UPDATE student_unpaid_fees SET unpaid_amount = ? WHERE id = ?");
                $stmt->execute([$unpaid_amount, $unpaid_fee_id]);

                $partial_remark = "₹" . $payment_amount . "/- has been used for fee payment " . $month_year . " from payment amount ₹" . $provided_payment_amount . "/- (" . $payment_date . ")";
                $payment_history_remarks[] = $payment_amount . " used for payment " . $month_year;

                // Insert partial payment record
                $stmt = $pdo->prepare("
                    INSERT INTO student_partial_payments
                    (
                    student_id, 
                    month_year, 
                    unpaid_fees_id, 
                    partial_paid_amount, 
                    method, 
                    remark,
                    created_at
                    ) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([$student_id, $month_year, $unpaid_fee_id, $payment_amount, 'cash', $partial_remark, $payment_date]);

                $partial_payment_ids_backup[] = $pdo->lastInsertId();

                $payment_amount = 0;

                break;
            } else {
                $paid_amount = $unpaid_amount;

                // Insert into full_paid_fees table
                $stmt = $pdo->prepare("
                    INSERT INTO student_full_paid_fees
                    (
                    student_id, 
                    month_year, 
                    actual_amount, 
                    discount_amount, 
                    total_paid_amount, 
                    last_paid_amount, 
                    remark, 
                    created_at
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$student_id, $month_year, $actual_amount, $discount_amount, ($actual_amount - $discount_amount), $paid_amount, $remark]);
                $full_paid_fees_id = $pdo->lastInsertId();

                $full_paid_payment_ids[] = $full_paid_fees_id;

                // Insert partial payment record
                $partial_remark = "₹" . $paid_amount . "/- has been used for fee payment " . $month_year . " from payment amount ₹" . $provided_payment_amount . "/- (" . $payment_date . ")";
                $payment_history_remarks[] = $paid_amount . " used for payment " . $month_year;

                $stmt = $pdo->prepare("
                    INSERT INTO student_partial_payments
                    (
                    student_id, 
                    month_year, 
                    unpaid_fees_id, 
                    full_paid_fees_id, 
                    partial_paid_amount, 
                    method, 
                    remark,
                    created_at
                    ) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([$student_id, $month_year, $unpaid_fee_id, $full_paid_fees_id, $paid_amount, 'cash', $partial_remark, $payment_date]);

                $partial_payment_ids_backup[] = $pdo->lastInsertId();

                // Update partial payments table for inserting full paid fees id  
                $stmt = $pdo->prepare("UPDATE student_partial_payments SET full_paid_fees_id = ? WHERE unpaid_fees_id = ?");
                $stmt->execute([$full_paid_fees_id, $unpaid_fee_id]);

                // Delete unpaid fees table data
                $stmt = $pdo->prepare("DELETE FROM student_unpaid_fees WHERE id = ?");
                $stmt->execute([$unpaid_fee_id]);

                $payment_amount -= $unpaid_amount;
            }
        }

        // Handle remaining payment amount (advance)
        // In the wallet transaction section, replace with this:
        $transaction_id = NULL;

        if ($payment_amount > 0) {
            // Deposit to student wallet for future usage
            $stmt = $pdo->prepare("SELECT * FROM student_wallet WHERE student_id = ?");
            $stmt->execute([$student_id]);
            $student_wallet = $stmt->fetch(PDO::FETCH_ASSOC);

            $wallet_id = null;
            $transaction_type = 'deposit';
            $transaction_id = uniqid('tran');
            $description = 'Credited to wallet';

            try {
                $pdo->beginTransaction();

                if (!$student_wallet) {
                    $stmt = $pdo->prepare("INSERT INTO student_wallet(student_id, balance) VALUES (?, ?)");
                    $stmt->execute([$student_id, $payment_amount]);
                    $wallet_id = $pdo->lastInsertId();
                    $new_balance = $payment_amount;
                } else {
                    $wallet_id = $student_wallet['id'];
                    $new_balance = $student_wallet['balance'] + $payment_amount;
                    $stmt = $pdo->prepare("UPDATE student_wallet SET balance = ? WHERE id = ?");
                    $stmt->execute([$new_balance, $wallet_id]);
                }

                // Insert transaction record
                $stmt = $pdo->prepare("
                    INSERT INTO wallet_transactions
                    (wallet_id, student_id, amount, transaction_type, transaction_id, description) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $wallet_id,
                    $student_id,
                    $payment_amount, // The amount being deposited now
                    $transaction_type,
                    $transaction_id,
                    $description
                ]);

                $pdo->commit();

                $payment_history_remarks[] = $payment_amount . " credited to wallet";
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Wallet transaction failed for student $student_id: " . $e->getMessage());
                // Continue processing but log the error
            }
        }

        // Insert payment history
        $payment_history_remark = implode(' & ', $payment_history_remarks);

        $stmt = $pdo->prepare("
            INSERT INTO student_payment_history
            (
            student_id, 
            payment_amount, 
            payment_date, 
            remark, 
            full_paid_payment_ids, 
            partial_payment_ids_backup,
            wallet_affected_balance,
            wallet_transaction_id,
            unpaid_fee_rows_backup
            ) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $student_id,
            $provided_payment_amount,
            $payment_date,
            $payment_history_remark,
            json_encode($full_paid_payment_ids),
            json_encode($partial_payment_ids_backup),
            $payment_amount,
            $transaction_id,
            json_encode($unpaid_fee_rows_backup)
        ]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Payment updated successfully.'
    ]);

    try {
        // Get FCM tokens for all processed students
        $fcmTokens = getFCMTokensFromDatabase($pdo, $studentIDs);
        if ($fcmTokens) {
            $notificationTitle = 'Payment Update';
            $notificationBody = 'Your payment has been processed successfully.';
            $data = [
                'title' => $notificationTitle,
                'message' => $notificationBody
            ];
            sendFirebaseNotification($fcmTokens, $notificationTitle, $notificationBody, $data);
        }
    } catch (Exception $e) {
        // Log the error if needed
        // error_log("Failed to send notification: " . $e->getMessage());
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
