<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatSalesPresentacion extends Model
{
    protected $connection = 'sqlsrvM';
    protected $table = 'cat_sales_presentacion';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $fillable = [
        'sustancia', 'concentracion', 'envasado', 'presentacion',
        'cantidad', 'unidad', 'cantidadPresentacion', 'indicacion_terapeutica_1'
    ];
    
    protected $casts = [
        'cantidad' => 'decimal:2',
        'cantidadPresentacion' => 'decimal:2',
    ];
    
    // Relación con productos (a través de tabla pivote)
    public function productos()
    {
        return $this->belongsToMany(
            CatalogoGeneral::class,
            'catalogo_presentacion',
            'id_presentacion',
            'id_catalogo_general'
        );
    }
    
    // Obtener nombre completo de la presentación
    public function getNombreCompletoAttribute(): string
    {
        $parts = [];
        if ($this->sustancia) $parts[] = $this->sustancia;
        if ($this->concentracion) $parts[] = $this->concentracion;
        if ($this->presentacion) $parts[] = $this->presentacion;
        return implode(' ', $parts);
    }
}