<?php

$appKey = 'base64:egKn4akqF+VoQKWm893L4WdtIGLpqiPot3PZhWgoIYM=';

// Forzar en el entorno
$_ENV['APP_KEY'] = $appKey;
$_SERVER['APP_KEY'] = $appKey;
putenv('APP_KEY=' . $appKey);

echo "APP_KEY forzada en el entorno<br>";
echo "ENV: " . ($_ENV['APP_KEY'] ?? 'NO') . "<br>";
echo "SERVER: " . ($_SERVER['APP_KEY'] ?? 'NO') . "<br>";

// Intentar cargar Laravel
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "Laravel cargado<br>";
    echo "Config APP_KEY: " . (config('app.key') ?? 'NO') . "<br>";
    
    // Forzar en la configuración
    config(['app.key' => $appKey]);
    echo "Config forzada<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Limpiar OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reiniciado<br>";
}

echo "<h3>Ahora intenta iniciar sesión</h3>";
echo "<a href='/login'>Ir al Login</a>";