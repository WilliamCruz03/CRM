<?php
echo "<h1>Limpiando caché</h1>";

// 1. Limpiar OPCache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPCache reiniciado<br>";
}

// 2. Intentar limpiar config
shell_exec('php ../artisan config:clear 2>&1');
echo "Config cache limpiado<br>";

// 3. Verificar APP_KEY
echo "<h2>APP_KEY después de limpiar:</h2>";
echo "ENV: " . ($_ENV['APP_KEY'] ?? 'NO DEFINIDA') . "<br>";
echo "SERVER: " . ($_SERVER['APP_KEY'] ?? 'NO DEFINIDA') . "<br>";
echo "Config: " . (function_exists('config') ? config('app.key') : 'config no disponible') . "<br>";

// 4. Verificar que el LoginController pueda usar la APP_KEY
echo "<h2>Verificando LoginController:</h2>";
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    $encrypter = $app->make('Illuminate\Contracts\Encryption\Encrypter');
    echo "Encrypter disponible<br>";
    echo "Clave: " . config('app.key') . "<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}