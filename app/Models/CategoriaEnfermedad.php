<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaEnfermedad extends Model
{
    protected $table = 'categoria_enfermedades';
    
    protected $fillable = [
        'nombre',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function enfermedades(): HasMany
    {
        return $this->hasMany(Enfermedad::class, 'categoria_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}