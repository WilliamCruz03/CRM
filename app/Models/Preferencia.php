<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Preferencia extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'preferencias';
    
    protected $fillable = [
        'cliente_id',
        'descripcion',
        'categoria',
        'fecha_registro',
        'activo'
    ];

    protected $casts = [
        'fecha_registro' => 'date',
        'activo' => 'boolean'
    ];

    /**
     * Relación: Una preferencia pertenece a un cliente
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    /**
     * Scope: Preferencias activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Accessor: Fecha formateada
     */
    public function getFechaRegistroFormateadaAttribute(): string
    {
        return $this->fecha_registro->format('d M Y');
    }
}