<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table = 'crm_configuraciones';
    protected $primaryKey = 'id_configuracion';
    public $timestamps = false;
    
    protected $fillable = [
        'modulo_ventas', 'modulo_clientes', 'modulo_seguridad', 'modulo_reportes',
        'nombre', 'valor', 'descripcion', 'activo', 'created_at', 'updated_at'
    ];
    
    protected $casts = [
        'modulo_ventas' => 'boolean',
        'modulo_clientes' => 'boolean',
        'modulo_seguridad' => 'boolean',
        'modulo_reportes' => 'boolean',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    // Helper para obtener valor de configuración
    public static function getValor(string $nombre, int $default = null): ?int
    {
        $config = self::where('nombre', $nombre)
            ->where('activo', 1)
            ->first();
            
        if (!$config) {
            return $default;
        }
        
        return $config->valor;
    }
    
    // Helper para verificar si una configuración está activa
    public static function isActivo(string $nombre): bool
    {
        $config = self::where('nombre', $nombre)
            ->where('activo', 1)
            ->first();
            
        return $config !== null;
    }

    /**
     * Obtener la ruta de respaldos configurada
     */
    public static function getRutaRespaldos()
    {
        $config = self::where('nombre', 'ruta_respaldo')
            ->where('activo', 1)
            ->first();
            
        if ($config && $config->valor) {
            return $config->valor;
        }
        
        // Valor por defecto
        return storage_path('app/backups');
    }
    
    /**
     * Actualizar la ruta de respaldos
     */
    public static function setRutaRespaldos($ruta)
    {
        return self::updateOrCreate(
            ['nombre' => 'ruta_respaldo'],
            [
                'modulo_seguridad' => 1,
                'descripcion' => 'Ruta donde se guardan los respaldos de base de datos',
                'valor' => $ruta,
                'activo' => 1
            ]
        );
    }
}