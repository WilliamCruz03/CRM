<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Cotizaciones\CotizacionDetalle;
use App\Models\Pedidos\OrdenPedidoDetalle;

class TmpCatalogo extends Model
{
    protected $table = 'tmp_catalogo';
    protected $primaryKey = 'id_tmp';
    public $timestamps = false;

    protected $fillable = [
        'ean',
        'descripcion',
        'precio',
        'creado_por',
        'fecha_creacion',
        'activo'
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'fecha_creacion' => 'datetime',
        'activo' => 'boolean'
    ];

    // Generar EAN automático
    public static function generarEan(): string
    {
        // Buscar el último EAN que empiece con 'T' Y que tenga exactamente 13 caracteres
        $ultimoT = self::where('ean', 'LIKE', 'T%')
            ->whereRaw('LEN(ean) = 13')
            ->orderBy('ean', 'desc')
            ->first();
        
        if ($ultimoT && preg_match('/T(\d+)/', $ultimoT->ean, $matches)) {
            $ultimoNumero = (int) $matches[1];
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            // Si no hay EANs válidos de 13 caracteres, empezar desde 1
            $nuevoNumero = 1;
        }
        
        return 'T' . str_pad($nuevoNumero, 12, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener el EAN final después de marcar como listo
     * Si el EAN es temporal (empieza con 'T'), actualizarlo al EAN real
     */
    public static function actualizarEanFinal($tmpEan, $eanReal)
    {
        // Buscar todos los pedidos que tengan este EAN temporal
        $pedidosDetalles = OrdenPedidoDetalle::where('ean', $tmpEan)
            ->orWhere('codbar', $tmpEan)
            ->get();
        
        foreach ($pedidosDetalles as $detalle) {
            $detalle->ean = $eanReal;
            $detalle->codbar = $eanReal;
            $detalle->save();
        }
        
        // También actualizar cotizaciones si es necesario
        $cotizacionesDetalles = CotizacionDetalle::where('codbar', $tmpEan)->get();
        foreach ($cotizacionesDetalles as $detalle) {
            $detalle->codbar = $eanReal;
            $detalle->save();
        }
        
        // Marcar el temporal como inactivo
        $tmpProducto = TmpCatalogo::where('ean', $tmpEan)->first();
        if ($tmpProducto) {
            $tmpProducto->activo = 0;
            $tmpProducto->save();
        }
    }
}