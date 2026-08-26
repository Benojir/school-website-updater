-- =====================================================================
--  Migration: give `teachers` the student financial-report permission
-- =====================================================================
--  Adds:
--    * A `can_view_student_financial_report` permission flag, matching the
--      column of the same name already on `drivers` (see migrate.sql).
--
--  Run this ONCE. Re-importing it will fail on "Duplicate column name".
-- =====================================================================


-- ---------------------------------------------------------------------
-- Access permission flag.
--
-- Gates whether a teacher's app may see the fee dues and payment history of
-- the students in their assigned sections. Defaults to 0 (denied) so existing
-- teachers do not silently gain access to student financial data on import --
-- switch it on per teacher from Add Teacher or the bulk editor.
-- ---------------------------------------------------------------------
ALTER TABLE `teachers`
  ADD COLUMN `can_view_student_financial_report` tinyint(1) NOT NULL DEFAULT 0 AFTER `can_view_parent_contacts`;
