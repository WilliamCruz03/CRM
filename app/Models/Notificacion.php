<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Notificacion extends Model
{
    protected $table = 'notificaciones';
    protected $primaryKey = 'id_notificacion';
    public $timestamps = false;  // Solo usamos created_at

    protected $fillable = [
        'id_usuario',
        'tipo',
        'titulo',
        'mensaje',
        'datos_extra',
        'leida',
        'creado_por',
        'created_at'
    ];

    protected $casts = [
        'leida' => 'boolean',
        'datos_extra' => 'array',
        'created_at' => 'datetime',
    ];

    // Relación con PersonalEmpresa (sin FK en la BD)
    public function usuario()
    {
        return $this->belongsTo(PersonalEmpresa::class, 'id_usuario', 'id_personal_empresa');
    }

    // Relación con quien creó
    public function creador()
    {
        return $this->belongsTo(PersonalEmpresa::class, 'creado_por', 'id_personal_empresa');
    }

    // Scopes
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', 0);
    }

    public function scopePorUsuario($query, $userId)
    {
        return $query->where('id_usuario', $userId);
    }

    // Métodos
    public function marcarComoLeida()
    {
        $this->leida = true;
        $this->save();
    }

    public static function contarNoLeidas($userId)
    {
        $user = PersonalEmpresa::find($userId);
        if (!$user) return 0;

        $esCRM = DB::connection('sqlsrv')
            ->table('permisos_granulares')
            ->where('id_personal_empresa', $userId)
            ->where('es_crm', 1)
            ->exists();

        return self::where('leida', 0)
            ->where(function ($query) use ($userId, $esCRM, $user) {
                // Personales (repartidor)
                $query->where('id_usuario', $userId);

                // Compartidas según tipo y permiso
                $tiposCompartidos = [];

                if ($user->puede('ventas', 'cotizaciones', 'ver')) {
                    $tiposCompartidos[] = 'cotizacion_alerta';
                }
                if ($user->puede('ventas', 'agenda_contactos', 'ver')) {
                    $tiposCompartidos[] = 'contacto_alerta';
                }
                if ($esCRM) {
                    $tiposCompartidos[] = 'pedido_listo';
                }

                if (!empty($tiposCompartidos)) {
                    $query->orWhere(function ($q) use ($tiposCompartidos) {
                        $q->where('id_usuario', 0)->whereIn('tipo', $tiposCompartidos);
                    });
                }
            })
            ->count();
    }

    public static function obtenerUltimas($userId, $limit = 20)
    {
        return self::where('id_usuario', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }
}