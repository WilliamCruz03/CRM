<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Enfermedad extends Model
{
    protected $table = 'enfermedades';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'categoria_id',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    /**
     * Relación: Una enfermedad pertenece a una categoría
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaEnfermedad::class, 'categoria_id');
    }

    /**
     * Relación: Una enfermedad pertenece a muchos clientes (Many-to-Many inverso)
     */
    public function clientes(): BelongsToMany
    {
        return $this->belongsToMany(Cliente::class, 'cliente_enfermedad')
                    ->withPivot('notas', 'severidad')
                    ->withTimestamps();
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}