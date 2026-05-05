<?php

namespace App\Models\AgendaContacto; 

use App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\PersonalEmpresa;

class AgendaContacto extends Model
{
    protected $table = 'agenda_contactos';
    protected $primaryKey = 'id_agenda_contacto';
    public $timestamps = false;
    
    protected $fillable = [
        'id_cliente',
        'asunto',
        'tipo',
        'estado',
        'fecha',
        'hora',
        'comentario',
        'recordatorio_minutos',
        'recordatorio_enviado',
        'creado_por',
        'activo',
        'fecha_creacion',
        'fecha_actualizacion'
    ];
    
    protected $casts = [
        'fecha' => 'date',
        'hora' => 'string',
        'recordatorio_enviado' => 'boolean',
        'activo' => 'boolean',
        'tipo' => 'integer',
        'estado' => 'integer'
    ];
    
    // Tipos de contacto
    const TIPO_LLAMADA = 1;
    const TIPO_MENSAJE = 2;
    const TIPO_CORREO = 3;
    
    // Estados
    const ESTADO_PENDIENTE = 1;
    const ESTADO_REALIZADO = 2;
    const ESTADO_CANCELADO = 3;
    
    // Obtener nombre del tipo
    public function getTipoNombreAttribute()
    {
        return match($this->tipo) {
            self::TIPO_LLAMADA => 'Llamada',
            self::TIPO_MENSAJE => 'Mensaje',
            self::TIPO_CORREO => 'Correo',
            default => 'Desconocido'
        };
    }
    
    // Obtener nombre del estado
    public function getEstadoNombreAttribute()
    {
        return match($this->estado) {
            self::ESTADO_PENDIENTE => 'Pendiente',
            self::ESTADO_REALIZADO => 'Realizado',
            self::ESTADO_CANCELADO => 'Cancelado',
            default => 'Desconocido'
        };
    }
    
    // Obtener datos del cliente desde la otra base
    public function getClienteAttribute()
    {
        return DB::connection('sqlsrvM')
            ->table('catalogo_cliente_maestro')
            ->where('id_Cliente', $this->id_cliente)
            ->first();
    }
    
    // Usuario que creó el contacto
    public function creador()
    {
        return $this->belongsTo(PersonalEmpresa::class, 'creado_por', 'id_personal_empresa');
    }
    
    // Scope para contactos pendientes próximos
    public function scopeProximosPendientes($query, $minutos = 60)
    {
        $ahora = now();
        $fechaLimite = now()->addMinutes($minutos);
        
        // Contactos cuya fecha_hora está entre ahora y el límite
        return $query->where('estado', self::ESTADO_PENDIENTE)
            ->whereRaw("CONVERT(DATETIME, CONVERT(VARCHAR(10), fecha, 120) + ' ' + CONVERT(VARCHAR(8), hora)) >= ?", [$ahora])
            ->whereRaw("CONVERT(DATETIME, CONVERT(VARCHAR(10), fecha, 120) + ' ' + CONVERT(VARCHAR(8), hora)) <= ?", [$fechaLimite])
            ->where('recordatorio_enviado', false)
            ->where('activo', true);
    }
}