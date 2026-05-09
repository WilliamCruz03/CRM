<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seguimiento extends Model
{
    protected $table = 'crm_seguimientos';
    protected $primaryKey = 'id_seguimiento';
    public $timestamps = true;

    protected $fillable = [
        'id_cliente_maestro',
        'folio_cotizacion',
        'folio_pedido',
        'estado_actual',
        'motivo_no_finalizacion',
        'mensaje_cliente',
        'hora_inicio',
        'hora_fin',
        'creado_por',
        'editado_por'
    ];

    protected $casts = [
        'estado_actual' => 'integer',
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime'
    ];

    // Relaciones lógicas (sin FK)
    public function creador()
    {
        return $this->belongsTo(PersonalEmpresa::class, 'creado_por', 'id_personal_empresa');
    }

    public function mensajes()
    {
        return $this->hasMany(SeguimientoMensaje::class, 'id_seguimiento', 'id_seguimiento');
    }
}