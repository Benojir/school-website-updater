<?php
/**
 * Migration: add the per-teacher website / access switches to `teachers`.
 *
 *   show_on_website          - is this teacher listed on the public pages?
 *   share_phone_with_chatbot - may the chatbot hand out this teacher's number?
 *   display_priority         - manual front-end ordering (lower first, NULL = unordered)
 *   can_view_parent_contacts - may this teacher see parents' phone numbers / emails?
 *
 * The three flags default to 1 and display_priority to NULL, so existing teachers keep
 * behaving exactly as they do today until an admin changes something.
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

// column name => definition to append after the previous one
$newColumns = [
    'show_on_website'          => "tinyint(1) NOT NULL DEFAULT '1'",
    'share_phone_with_chatbot' => "tinyint(1) NOT NULL DEFAULT '1'",
    'display_priority'         => "int DEFAULT NULL",
    'can_view_parent_contacts' => "tinyint(1) NOT NULL DEFAULT '1'",
];

// Keep the new columns grouped right after `status`, in the order listed above
$afterColumn = 'status';

foreach ($newColumns as $column => $definition) {
    $exists = $pdo->query("SHOW COLUMNS FROM `teachers` LIKE " . $pdo->quote($column))->fetch();

    if ($exists) {
        log_message("Column `teachers`.`{$column}` already exists - skipping.");
    } else {
        $pdo->exec("ALTER TABLE `teachers` ADD COLUMN `{$column}` {$definition} AFTER `{$afterColumn}`");
        log_message("Column `teachers`.`{$column}` added.");
    }

    $afterColumn = $column;
}

log_message("Table `teachers` is ready.");
