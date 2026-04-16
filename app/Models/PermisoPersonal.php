<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Catalogo\ModuloClientes;
use App\Models\Catalogo\ModuloVentas;
use App\Models\Catalogo\ModuloSeguridad;
use App\Models\Catalogo\ModuloReportes;
use App\Models\Catalogo\Accion;

class PermisoPersonal extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'permisos_personal';
    protected $primaryKey = 'id_permiso';
    public $timestamps = true;

    protected $fillable = [
        'id_personal_empresa',
        'id_cliente_modulo',
        'id_ventas_modulo',
        'id_seguridad_modulo',
        'id_reportes_modulo',
        'id_accion',
        'permitido'
    ];

    protected $casts = [
        'permitido' => 'boolean'
    ];

    public function personal(): BelongsTo
    {
        return $this->belongsTo(PersonalEmpresa::class, 'id_personal_empresa', 'id_personal_empresa');
    }

    public function moduloClientes(): BelongsTo
    {
        return $this->belongsTo(ModuloClientes::class, 'id_cliente_modulo', 'id_cliente_modulo');
    }

    public function moduloVentas(): BelongsTo
    {
        return $this->belongsTo(ModuloVentas::class, 'id_ventas_modulo', 'id_ventas_modulo');
    }

    public function moduloSeguridad(): BelongsTo
    {
        return $this->belongsTo(ModuloSeguridad::class, 'id_seguridad_modulo', 'id_seguridad_modulo');
    }

    public function moduloReportes(): BelongsTo
    {
        return $this->belongsTo(ModuloReportes::class, 'id_reportes_modulo', 'id_reportes_modulo');
    }

    public function accion(): BelongsTo
    {
        return $this->belongsTo(Accion::class, 'id_accion', 'id_accion');
    }
}