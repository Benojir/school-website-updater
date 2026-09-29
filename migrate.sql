-- Teacher App mark-entry permission. Review and run manually; never auto-applied.
-- All existing and new teachers start without permission. Grant it from Edit Teachers.
SET @marks_permission_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'teachers' AND COLUMN_NAME = 'can_enter_marks'
);
SET @marks_permission_sql = IF(@marks_permission_exists = 0,
    'ALTER TABLE teachers ADD COLUMN can_enter_marks TINYINT(1) NOT NULL DEFAULT 0',
    'SELECT ''can_enter_marks already exists'' AS migration_status');
PREPARE marks_permission_statement FROM @marks_permission_sql;
EXECUTE marks_permission_statement;
DEALLOCATE PREPARE marks_permission_statement;
