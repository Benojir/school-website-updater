-- =====================================================================
--  Migration: bring the `drivers` table up to parity with `teachers`
-- =====================================================================
--  Adds:
--    * Family / parent contact fields
--    * Bank & statutory fields (PAN, Voter EPIC, bank, IFSC, account)
--    * A `documents` JSON column holding [{document_name, document_url}]
--    * A `can_view_student_financial_report` permission flag
--    * UNIQUE indexes on aadhaar_number / pan_number / voter_epic_number
--      (mirrors the existing uq_teachers_* indexes)
--
--  Run this ONCE. Re-importing it will fail on "Duplicate column name".
-- =====================================================================


-- ---------------------------------------------------------------------
-- STEP 1: Normalise blank Aadhaar numbers to NULL.
--
-- Some existing driver rows store '' instead of NULL. A UNIQUE index
-- treats every '' as the same value, so the index in STEP 3 would fail
-- with "Duplicate entry ''" unless these are nulled out first.
-- NULLs are exempt from UNIQUE checks, which is exactly what we want
-- for an optional identity field.
-- ---------------------------------------------------------------------
UPDATE `drivers` SET `aadhaar_number` = NULL WHERE `aadhaar_number` = '';


-- ---------------------------------------------------------------------
-- STEP 2: Add the new columns.
-- Types match the equivalent columns on the `teachers` table.
-- ---------------------------------------------------------------------
ALTER TABLE `drivers`
  ADD COLUMN `father_name`       varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL AFTER `aadhaar_number`,
  ADD COLUMN `father_phone`      varchar(15)  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL AFTER `father_name`,
  ADD COLUMN `mother_name`       varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL AFTER `father_phone`,
  ADD COLUMN `mother_phone`      varchar(15)  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL AFTER `mother_name`,
  ADD COLUMN `pan_number`        varchar(10)  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL AFTER `mother_phone`,
  ADD COLUMN `voter_epic_number` varchar(20)  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL AFTER `pan_number`,
  ADD COLUMN `bank_name`         varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL AFTER `voter_epic_number`,
  ADD COLUMN `bank_branch`       varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL AFTER `bank_name`,
  ADD COLUMN `ifsc_code`         varchar(20)  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL AFTER `bank_branch`,
  ADD COLUMN `account_number`    varchar(30)  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL AFTER `ifsc_code`,
  ADD COLUMN `documents`         json DEFAULT NULL AFTER `account_number`;


-- ---------------------------------------------------------------------
-- STEP 3: Access permission flag.
--
-- Gates whether a driver's app may see the fee / bank / income figures of
-- the students assigned to them. Defaults to 0 (denied) so existing drivers
-- do not silently gain access to student financial data on import — switch
-- it on per driver from Add Driver or the bulk editor.
-- ---------------------------------------------------------------------
ALTER TABLE `drivers`
  ADD COLUMN `can_view_student_financial_report` tinyint(1) NOT NULL DEFAULT 0 AFTER `documents`;


-- ---------------------------------------------------------------------
-- STEP 4: Enforce uniqueness on the optional identity fields, the same
-- way `teachers` does (uq_teachers_aadhaar / _pan / _voter_epic).
-- ---------------------------------------------------------------------
ALTER TABLE `drivers`
  ADD UNIQUE KEY `uq_drivers_aadhaar`    (`aadhaar_number`),
  ADD UNIQUE KEY `uq_drivers_pan`        (`pan_number`),
  ADD UNIQUE KEY `uq_drivers_voter_epic` (`voter_epic_number`);


ALTER TABLE student_attendance MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;