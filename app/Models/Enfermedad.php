<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaEnfermedad::class, 'categoria_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}