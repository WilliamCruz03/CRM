<?php
// public/server-check.php
// Sube este archivo a public/server-check.php

echo "=== ESTADO DEL SERVIDOR ===\n\n";

echo "PHP Version: " . phpversion() . "\n";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'No disponible') . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "s\n";
echo "Max Input Time: " . ini_get('max_input_time') . "s\n";
echo "\n";

// Verificar conexión a la base de datos
try {
    echo "Base de datos: ";
    $conn = new PDO("sqlsrv:Server=192.168.1.99;Database=master", '', '');
    echo "✅ Conectado\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=== SERVICIOS ===\n";
echo "Estado de sesión: " . session_status() . "\n";
echo "Configuración de sesión:\n";
print_r(ini_get_all('session'));