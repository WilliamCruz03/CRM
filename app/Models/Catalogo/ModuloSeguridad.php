<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuloSeguridad extends Model
{
    protected $table = 'cat_modulo_seguridad';
    protected $primaryKey = 'id_seguridad_modulo';
    public $timestamps = true;

    protected $fillable = [
        'usuarios',
        'permisos',
        'respaldos'
    ];

    protected $casts = [
        'usuarios' => 'boolean',
        'permisos' => 'boolean',
        'respaldos' => 'boolean'
    ];

    public function permisos(): HasMany
    {
        return $this->hasMany(\App\Models\PermisoPersonal::class, 'id_seguridad_modulo', 'id_seguridad_modulo');
    }
}