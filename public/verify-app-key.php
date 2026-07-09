<?php
// public/verify-app-key.php
header('Content-Type: application/json');

// Intentar forzar la APP_KEY si no está
$appKey = 'base64:egKn4akqF+VoQKWm893L4WdtIGLpqiPot3PZhWgoIYM=';
if (empty($_ENV['APP_KEY'])) {
    $_ENV['APP_KEY'] = $appKey;
    $_SERVER['APP_KEY'] = $appKey;
    putenv('APP_KEY=' . $appKey);
}

// Cargar Laravel
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$response = [
    'env_app_key' => $_ENV['APP_KEY'] ?? 'NO DEFINIDA',
    'server_app_key' => $_SERVER['APP_KEY'] ?? 'NO DEFINIDA',
    'config_app_key' => config('app.key') ?? 'NO DEFINIDA',
    'php_version' => PHP_VERSION,
    'opcache_enabled' => function_exists('opcache_get_status') ? opcache_get_status(false)['opcache_enabled'] : false,
    'pid' => getmypid(),
];

echo json_encode($response, JSON_PRETTY_PRINT);