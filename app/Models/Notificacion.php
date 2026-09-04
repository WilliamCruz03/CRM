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
        // Verificar si el usuario es CRM
        $esCRM = DB::connection('sqlsrv')
            ->table('permisos_granulares')
            ->where('id_personal_empresa', $userId)
            ->where('es_crm', 1)
            ->exists();
        
        return self::where(function($query) use ($userId, $esCRM) {
                $query->where('id_usuario', $userId);
                
                if ($esCRM) {
                    $query->orWhere('id_usuario', 0);
                }
            })
            ->where('leida', 0)
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