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
}