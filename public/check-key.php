<?php
// public/check-key.php
header('Content-Type: application/json');

$response = [
    'app_key_defined' => defined('APP_KEY_FORZADA'),
    'env_app_key' => $_ENV['APP_KEY'] ?? null,
    'config_app_key' => config('app.key'),
    'php_version' => PHP_VERSION,
    'opcache_enabled' => function_exists('opcache_get_status') ? opcache_get_status(false) : false,
    'pid' => getmypid(),
    'memory' => memory_get_usage(true),
];

echo json_encode($response, JSON_PRETTY_PRINT);