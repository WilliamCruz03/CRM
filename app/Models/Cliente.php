<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

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
        return $this->belongsToMany(
            Patologia::class,
            'crm_patologia_asociada',
            'id_cliente_maestro',
            'patologia'
        )->withPivot('fecha_creacion', 'id_operador', 'status');
    }

    // Relación con preferencias (si las tienes)
    public function preferencias()
    {
        return $this->hasMany(Preferencia::class, 'cliente_id', 'id_Cliente');
    }
}