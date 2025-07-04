<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($websiteConfig)) {
    echo json_encode([
        'success' => false,
        'message' => 'Website configuration not found.'
    ]);
    exit;
}

$settings = [
    'country_code' => $websiteConfig['country_code'],
    'theme_color' => $websiteConfig['theme_color']
];

echo json_encode([
    'success' => true,
    'data' => $settings
]);
