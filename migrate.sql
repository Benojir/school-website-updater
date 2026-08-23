-- =====================================================================
--  Teacher application module migration
--  -------------------------------------------------------------------
--  Public teacher applications are no longer stored in `teacher_applications`.
--  They are inserted straight into `teachers` with status = 'pending', and the
--  admin moves them through the lifecycle: pending -> active / reject / archive.
--
--  Run this ONCE against the school_erp database, e.g.
--      mysql -u root -p school_erp < migrate.sql
--
--  MySQL has no "ADD COLUMN IF NOT EXISTS", so re-running this file will
--  report duplicate-column errors. That is expected - it is a one-time script.
-- =====================================================================


-- ---------------------------------------------------------------------
-- 1. Application lifecycle states for teachers.status
--    'pending' = newly submitted application awaiting review
--    'reject'  = application turned down (record kept for the audit trail)
--    'archive' = application parked out of the main pending list
-- ---------------------------------------------------------------------
ALTER TABLE `teachers`
    MODIFY COLUMN `status` ENUM('active', 'inactive', 'pending', 'reject', 'archive')
    CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'active';


-- ---------------------------------------------------------------------
-- 2. The joining date is decided by the admin when the application is
--    approved, so an incoming application has none yet.
-- ---------------------------------------------------------------------
ALTER TABLE `teachers`
    MODIFY COLUMN `joining_date` DATE DEFAULT NULL;


-- ---------------------------------------------------------------------
-- 3. Date of birth and gender stay required in the application form and in
--    add-teacher.php, but must accept NULL so that applications migrated from
--    the old `teacher_applications` table (which never collected them) can be
--    stored honestly as "unknown" instead of with an invented placeholder.
-- ---------------------------------------------------------------------
ALTER TABLE `teachers`
    MODIFY COLUMN `date_of_birth` DATE DEFAULT NULL,
    MODIFY COLUMN `gender` ENUM('Male', 'Female', 'Other')
    CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL;


-- ---------------------------------------------------------------------
-- 4. Teaching-experience details collected by the public application form
-- ---------------------------------------------------------------------
ALTER TABLE `teachers`
    ADD COLUMN `has_experience` ENUM('yes', 'no')
        CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
        NOT NULL DEFAULT 'no' AFTER `subject_specialization`,
    ADD COLUMN `previous_school` TEXT
        CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
        NULL AFTER `has_experience`;


-- ---------------------------------------------------------------------
-- 5. The applications screens filter by status on every page load
-- ---------------------------------------------------------------------
ALTER TABLE `teachers`
    ADD INDEX `idx_teachers_status` (`status`);


-- ---------------------------------------------------------------------
-- 6. Carry the existing pending applications over to `teachers` so they stay
--    visible on the applications page after the switch.
--
--    - INSERT IGNORE + NOT EXISTS skips any applicant whose email is already
--      registered as a teacher (teachers.email is a UNIQUE key).
--    - The explicit COLLATE is required: `teachers` is utf8mb4_general_ci while
--      `teacher_applications` is utf8mb4_unicode_ci, and MySQL refuses to compare
--      columns across two collations (error 1267).
--    - date_of_birth / gender land as NULL because the old form never asked
--      for them; the admin should fill them in before approving.
--
--    `teacher_applications` itself is left untouched as a backup. Once you are
--    satisfied with the migration you can drop it with the statement at the
--    bottom of this file.
-- ---------------------------------------------------------------------
INSERT IGNORE INTO `teachers` (
    `name`, `email`, `phone`,
    `village`, `post_office`, `police_station`, `district`, `pincode`, `address`,
    `qualification`, `subject_specialization`, `has_experience`, `previous_school`,
    `teacher_image`, `application_date`, `joining_date`, `status`
)
SELECT
    ta.`name`,
    ta.`email`,
    ta.`phone_number`,
    ta.`village`,
    ta.`post_office`,
    ta.`police_station`,
    ta.`district`,
    ta.`pincode`,
    ta.`address`,
    ta.`qualification`,
    ta.`specialization`,
    ta.`has_experience`,
    ta.`previous_school`,
    ta.`applicant_image`,
    ta.`application_date`,
    NULL,
    'pending'
FROM `teacher_applications` ta
WHERE NOT EXISTS (
    SELECT 1 FROM `teachers` t
    WHERE t.`email` = ta.`email` COLLATE utf8mb4_general_ci
);


-- ---------------------------------------------------------------------
-- 7. OPTIONAL - only after you have verified step 6 landed correctly.
--    Uncomment to retire the now-unused table.
-- ---------------------------------------------------------------------
-- DROP TABLE `teacher_applications`;
