<?php

namespace App\Models\Catalogo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuloClientes extends Model
{
    protected $table = 'cat_modulo_clientes';
    protected $primaryKey = 'id_cliente_modulo';
    public $timestamps = true;

    protected $fillable = [
        'clientes',
        'enfermedades',
        'intereses'
    ];

    protected $casts = [
        'clientes' => 'boolean',
        'enfermedades' => 'boolean',
        'intereses' => 'boolean'
    ];

    public function permisos(): HasMany
    {
        return $this->hasMany(\App\Models\PermisoPersonal::class, 'id_cliente_modulo', 'id_cliente_modulo');
    }
}