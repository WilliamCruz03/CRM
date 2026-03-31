<?php

namespace App\Models\Cotizaciones;

use App\Models\CatalogoGeneral;
use App\Models\Cotizaciones\CatConvenio;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CotizacionDetalle extends Model
{
    protected $table = 'crm_cotizaciones_detalle';
    protected $primaryKey = 'id_cotizacion_detalle';
    public $timestamps = false;
    
    protected $fillable = [
        'id_cotizacion', 'id_producto', 'codbar', 'descripcion',
        'cantidad', 'precio_unitario', 'descuento', 'importe',
        'id_convenio', 'id_sucursal_surtido', 'fecha_actualizacion', 'activo', 'apartado'
    ];
    
    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'importe' => 'decimal:2',
        'activo' => 'boolean',
        'apartado' => 'boolean',
    ];
    
    // Relaciones
    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'id_cotizacion', 'id_cotizacion');
    }
    
    public function producto(): BelongsTo
    {
        return $this->belongsTo(CatalogoGeneral::class, 'id_producto', 'id_catalogo_general');
    }
    
    public function convenio(): BelongsTo
    {
        return $this->belongsTo(CatConvenio::class, 'id_convenio', 'id_convenio');
    }
    
    public function sucursalSurtido(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal_surtido', 'id_sucursal');
    }
    
    
   protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($detalle) {
            if (empty($detalle->fecha_actualizacion)) {
                $detalle->fecha_actualizacion = now();
            }
        });
        
        // Evento para actualizar la fecha al modificar
        static::updating(function ($detalle) {
            $detalle->fecha_actualizacion = now();
        });
    }
}