<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Accion extends Model
{
    protected $table = 'cat_acciones';
    protected $primaryKey = 'id_accion';
    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function permisos(): HasMany
    {
        return $this->hasMany(\App\Models\PermisoPersonal::class, 'id_accion', 'id_accion');
    }
}