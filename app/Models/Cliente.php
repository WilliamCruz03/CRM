<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Clientes\CatPais;
use App\Models\Clientes\CatEstado;
use App\Models\Clientes\CatMunicipio;
use App\Models\Clientes\CatLocalidad;
use App\Models\Clientes\ClienteContacto;

class Cliente extends Model
{
    
    const STATUS_PROSPECTO = 'PROSPECTO';
    const STATUS_CLIENTE = 'CLIENTE';
    const STATUS_INACTIVO = 'INACTIVO';
    const STATUS_BLOQUEADO = 'BLOQUEADO';

    public static function getActiveStatuses()
    {
        return [
            self::STATUS_PROSPECTO,
            self::STATUS_CLIENTE,
        ];
    }

    protected $connection = 'sqlsrvM';
    protected $table = 'catalogo_cliente_maestro';
    protected $primaryKey = 'id_Cliente';
    public $timestamps = false; // Fecha_creacion manual

    public $incrementing = true; // Desactiva autoincrement
    protected $keyType = 'int'; // El tipo de la llave
    
    protected $fillable = [
        'sucursal_origen',
        'Nombre',
        'apPaterno',
        'apMaterno',
        'titulo',
        'status',
        'telefono1',
        'telefono2',
        'email1',
        'Domicilio',
        'Sexo',
        'FechaNac',
        'fecha_creacion',
        'id_operador',
        'pais_id',
        'estado_id',
        'municipio_id',
        'localidad_id'
    ];

    protected $casts = [
        'FechaNac' => 'date',
        'fecha_creacion' => 'datetime'
    ];

    // Accessor para nombre completo
    public function getNombreCompletoAttribute(): string
    {
        $nombre = trim($this->Nombre . ' ' . $this->apPaterno . ' ' . $this->apMaterno);
        return $nombre;
    }

    // Accessor para dirección (solo Domicilio por ahora)
    public function getDireccionCompletaAttribute(): string
    {
        return $this->Domicilio ?? 'Dirección no especificada';
    }

    // Scopes
    public function scopeActivos($query)
    {
        return $query->whereIn('status', self::getActiveStatuses());
    }

    public function scopeClientes($query)
    {
        return $query->where('status', 'CLIENTE');
    }

    public function scopeProspectos($query)
    {
        return $query->where('status', 'PROSPECTO');
    }

    public function scopeBloqueados($query)
    {
        return $query->where('status', 'BLOQUEADO');
    }

    public function scopeSoloClientes($query)
    {
        return $query->where('status', 'CLIENTE');
    }

    // Relacion de intereses
    public function intereses()
    {
        return $this->belongsToMany(Interes::class, 'crm_cliente_intereses', 'id_cliente', 'id_interes')
                    ->withPivot('fecha_asignacion', 'activo')
                    ->wherePivot('activo', 1);
    }

    
    public function getStatusAttribute($value)
    {
        return trim($value);
    }

    // Y para mantener compatibilidad con la BD, también:
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = trim($value);
    }

    // Relación con patologías asociadas
    public function patologiasAsociadas()
    {
        return $this->hasMany(PatologiaAsociada::class, 'id_cliente_maestro', 'id_Cliente');
    }

    // Para mantener compatibilidad con con codigo existente
    public function getEnfermedadesAttribute()
    {
        return $this->patologiasAsociadas;
    }

    // Accessor para dirección completa (Domicilio + Localidad)
    public function getDireccionConLocalidadAttribute(): string
    {
        $direccion = $this->Domicilio ?? '';
        $localidad = '';
        
        if ($this->localidad_id) {
            $localidadObj = CatLocalidad::find($this->localidad_id);
            if ($localidadObj) {
                $localidad = $localidadObj->nombre;
            }
        }
        
        if ($direccion && $localidad) {
            return $direccion . ', ' . $localidad;
        } elseif ($direccion) {
            return $direccion;
        } elseif ($localidad) {
            return $localidad;
        }
        
        return '';
    }

    public function contactoPreferencia()
    {
        return $this->hasOne(ClienteContacto::class, 'id_cliente', 'id_Cliente');
    }

    // Relación con País
    public function pais()
    {
        return $this->belongsTo(CatPais::class, 'pais_id', 'id');
    }

    // Relación con Estado
    public function estado()
    {
        return $this->belongsTo(CatEstado::class, 'estado_id', 'id');
    }

    // Relación con Municipio
    public function municipio()
    {
        return $this->belongsTo(CatMunicipio::class, 'municipio_id', 'id');
    }

    // Relación con Localidad
    public function localidad()
    {
        return $this->belongsTo(CatLocalidad::class, 'localidad_id', 'id');
    }
}