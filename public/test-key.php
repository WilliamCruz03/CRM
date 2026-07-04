<?php
// public/test-key.php

echo "<h1>Prueba de APP_KEY</h1>";

// 1. Verificar si está definida en el entorno
echo "<h2>1. Variables de Entorno</h2>";
echo "ENV APP_KEY: " . ($_ENV['APP_KEY'] ?? 'NO DEFINIDA') . "<br>";
echo "SERVER APP_KEY: " . ($_SERVER['APP_KEY'] ?? 'NO DEFINIDA') . "<br>";

// 2. Intentar cargar Laravel
echo "<h2>2. Cargando Laravel</h2>";
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "Laravel cargado correctamente<br>";
    echo "Config APP_KEY: " . (config('app.key') ?? '❌ NO DEFINIDA') . "<br>";
    
    // Verificar si hay sesión
    session_start();
    echo "Session ID: " . session_id() . "<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// 3. Verificar OPcache
echo "<h2>3. OPcache</h2>";
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reiniciado<br>";
}

echo "<h2>4. Recomendaciones</h2>";
echo "1. Verifica que el archivo .env tenga la APP_KEY<br>";
echo "2. Reinicia Apache/WAMP<br>";
echo "3. Limpia el caché del navegador<br>";