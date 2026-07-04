<?php
// public/diagnostico.php

echo "<h1>Diagnóstico de Sesión - SIN Laravel</h1>";
echo "<hr>";

// 1. Verificar APP_KEY en diferentes lugares
echo "<h2>1. APP_KEY</h2>";
echo "<strong>ENV:</strong> " . ($_ENV['APP_KEY'] ?? 'NO DEFINIDA') . "<br>";
echo "<strong>SERVER:</strong> " . ($_SERVER['APP_KEY'] ?? 'NO DEFINIDA') . "<br>";

// 2. Verificar archivo .env directamente
echo "<h2>2. Archivo .env</h2>";
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    echo "Archivo .env encontrado en: " . realpath($envPath) . "<br>";
    $envContent = file_get_contents($envPath);
    if (preg_match('/APP_KEY=(.+)/', $envContent, $matches)) {
        echo "APP_KEY en .env: " . trim($matches[1]) . "<br>";
    } else {
        echo "APP_KEY NO ENCONTRADA en .env<br>";
    }
    
    // Verificar permisos
    echo "Permisos: " . substr(sprintf('%o', fileperms($envPath)), -4) . "<br>";
} else {
    echo "Archivo .env NO ENCONTRADO en: " . $envPath . "<br>";
}

// 3. Verificar sesión de PHP (NO Laravel)
echo "<h2>3. Sesión PHP (nativa)</h2>";
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "Session Data:<pre>";
print_r($_SESSION);
echo "</pre>";

// 4. Verificar archivos de sesión
echo "<h2>4. Archivos de Sesión (storage/framework/sessions)</h2>";
$sessionPaths = [
    __DIR__ . '/../storage/framework/sessions',
    __DIR__ . '/../storage/framework/sessions/database',
    __DIR__ . '/../../storage/framework/sessions'
];

$found = false;
foreach ($sessionPaths as $path) {
    if (is_dir($path)) {
        $found = true;
        echo "Carpeta encontrada: " . $path . "<br>";
        $files = glob($path . '/*');
        echo "Total archivos: " . count($files) . "<br>";
        
        // Mostrar últimos 5 archivos
        if (count($files) > 0) {
            echo "Últimos 5 archivos:<br>";
            $sorted = array_slice($files, -5);
            foreach ($sorted as $file) {
                $size = round(filesize($file) / 1024, 2);
                echo "  - " . basename($file) . " ({$size} KB, " . date('H:i:s', filemtime($file)) . ")<br>";
            }
            
            // Verificar si algún archivo coincide con el session_id actual
            $currentSessionFile = $path . '/' . session_id();
            if (file_exists($currentSessionFile)) {
                echo "¡ARCHIVO DE SESIÓN ACTIVO ENCONTRADO!<br>";
                echo "Contenido: <pre>";
                echo htmlspecialchars(file_get_contents($currentSessionFile));
                echo "</pre>";
            } else {
                echo "No se encontró archivo para el session_id actual: " . session_id() . "<br>";
                echo "Buscando archivos que contengan el session_id...<br>";
                foreach ($files as $file) {
                    $content = file_get_contents($file);
                    if (strpos($content, session_id()) !== false) {
                        echo "Encontrado en: " . basename($file) . "<br>";
                    }
                }
            }
        }
        break;
    }
}

if (!$found) {
    echo "❌ No se encontró carpeta de sesiones en ninguna ubicación.<br>";
}

// 5. Verificar OPcache
echo "<h2>5. Estado de OPcache</h2>";
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status(false);
    echo "OPcache habilitado: " . ($status['opcache_enabled'] ? 'SÍ' : 'NO') . "<br>";
    if ($status['opcache_enabled']) {
        echo "Memoria usada: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB<br>";
        echo "Memoria total: " . round($status['memory_usage']['total_memory'] / 1024 / 1024, 2) . " MB<br>";
        echo "Archivos cacheados: " . $status['opcache_statistics']['num_cached_scripts'] . "<br>";
    }
} else {
    echo "OPcache no disponible<br>";
}

// 6. Información del servidor
echo "<h2>6. Información del Servidor</h2>";
echo "PID del proceso: " . getmypid() . "<br>";
echo "Memoria usada: " . round(memory_get_usage(true) / 1024 / 1024, 2) . " MB<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Fecha/Hora: " . date('Y-m-d H:i:s') . "<br>";

// 7. Verificar archivo de configuración de Laravel
echo "<h2>7. Configuración de Laravel (cacheada)</h2>";
$configPath = __DIR__ . '/../bootstrap/cache/config.php';
if (file_exists($configPath)) {
    echo "Config cacheada encontrada: " . realpath($configPath) . "<br>";
    echo "Última modificación: " . date('Y-m-d H:i:s', filemtime($configPath)) . "<br>";
    
    // Buscar APP_KEY en el archivo cacheado
    $configContent = file_get_contents($configPath);
    if (preg_match('/\'key\'\s*=>\s*\'([^\']+)\'/', $configContent, $matches)) {
        echo "APP_KEY en cache: " . $matches[1] . "<br>";
    } else {
        echo "No se encontró APP_KEY en archivo cacheado<br>";
    }
} else {
    echo "No hay archivo de configuración cacheado<br>";
}

// 8. Check de carga de variables de entorno
echo "<h2>8. Variables de Entorno del Sistema</h2>";
echo "<pre>";
print_r(array_filter($_ENV, function($key) {
    return strpos($key, 'APP_') === 0 || strpos($key, 'DB_') === 0 || $key === 'PHP_SELF';
}, ARRAY_FILTER_USE_KEY));
echo "</pre>";

echo "<hr>";
echo "<p style='color: #666;'>Diagnóstico completado. Revisa la información de APP_KEY y el archivo de sesión.</p>";