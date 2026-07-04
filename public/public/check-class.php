<?php
// public/check-class.php
echo "<h1>Verificando Clases</h1>";

// 1. Verificar que el archivo existe
$filePath = __DIR__ . '/../app/Http/Middleware/CheckUserStatus.php';
echo "<h2>Archivo:</h2>";
echo "Path: " . $filePath . "<br>";
echo "Existe: " . (file_exists($filePath) ? '✅ SÍ' : '❌ NO') . "<br>";

if (file_exists($filePath)) {
    echo "Tamaño: " . filesize($filePath) . " bytes<br>";
    echo "Contenido (primeras líneas):<br>";
    echo "<pre>" . htmlspecialchars(substr(file_get_contents($filePath), 0, 500)) . "</pre>";
}

// 2. Verificar que la clase existe
echo "<h2>Clase:</h2>";
if (class_exists('App\Http\Middleware\CheckUserStatus')) {
    echo "✅ La clase App\Http\Middleware\CheckUserStatus EXISTE<br>";
} else {
    echo "❌ La clase NO EXISTE<br>";
    
    // Intentar cargar manualmente
    echo "Intentando cargar manualmente...<br>";
    require_once $filePath;
    if (class_exists('App\Http\Middleware\CheckUserStatus')) {
        echo "✅ Cargada manualmente con éxito<br>";
    } else {
        echo "❌ Error al cargar manualmente<br>";
    }
}

// 3. Verificar alias
echo "<h2>Alias 'check.activo':</h2>";
try {
    $kernel = app('Illuminate\Contracts\Http\Kernel');
    $aliases = $kernel->getMiddlewareAliases();
    echo "Alias registrados:<br>";
    print_r($aliases);
} catch (Exception $e) {
    echo "Error al obtener aliases: " . $e->getMessage() . "<br>";
}