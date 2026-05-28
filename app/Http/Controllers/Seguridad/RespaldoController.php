<?php
// app/Http/Controllers/Seguridad/RespaldoController.php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class RespaldoController extends Controller
{
    public function __construct()
    {
        // Aplicar middleware auth
        $this->middleware('auth');
        
        // Verificar permiso después de autenticación
        $this->middleware(function ($request, $next) {
            if (!auth()->user() || !auth()->user()->puede('seguridad', 'respaldos', 'ver')) {
                abort(403, 'No tienes permiso para acceder a esta sección');
            }
            return $next($request);
        });
    }

    /**
     * Muestra la lista de respaldos disponibles
     */
    public function index()
    {
        $backups = $this->getBackupsList();
        return view('seguridad.respaldos.index', compact('backups'));
    }

    /**
     * Genera un nuevo respaldo
     */
    public function create()
    {
        try {
            // Usar public_path en lugar de storage_path
            $backupDir = public_path('backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $fecha = date('Y-m-d_H-i-s');
            
            $dbMatriz = config('database.connections.sqlsrvM');
            $dbVentas = config('database.connections.sqlsrvV');
            
            $sqlcmd = 'C:\Program Files\SqlCmd\sqlcmd.exe';
            
            if (!file_exists($sqlcmd)) {
                $sqlcmd = 'C:\Program Files\Microsoft SQL Server\Client SDK\ODBC\170\Tools\Binn\SQLCMD.EXE';
            }
            
            // Respaldo BD Matriz
            $filenameMatriz = "backup_matriz_{$fecha}.bak";
            $backupPathMatriz = $backupDir . DIRECTORY_SEPARATOR . $filenameMatriz;
            
            $cmdMatriz = "\"$sqlcmd\" -S {$dbMatriz['host']},{$dbMatriz['port']} -U {$dbMatriz['username']} -P \"{$dbMatriz['password']}\" -Q \"BACKUP DATABASE [{$dbMatriz['database']}] TO DISK = '{$backupPathMatriz}'\"";
            
            exec($cmdMatriz, $outputMatriz, $returnCodeMatriz);
            
            // Respaldo BD Ventas
            $filenameVentas = "backup_ventas_{$fecha}.bak";
            $backupPathVentas = $backupDir . DIRECTORY_SEPARATOR . $filenameVentas;
            
            $cmdVentas = "\"$sqlcmd\" -S {$dbVentas['host']},{$dbVentas['port']} -U {$dbVentas['username']} -P \"{$dbVentas['password']}\" -Q \"BACKUP DATABASE [{$dbVentas['database']}] TO DISK = '{$backupPathVentas}'\"";
            
            exec($cmdVentas, $outputVentas, $returnCodeVentas);
            
            $matrizCreado = file_exists($backupPathMatriz);
            $ventasCreado = file_exists($backupPathVentas);
            
            if ($returnCodeMatriz === 0 && $matrizCreado && $returnCodeVentas === 0 && $ventasCreado) {
                return response()->json([
                    'success' => true,
                    'message' => 'Respaldos generados correctamente',
                    'files' => [$filenameMatriz, $filenameVentas]
                ]);
            }
            
            $errorMsg = '';
            if ($returnCodeMatriz !== 0 || !$matrizCreado) {
                $errorMsg .= 'Error en matriz: ' . implode("\n", $outputMatriz);
            }
            if ($returnCodeVentas !== 0 || !$ventasCreado) {
                $errorMsg .= ' Error en ventas: ' . implode("\n", $outputVentas);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Error al generar respaldos: ' . $errorMsg
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descarga un respaldo específico
     */
    public function download($filename)
    {
        // Buscar en public/backups
        $path = public_path('backups/' . $filename);
        
        \Log::info('Intentando descargar: ' . $path);
        
        if (!file_exists($path)) {
            \Log::error('Archivo no encontrado: ' . $path);
            abort(404, 'Archivo no encontrado');
        }
        
        return response()->download($path, $filename);
    }

    /**
     * Elimina un respaldo
     */
    public function destroy($filename)
    {
        try {
            // Buscar en public/backups
            $path = public_path('backups/' . $filename);
            
            \Log::info('Intentando eliminar: ' . $path);
            
            if (!file_exists($path)) {
                \Log::error('Archivo no encontrado: ' . $path);
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado'
                ], 404);
            }
            
            unlink($path);
            
            \Log::info('Archivo eliminado: ' . $path);
            
            return response()->json([
                'success' => true,
                'message' => 'Respaldo eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene la lista de respaldos disponibles
     */
    private function getBackupsList()
    {
        // Buscar en public/backups (donde se guardan)
        $backupPath = public_path('backups');
        
        \Log::info('Buscando respaldos en: ' . $backupPath);
        
        if (!is_dir($backupPath)) {
            \Log::warning('La carpeta de respaldos no existe: ' . $backupPath);
            return [];
        }
        
        $files = glob($backupPath . '/*.bak');
        $backups = [];
        
        foreach ($files as $file) {
            $filename = basename($file);
            $size = filesize($file);
            $date = date('Y-m-d H:i:s', filemtime($file));
            
            $backups[] = [
                'filename' => $filename,
                'size' => $this->formatSize($size),
                'date' => $date,
                'path' => $file
            ];
        }
        
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return $backups;
    }

    /**
     * Formatea el tamaño del archivo
     */
    private function formatSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        
        return $bytes . ' bytes';
    }
}