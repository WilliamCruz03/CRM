<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TmpCatalogo extends Model
{
    protected $table = 'tmp_catalogo';
    protected $primaryKey = 'id_tmp';
    public $timestamps = false;

    protected $fillable = [
        'ean',
        'descripcion',
        'precio',
        'creado_por',
        'fecha_creacion',
        'activo'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'fecha_creacion' => 'datetime',
        'activo' => 'boolean'
    ];

    // Generar EAN automático
    public static function generarEan(): string
    {
        $fecha = now();
        $prefijo = "EXT-{$fecha->format('Ymd')}-";
        
        $ultimo = self::where('ean', 'LIKE', "{$prefijo}%")
            ->orderBy('ean', 'desc')
            ->first();
        
        if ($ultimo) {
            $ultimoNumero = (int) substr($ultimo->ean, -4);
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }
        
        return $prefijo . str_pad($nuevoNumero, 4, '0', STR_PAD_LEFT);
    }
}