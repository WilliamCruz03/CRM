<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
            self::STATUS_INACTIVO,
        ];
    }

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
        if ($this->titulo) {
            return $this->titulo . ' ' . $nombre;
        }
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

    // Relación con enfermedades a través de la tabla pivote
    public function enfermedades()
    {
        return $this->hasMany(PatologiaAsociada::class, 'id_cliente_maestro', 'id_Cliente');
    }

    // Relación con preferencias
    public function preferencias()
    {
        return $this->hasMany(Preferencia::class, 'cliente_id', 'id_Cliente');
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
}