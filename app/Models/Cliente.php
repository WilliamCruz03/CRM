<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'clientes';
    
    protected $fillable = [
        'nombre',
        'apellidos',
        'email',
        'telefono',
        'calle',
        'colonia',
        'ciudad',
        'estado',
        'notas'
    ];

    protected $casts = [
        'estado' => 'string'
    ];

    /**
     * Relación: Un cliente tiene muchas preferencias
     */
    public function preferencias(): HasMany
    {
        return $this->hasMany(Preferencia::class)->where('activo', true);
    }

    /**
     * Relación: Un cliente pertenece a muchas enfermedades (Many-to-Many)
     */
    public function enfermedades(): BelongsToMany
    {
        return $this->belongsToMany(Enfermedad::class, 'cliente_enfermedad')
                    ->withPivot('notas', 'severidad')
                    ->withTimestamps();
    }

    /**
     * Accessor: Nombre completo
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellidos}";
    }

    /**
     * Accessor: Dirección completa
     */
    public function getDireccionCompletaAttribute(): string
    {
        $partes = array_filter([
            $this->calle,
            $this->colonia,
            $this->ciudad
        ]);
        
        return implode(', ', $partes) ?: 'Dirección no especificada';
    }

    /**
     * Scope: Clientes activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'Activo');
    }

    /**
     * Scope: Clientes inactivos
     */
    public function scopeInactivos($query)
    {
        return $query->where('estado', 'Inactivo');
    }
}