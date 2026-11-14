<?php

namespace App\Models\Pedidos;

use App\Models\Cotizaciones\Cotizacion;
use App\Models\Cliente;
use App\Models\Sucursal;
use App\Models\PersonalEmpresa;
use App\Models\Pedidos\OperRecorridosChoferes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class OrdenPedido extends Model
{
    public const SUCURSALES_SOBRE_PEDIDO = [1, 2, 3, 4, 7];

    protected $table = 'orden_pedido';
    protected $primaryKey = 'id_pedido';
    public $timestamps = true;

    protected $fillable = [
        'id_cotizacion',
        'id_cliente',
        'id_sucursal_origen',
        'sobre_pedido',
        'anticipo_cliente',
        'folio_pedido',
        'status',
        'id_repartidor',
        'fecha_pedido',
        'fecha_encargo',
        'fecha_entrega_sugerida',
        'hora_entrega_sugerida',
        'fecha_entrega_real',
        'comentarios',
        'creado_por',
        'activo',
    ];

    protected $casts = [
        'status' => 'integer',
        'activo' => 'boolean',
        'sobre_pedido' => 'boolean',
        'anticipo_cliente' => 'decimal:2',
        'fecha_pedido' => 'datetime',
        'fecha_encargo' => 'date',
        'fecha_entrega_sugerida' => 'date',
        'hora_entrega_sugerida' => 'datetime:H:i:s',
        'fecha_entrega_real' => 'datetime',
    ];

    /**
     * Obtiene la sucursal efectiva del pedido
     * (la sucursal que tiene productos asignados en el pedido)
     */
    public function getSucursalAsignadaEfectivaAttribute()
    {
        // Buscar la primera sucursal que tiene productos asignados en este pedido
        $detalle = $this->detalles()
            ->where('se_elimino', 0)
            ->whereNotNull('id_sucursal_surtido')
            ->first();
        
        return $detalle ? $detalle->id_sucursal_surtido : null;
    }

    // RELACIONES
    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class, 'id_cotizacion', 'id_cotizacion');
    }

    public function creador()
    {
        return $this->belongsTo(PersonalEmpresa::class, 'creado_por', 'id_personal_empresa');
    }

    public function clienteDirecto()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente', 'id_Cliente');
    }

    public function sucursalOrigen()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal_origen', 'id_sucursal');
    }

    public function getClientePedidoAttribute(): ?Cliente
    {
        return $this->sobre_pedido ? $this->clienteDirecto : $this->cotizacion?->cliente;
    }

    public function scopePedidosDirectosVisibles(Builder $query, PersonalEmpresa $usuario): Builder
    {
        $query->where('activo', true)->where('sobre_pedido', true)->whereIn('status', [2, 3]);

        return (int) $usuario->sucursal_asignada === 0
            ? $query
            : $query->deOrigenVisible($usuario);
    }

    public function scopeDeOrigenVisible(Builder $query, PersonalEmpresa $usuario): Builder
    {
        return (int) $usuario->sucursal_asignada > 0
            ? $query->where('id_sucursal_origen', $usuario->sucursal_asignada)
            : $query->whereRaw('1 = 0');
    }

    public function scopeIncluyendoDirectosVisibles(Builder $query, PersonalEmpresa $usuario): Builder
    {
        return $query->where(function (Builder $pedidos) use ($usuario) {
            $pedidos->where('sobre_pedido', false)
                ->orWhere(fn (Builder $directos) => $directos->where('sobre_pedido', true)->deOrigenVisible($usuario));
        });
    }

    public function scopeDeSucursal(Builder $query, int $idSucursal): Builder
    {
        return $query->where(function (Builder $pedidos) use ($idSucursal) {
            $pedidos->whereHas('detalles', function (Builder $detalles) use ($idSucursal) {
                $detalles->where('id_sucursal_surtido', $idSucursal)->where('se_elimino', false);
            })->orWhere(function (Builder $directos) use ($idSucursal) {
                $directos->where('sobre_pedido', true)->where('id_sucursal_origen', $idSucursal);
            });
        });
    }

    public function repartidor()
    {
        return $this->belongsTo(
            PersonalEmpresa::class,
            'id_repartidor',
            'id_personal_empresa'
        )->select(['id_personal_empresa', 'Nombre', 'apPaterno', 'apMaterno']);
    }

    public function sucursales()
    {
        return $this->hasMany(OrdenPedidoSucursal::class, 'id_pedido', 'id_pedido');
    }

    public function detalles()
    {
        return $this->hasMany(OrdenPedidoDetalle::class, 'id_pedido', 'id_pedido');
    }
    
    // ATRIBUTOS
    public function getStatusNombreAttribute()
    {
        return match($this->status) {
            1 => 'No realizada',
            2 => 'En proceso',
            3 => 'Finalizada',
            default => 'Desconocido'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            1 => 'secondary',
            2 => 'warning',
            3 => 'success',
            default => 'secondary'
        };
    }

    /**
     * Verificar si el pedido tiene un recorrido activo.
     */
    public function recorridosActivos()
    {
        return $this->hasMany(OperRecorridosChoferes::class, 'folio_pedido', 'folio_pedido')
            ->where('status', 0);
    }

    /**
     * Scope para pedidos sin recorrido activo.
     */
    public function scopeSinRecorridoActivo($query)
    {
        return $query->whereDoesntHave('recorridosActivos');
    }

    /**
     * Verificar si todos los productos del pedido tienen sucursal asignada
     */
    public function verificarAsignacionSucursales(): bool
    {
        // Productos sin sucursal asignada (id_sucursal_surtido = null)
        $productosSinSucursal = $this->detalles()
            ->where('se_elimino', 0)
            ->whereNull('id_sucursal_surtido')
            ->count();
        
        return $productosSinSucursal === 0;
    }

    /**
     * Actualizar el status del pedido según el estado de las sucursales
     */
    public function actualizarStatusPorSucursales(): void
    {
        $productosSinSucursal = $this->detalles()
            ->where('se_elimino', 0)
            ->whereNull('id_sucursal_surtido')
            ->count();
        
        // Todas las sucursales del pedido que están listas
        $sucursalesListas = $this->sucursales->every(function($sucursal) {
            return $sucursal->status == 1;
        });
        
        if ($productosSinSucursal === 0) {
            if ($this->status == 2) {
                $this->status = 2;
            }
        }
        
        if ($sucursalesListas && $this->status == 2) {
            $this->status = 2;
        }
        
        $this->save();
    }
}
