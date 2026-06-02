<?php

//include_once("includes/db.php"); No need to include this as the update.php already included

echo "Starting Migration Script...<br>";

try {
	$sql1 = "ALTER TABLE `admission_unpaid_fees` CHANGE `actual_amount` `actual_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `admission_unpaid_fees` CHANGE `unpaid_amount` `unpaid_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `admission_unpaid_fees` CHANGE `discount_amount` `discount_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `admission_partial_fees_payments` CHANGE `partial_paid_amount` `partial_paid_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `admission_full_paid_fees` CHANGE `actual_amount` `actual_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `admission_full_paid_fees` CHANGE `discount_amount` `discount_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `admission_full_paid_fees` CHANGE `total_paid_amount` `total_paid_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `admission_full_paid_fees` CHANGE `last_paid_amount` `last_paid_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `admission_fees_payment_history` CHANGE `payment_amount` `payment_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `admission_fees_payment_history` CHANGE `wallet_affected_balance` `wallet_affected_balance` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `student_full_paid_fees` CHANGE `actual_amount` `actual_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `student_full_paid_fees` CHANGE `discount_amount` `discount_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `student_full_paid_fees` CHANGE `total_paid_amount` `total_paid_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `student_full_paid_fees` CHANGE `last_paid_amount` `last_paid_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `student_partial_payments` CHANGE `partial_paid_amount` `partial_paid_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `student_payment_history` CHANGE `wallet_affected_balance` `wallet_affected_balance` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `student_payment_history` CHANGE `payment_amount` `payment_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `student_unpaid_fees` CHANGE `actual_amount` `actual_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `student_unpaid_fees` CHANGE `unpaid_amount` `unpaid_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `student_unpaid_fees` CHANGE `discount_amount` `discount_amount` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `class_wise_monthly_fees` CHANGE `amount` `amount` DECIMAL(10,2) NOT NULL;
ALTER TABLE `class_wise_additional_fees` CHANGE `amount` `amount` DECIMAL(10,2) NOT NULL;
ALTER TABLE `class_wise_new_admission_fees` CHANGE `amount` `amount` DECIMAL(10,2) NOT NULL;";
	
	$pdo->exec($sql1);
	
	echo "Database migration successfully done! 😅<br>";

} catch (PDOException $e) {
    echo "Database migration failed! Error: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
	echo "Migration script failed! Error: " . $e->getMessage() . "<br>";
}

?>