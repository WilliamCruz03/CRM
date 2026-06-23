<?php
// app/Http/Controllers/Seguridad/RespaldoController.php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
use App\Models\Configuracion;

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
        
        // Obtener ruta de respaldos desde configuración
        $this->backupPath = Configuracion::getRutaRespaldos();
        
        // Crear carpeta si no existe
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    /**
     * Muestra la lista de respaldos disponibles
     */
    public function index()
    {
        $backups = $this->getBackupsList();
        $databases = $this->getAvailableDatabases();
        
        return view('seguridad.respaldos.index', compact('backups', 'databases'));
    }

    /**
     * Genera un nuevo respaldo
     */
    public function create(Request $request)
    {
        try {
            $selectedDatabases = $request->input('databases', []);
            
            if (empty($selectedDatabases)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se seleccionó ninguna base de datos'
                ], 400);
            }
            
            $backupDir = $this->backupPath; 
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            $fecha = date('Y-m-d_H-i-s');
            $generados = [];
            $errores = [];
            
            $dbConfig = config('database.connections.sqlsrvM');
            $sqlcmd = 'C:\Program Files\SqlCmd\sqlcmd.exe';
            
            if (!file_exists($sqlcmd)) {
                $sqlcmd = 'C:\Program Files\Microsoft SQL Server\Client SDK\ODBC\170\Tools\Binn\SQLCMD.EXE';
            }
            
            foreach ($selectedDatabases as $database) {
                $filename = "backup_{$database}_{$fecha}.bak";
                $backupPath = $backupDir . DIRECTORY_SEPARATOR . $filename;
                
                $cmd = "\"$sqlcmd\" -S {$dbConfig['host']},{$dbConfig['port']} -U {$dbConfig['username']} -P \"{$dbConfig['password']}\" -Q \"BACKUP DATABASE [{$database}] TO DISK = '{$backupPath}'\"";
                
                exec($cmd, $output, $returnCode);
                
                if ($returnCode === 0 && file_exists($backupPath)) {
                    $generados[] = $filename;
                } else {
                    $errores[] = $database;
                    \Log::error("Error al respaldar {$database}: " . implode("\n", $output));
                }
            }
            
            if (empty($generados)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo generar ningún respaldo: ' . implode(', ', $errores)
                ], 500);
            }
            
            $mensaje = 'Respaldos generados correctamente: ' . implode(', ', $generados);
            if (!empty($errores)) {
                $mensaje .= '. Error en: ' . implode(', ', $errores);
            }
            
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'files' => $generados
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en respaldo: ' . $e->getMessage());
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
        $path = $this->backupPath . '/' . $filename;
        
        if (!file_exists($path)) {
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
            // Buscar en storage/backups
            $path = $this->backupPath . '/' . $filename;
            
            if (!file_exists($path)) {
                \Log::error('Archivo no encontrado: ' . $path);
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado'
                ], 404);
            }
            
            unlink($path);
            
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
     * Obtiene la lista de bases de datos disponibles en el servidor
     */
    private function getAvailableDatabases()
    {
        try {
            // Usar la conexión sqlsrvM como base para consultar sys.databases
            $databases = DB::connection('sqlsrvM')->select("
                SELECT name 
                FROM sys.databases 
                WHERE database_id > 4  -- Excluye system DBs (master, tempdb, model, msdb)
                AND name NOT IN ('reportserver', 'reportservertempdb')  -- Excluir BD de reportes
                ORDER BY name
            ");
            
            return collect($databases)->pluck('name')->toArray();
            
        } catch (\Exception $e) {
            \Log::error('Error al obtener lista de bases de datos: ' . $e->getMessage());
            return ['fp_central_crm', 'fp_central_matriz', 'fp_central_ventas']; // Fallback a las conocidas
        }
    }

    /**
     * Obtiene la lista de respaldos disponibles
     */
    private function getBackupsList()
    {
        $backupPath = $this->backupPath;
        
        if (!is_dir($backupPath)) {
            // Crear la carpeta si no existe
            mkdir($backupPath, 0755, true);
        }
        
        $files = glob($backupPath . '/*.bak');
        $backups = [];
        
        foreach ($files as $file) {
            $filename = basename($file);
            $size = filesize($file);
            $date = date('Y-m-d H:i:s', filemtime($file));
            
            // Extraer nombre de la base de datos del nombre del archivo
            // Formato: backup_NOMBRE_BD_FECHA.bak
            preg_match('/backup_(.*?)_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.bak/', $filename, $matches);
            $databaseName = $matches[1] ?? 'desconocida';
            
            $backups[] = [
                'filename' => $filename,
                'database' => $databaseName,
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