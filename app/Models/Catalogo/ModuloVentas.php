<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuloVentas extends Model
{
    protected $table = 'cat_modulo_ventas';
    protected $primaryKey = 'id_ventas_modulo';
    public $timestamps = true;

    protected $fillable = [
        'cotizaciones',
        'pedidos_anticipo',
        'seguimiento_ventas',
        'seguimiento_cotizaciones',
        'agenda_contactos'
    ];

    protected $casts = [
        'cotizaciones' => 'boolean',
        'pedidos_anticipo' => 'boolean',
        'seguimiento_ventas' => 'boolean',
        'seguimiento_cotizaciones' => 'boolean',
        'agenda_contactos' => 'boolean'
    ];

    public function permisos(): HasMany
    {
        return $this->hasMany(\App\Models\PermisoPersonal::class, 'id_ventas_modulo', 'id_ventas_modulo');
    }
}