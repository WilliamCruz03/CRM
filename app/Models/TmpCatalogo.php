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
        // Obtener el último EAN registrado (ordenado por ID descendente)
        $ultimo = self::orderBy('id_tmp', 'desc')->first();
        
        if ($ultimo && preg_match('/T(\d+)/', $ultimo->ean, $matches)) {
            $ultimoNumero = (int) $matches[1];
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }
        
        // Formato: T + 12 dígitos (rellenar con ceros a la izquierda)
        // Ejemplo: T000000000001 (13 caracteres)
        return 'T' . str_pad($nuevoNumero, 12, '0', STR_PAD_LEFT);
    }
}