<?php
// app/Http/Controllers/Seguridad/RespaldoController.php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class RespaldoController extends Controller
{
    public function __construct()
    {
        // Verificar permiso de ver respaldos
        if (!auth()->user()->puede('seguridad', 'respaldos', 'ver')) {
            abort(403, 'No tienes permiso para acceder a esta sección');
        }
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
            Artisan::call('backup:run', [
                '--only-db' => true,
            ]);
            
            $output = Artisan::output();
            
            return response()->json([
                'success' => true,
                'message' => 'Respaldo generado correctamente',
                'output' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar respaldo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descarga un respaldo específico
     */
    public function download($filename)
    {
        $path = storage_path('app/' . config('backup.backup.name') . '/' . $filename);
        
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
            $path = storage_path('app/' . config('backup.backup.name') . '/' . $filename);
            
            if (file_exists($path)) {
                unlink($path);
                return response()->json([
                    'success' => true,
                    'message' => 'Respaldo eliminado correctamente'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Archivo no encontrado'
            ], 404);
        } catch (\Exception $e) {
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
        $backupPath = storage_path('app/' . config('backup.backup.name'));
        
        if (!is_dir($backupPath)) {
            return [];
        }
        
        $files = glob($backupPath . '/*.zip');
        $backups = [];
        
        foreach ($files as $file) {
            $filename = basename($file);
            $size = filesize($file);
            $date = date('Y-m-d H:i:s', filemtime($file));
            
            // Extraer fecha del nombre del archivo
            $backupDate = $date;
            
            $backups[] = [
                'filename' => $filename,
                'size' => $this->formatSize($size),
                'date' => $backupDate,
                'path' => $file
            ];
        }
        
        // Ordenar por fecha descendente
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
