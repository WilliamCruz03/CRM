<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cliente extends Model
{

    protected $table = 'catalogo_cliente_maestro';
    protected $primaryKey = 'id_Cliente';
    public $timestamps = false; // Porque usas fecha_creacion manual
    
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
        return $query->whereIn('status', ['CLIENTE', 'PROSPECTO']);
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

    // Relación con preferencias (si las tienes)
    public function preferencias()
    {
        return $this->hasMany(Preferencia::class, 'cliente_id', 'id_Cliente');
    }

    
    // En app/Models/Cliente.php, agrega:
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