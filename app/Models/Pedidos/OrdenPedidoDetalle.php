<?php

namespace App\Models\Pedidos;

use App\Models\Cotizaciones\CotizacionDetalle;
use App\Models\CatalogoGeneral;
use App\Models\Cotizaciones\CatConvenio;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Model;

class OrdenPedidoDetalle extends Model
{
    protected $table = 'orden_pedido_detalle';
    protected $primaryKey = 'id_detalle_pedido';
    public $timestamps = false;

    protected $fillable = [
        'id_pedido',
        'id_cotizacion_detalle',
        'id_producto',
        'ean',
        'cantidad',
        'precio_unitario',
        'descuento',
        'importe',
        'id_convenio',
        'id_sucursal_surtido',
        'es_agregado',
        'se_elimino',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'descuento' => 'decimal:2',
        'importe' => 'decimal:2',
        'es_agregado' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relaciones
    public function pedido()
    {
        return $this->belongsTo(OrdenPedido::class, 'id_pedido', 'id_pedido');
    }

    public function cotizacionDetalle()
    {
        return $this->belongsTo(CotizacionDetalle::class, 'id_cotizacion_detalle', 'id_cotizacion_detalle');
    }

    public function producto()
    {
        return $this->belongsTo(CatalogoGeneral::class, 'id_producto', 'id_catalogo_general');
    }

    public function convenio()
    {
        return $this->belongsTo(CatConvenio::class, 'id_convenio', 'id_convenio');
    }

    public function sucursalSurtido()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal_surtido', 'id_sucursal');
    }

    // Accessor para saber si es producto externo
  public function getEsExternoAttribute()
    {
        // Es externo si no tiene id_producto o si el EAN empieza con T
        return is_null($this->id_producto) || 
            ($this->ean && str_starts_with($this->ean, 'T'));
    }

    // Boot para manejar timestamps
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($detalle) {
            $detalle->created_at = now();
        });

        static::updating(function ($detalle) {
            $detalle->updated_at = now();
        });
    }
}