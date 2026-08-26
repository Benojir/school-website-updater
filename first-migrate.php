<?php
/**
 * Migration: move school feature settings out of `website_config` into `school_settings`.
 *
 * `website_config` now only holds API keys and other sensitive data. School feature
 * related settings live in their own `school_settings` table.
 *
 * This file is included by updates/update.php and deleted afterwards.
 * It is safe to run more than once.
 *
 * @var PDO $pdo
 */

if (!isset($pdo)) {
    require_once(__DIR__ . "/includes/config.php");
}

if (!function_exists('log_message')) {
    function log_message($message)
    {
        echo $message . "<br>\n";
        flush();
    }
}

// Step 1: Create the new school_settings table
$pdo->exec("CREATE TABLE IF NOT EXISTS `school_settings` (
    `id` int NOT NULL AUTO_INCREMENT,
    `auto_request_monthly_fees_day` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT '28',
    `settings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

log_message("Table `school_settings` is ready.");

// Step 2: Copy the old values over, but only if school_settings is still empty
$alreadyMigrated = $pdo->query("SELECT id FROM school_settings LIMIT 1")->fetch();

if ($alreadyMigrated) {
    log_message("`school_settings` already contains data - skipping data migration.");
} else {
    // Defaults used when there is nothing to copy from
    $day = '28';
    $settings = [
        'admission_open' => 0,
        'teacher_application' => 0,
        'total_student_show' => 0,
        'admin_login_option_show' => 0,
        'show_teachers_on_front_page' => 0,
        'allow_online_payment' => 0,
        'auto_request_monthly_fees' => 0,
        'need_security_pin_for_payment_entry' => 0,
        'hide_fees_structure_from_website' => 0
    ];

    // Read the legacy values from website_config if those columns still exist
    $legacyColumns = $pdo->query("SHOW COLUMNS FROM `website_config` LIKE 'additional_settings'")->fetch();

    if ($legacyColumns) {
        $oldConfig = $pdo->query("SELECT additional_settings, auto_request_monthly_fees_day FROM website_config LIMIT 1")->fetch();

        if ($oldConfig) {
            $day = !empty($oldConfig['auto_request_monthly_fees_day']) ? $oldConfig['auto_request_monthly_fees_day'] : '28';

            $oldSettings = json_decode($oldConfig['additional_settings'] ?? '{}', true);

            if (is_array($oldSettings)) {
                // Keep only the keys we know about, falling back to the defaults
                foreach ($settings as $key => $default) {
                    $settings[$key] = isset($oldSettings[$key]) ? (int) $oldSettings[$key] : $default;
                }
            }

            log_message("Legacy settings read from `website_config`.");
        }
    } else {
        log_message("No legacy settings found in `website_config` - inserting defaults.");
    }

    $stmt = $pdo->prepare("INSERT INTO school_settings (auto_request_monthly_fees_day, settings) VALUES (?, ?)");
    $stmt->execute([$day, json_encode($settings)]);

    log_message("School settings migrated into `school_settings`.");
}
