-- --------------------------------------------------------
-- Migration: Deleted payment logs
--
-- Stores an audit record every time an admin deletes a payment from
-- `student_payment_history` (monthly) or
-- `admission_fees_payment_history` (admission).
--
-- Nothing is duplicated in this table. Only IDs are stored:
--   `student_id`        -> joined to `students` for name / class / roll / father / phone
--   `payment_entry_by`  -> joined to `users` for the name of the receiving admin
--   `deleted_by`        -> joined to `users` for the name of the deleting admin
-- so renaming a student or an admin later is reflected in the log automatically.
--
-- Run this once on the school_erp database (phpMyAdmin -> SQL tab).
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `deleted_payment_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `payment_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `original_payment_id` bigint UNSIGNED DEFAULT NULL,
  `student_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `method` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `wallet_affected_balance` decimal(10,2) DEFAULT NULL,
  `wallet_transaction_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payment_entry_by` int DEFAULT NULL,
  `payment_recorded_at` timestamp NULL DEFAULT NULL,
  `payment_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `deleted_by` int DEFAULT NULL,
  `deleted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_dpl_payment_type` (`payment_type`),
  KEY `idx_dpl_student_id` (`student_id`),
  KEY `idx_dpl_payment_entry_by` (`payment_entry_by`),
  KEY `idx_dpl_deleted_by` (`deleted_by`),
  KEY `idx_dpl_deleted_at` (`deleted_at`),
  KEY `idx_dpl_payment_date` (`payment_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- ONLY run the block below if you already created this table with an earlier
-- version that duplicated the student / admin names. It drops those columns.
-- Remove any lines for columns you never created.
-- --------------------------------------------------------

-- ALTER TABLE `deleted_payment_logs`
--   DROP COLUMN `student_name`,
--   DROP COLUMN `student_image`,
--   DROP COLUMN `class_name`,
--   DROP COLUMN `section_name`,
--   DROP COLUMN `roll_no`,
--   DROP COLUMN `registration_no`,
--   DROP COLUMN `father_name`,
--   DROP COLUMN `phone_number`,
--   DROP COLUMN `payment_entry_by_name`,
--   DROP COLUMN `deleted_by_name`,
--   ADD KEY `idx_dpl_payment_entry_by` (`payment_entry_by`);
