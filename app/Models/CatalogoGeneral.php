<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CatSalesPresentacion;

class CatalogoGeneral extends Model
{
    protected $table = 'catalogo_general';
    protected $primaryKey = 'id_catalogo_general';
    public $timestamps = false;
    
    protected $fillable = [
        'id_sucursal', 'ean', 'descripcion', 'inventario', 'costo', 'precio', 'num_familia', 'activo'
    ];
    
    protected $casts = [
        'inventario' => 'decimal:2',
        'costo' => 'decimal:2',
        'precio' => 'decimal:2',
        'activo' => 'boolean'
    ];
    
    // Relaciones
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }
    
    public function familia()
    {
        return $this->belongsTo(Cotizaciones\CatFamilia::class, 'num_familia', 'num_familia');
    }
    
    // Presentaciones de medicamentos
    public function presentaciones()
    {
        // Ajusta 'id_presentacion' y 'id_catalogo_general' según tus columnas
        return $this->belongsToMany(
            CatSalesPresentacion::class,
            'catalogo_presentacion',
            'id_catalogo_general',
        );
    }
    
    // Scopes
    public function scopeActivos($query)
    {
        return $query->where('activo', 1);
    }
    
    public function scopeConInventario($query)
    {
        return $query->where('inventario', '>', 0);
    }
    
    // Accessor para nombre completo
    public function getNombreCompletoAttribute()
    {
        return $this->descripcion . ($this->ean ? " ({$this->ean})" : '');
    }
    
    // Verificar si es un medicamento (tiene presentación asociada)
    public function getEsMedicamentoAttribute(): bool
    {
        return $this->presentaciones()->exists();
    }
    
    // Obtener sustancias activas del producto
    public function getSustanciasActivasAttribute(): string
    {
        return $this->presentaciones->pluck('sustancia')->unique()->implode(', ');
    }
}