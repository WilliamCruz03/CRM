<?php

// Forzar APP_KEY inmediatamente
$appKey = 'base64:egKn4akqF+VoQKWm893L4WdtIGLpqiPot3PZhWgoIYM=';
$_ENV['APP_KEY'] = $appKey;
$_SERVER['APP_KEY'] = $appKey;
putenv('APP_KEY=' . $appKey);

// Cargar Laravel
require_once __DIR__ . '/../public/index.php';

echo "APP_KEY forzada: " . $appKey . "<br>";
echo "ENV APP_KEY: " . ($_ENV['APP_KEY'] ?? 'NO') . "<br>";
echo "Config APP_KEY: " . (config('app.key') ?? 'NO') . "<br>";

// Limpiar cache de OPcache si es posible
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reiniciado<br>";
}

echo "Estado actual de la sesión:<br>";
if (auth()->check()) {
    echo "Usuario autenticado: " . auth()->user()->email . "<br>";
} else {
    echo "No hay usuario autenticado<br>";
}