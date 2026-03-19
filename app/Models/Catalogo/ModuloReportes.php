<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuloReportes extends Model
{
    protected $table = 'cat_modulo_reportes';
    protected $primaryKey = 'id_reportes_modulo';
    public $timestamps = true;

    protected $fillable = [
        'compras_cliente',
        'frecuencia_compra',
        'montos_promedio',
        'sucursales_preferidas',
        'cotizaciones_cliente',
        'cotizaciones_concretadas'
    ];

    protected $casts = [
        'compras_cliente' => 'boolean',
        'frecuencia_compra' => 'boolean',
        'montos_promedio' => 'boolean',
        'sucursales_preferidas' => 'boolean',
        'cotizaciones_cliente' => 'boolean',
        'cotizaciones_concretadas' => 'boolean'
    ];

    public function permisos(): HasMany
    {
        return $this->hasMany(\App\Models\PermisoPersonal::class, 'id_reportes_modulo', 'id_reportes_modulo');
    }
}