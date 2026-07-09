<?php
echo "<h1>Limpiando caché de configuración</h1>";

// 1. Limpiar OPCache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPCache reiniciado<br>";
} else {
    echo "OPCache no disponible<br>";
}

// 2. Intentar limpiar caché de Laravel
$commands = [
    'php ../artisan config:clear 2>&1',
    'php ../artisan cache:clear 2>&1',
    'php ../artisan view:clear 2>&1',
];

foreach ($commands as $cmd) {
    $output = shell_exec($cmd);
    echo "$cmd: " . ($output ?: "✅ Ejecutado<br>");
}

// 3. Verificar APP_KEY
echo "<h2>Estado de APP_KEY</h2>";
echo "ENV: " . ($_ENV['APP_KEY'] ?? 'NO DEFINIDA') . "<br>";
echo "SERVER: " . ($_SERVER['APP_KEY'] ?? 'NO DEFINIDA') . "<br>";

// 4. Verificar archivo .env
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $content = file_get_contents($envPath);
    if (preg_match('/APP_KEY=(.+)/', $content, $matches)) {
        echo ".env APP_KEY: " . trim($matches[1]) . "<br>";
    } else {
        echo ".env NO contiene APP_KEY<br>";
    }
} else {
    echo ".env no encontrado<br>";
}