<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermisoGranular extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'permisos_granulares';
    protected $primaryKey = 'id_permiso_granular';
    public $timestamps = true;

    protected $fillable = [
        'id_personal_empresa',
        'modulo',
        'submodulo',
        'mostrar',
        'ver',
        'crear',
        'editar',
        'eliminar'
    ];

    protected $casts = [
        'mostrar' => 'boolean',
        'ver' => 'boolean',
        'crear' => 'boolean',
        'editar' => 'boolean',
        'eliminar' => 'boolean',
    ];

    public function personal()
    {
        return $this->belongsTo(PersonalEmpresa::class, 'id_personal_empresa', 'id_personal_empresa');
    }
}