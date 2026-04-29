<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Pedidos\OrdenPedido;
use App\Models\Pedidos\OrdenPedidoSucursal;
use App\Models\Cotizaciones\Cotizacion;
use App\Models\Cotizaciones\CotizacionDetalle;
use App\Models\Sucursal;
use App\Models\PersonalEmpresa;
use App\Models\CatalogoGeneral;
use App\Models\Pedidos\OrdenPedidoDetalle;
use App\Models\TmpCatalogo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class PedidoController extends Controller
{
    /**
     * Mostrar la lista de pedidos.
     */
    public function index(): View
    {
        $puedeMostrar = auth()->user()->puede('ventas', 'pedidos_anticipo', 'mostrar');
        $puedeVer = auth()->user()->puede('ventas', 'pedidos_anticipo', 'ver');
        
        if (!$puedeMostrar && !$puedeVer) {
            abort(403, 'No tienes permiso para acceder a este módulo');
        }
        
        $sucursalAsignada = auth()->user()->sucursal_asignada ?? 0;
        $usuarioId = auth()->id();
        
        $permisos = [
            'mostrar' => $puedeMostrar,
            'ver' => $puedeVer,
            'crear' => auth()->user()->puede('ventas', 'pedidos_anticipo', 'crear'),
            'editar' => auth()->user()->puede('ventas', 'pedidos_anticipo', 'editar'),
            'eliminar' => auth()->user()->puede('ventas', 'pedidos_anticipo', 'eliminar'),
        ];
        
        $pedidos = collect();
        
        // Verificar si el usuario es repartidor (está en rh_personal_servicios_domicilio)
        $esRepartidor = DB::connection('sqlsrvM')->table('rh_personal_servicios_domicilio')
            ->where('id_personal', $usuarioId)
            ->exists();

        if ($puedeVer) {
            $query = OrdenPedido::with([
                'cotizacion.cliente', 
                'cotizacion.sucursalAsignada', 
                'sucursales.sucursal',
                'repartidor'
            ])->where('activo', 1);
            
            if ($esRepartidor) {
                // Repartidor: ver solo pedidos asignados a él
                $query->where('id_repartidor', $usuarioId);
            } elseif ($sucursalAsignada > 0) {
                // Usuario de sucursal: filtrar por sucursal
                $query->whereHas('detalles', function($q) use ($sucursalAsignada) {
                    $q->where('id_sucursal_surtido', $sucursalAsignada)
                    ->where('se_elimino', 0);
                });
            }
            // CRM (sucursal 0) ve todos sin filtro
            
            $pedidos = $query->orderBy('id_pedido', 'desc')->paginate(15);
        }
        return view('ventas.pedidos.index', compact('pedidos', 'permisos', 'sucursalAsignada', 'esRepartidor'));
    }
    
    /**
     * Mostrar el pedido especificado.
     */
    public function show(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'pedidos_anticipo', 'ver')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        $sucursalAsignada = auth()->user()->sucursal_asignada ?? 0;

        if ($sucursalAsignada > 0) {
            $tieneProductos = OrdenPedidoDetalle::where('id_pedido', $id)
                ->where('id_sucursal_surtido', $sucursalAsignada)
                ->where('se_elimino', 0)
                ->exists();
            
            if (!$tieneProductos) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes productos asignados en este pedido para tu sucursal'
                ], 403);
            }
        }
        
        $pedido = OrdenPedido::with([
            'cotizacion' => function($q) {
                $q->with(['cliente', 'fase', 'sucursalAsignada']);
            },
            'cotizacion.detalles' => function($q) use ($sucursalAsignada) {
                // Para cotización: si usuario tiene sucursal, filtrar
                if ($sucursalAsignada > 0) {
                    $q->where(function($sq) use ($sucursalAsignada) {
                        $sq->where('id_sucursal_surtido', $sucursalAsignada)
                        ->orWhere('es_externo', 1);
                    });
                }
            },
            'cotizacion.detalles.sucursalSurtido',
            'detalles' => function($q) use ($sucursalAsignada) {
                // Para pedido_detalle: solo productos no eliminados
                $q->where('se_elimino', 0);
                
                // Si usuario tiene sucursal asignada, filtrar por ella
                if ($sucursalAsignada > 0) {
                    $q->where('id_sucursal_surtido', $sucursalAsignada);
                }
            },
            'detalles.sucursalSurtido',
            'sucursales.sucursal',
            'creador',
            'repartidor'
        ])->findOrFail($id);
        
        // Procesar detalles para la vista (priorizar orden_pedido_detalle)
        $detallesParaMostrar = [];
        
        if ($pedido->detalles->isNotEmpty()) {
            // Usar los detalles guardados en orden_pedido_detalle
            foreach ($pedido->detalles as $detalle) {
                // Determinar si es externo (id_producto = null o ean empieza con T)
                $esExterno = is_null($detalle->id_producto) || 
                            ($detalle->ean && str_starts_with($detalle->ean, 'T'));
                
                if ($esExterno) {
                    $productoExterno = TmpCatalogo::where('ean', $detalle->ean)->first();
                    $detallesParaMostrar[] = (object)[
                        'id_detalle' => $detalle->id_detalle_pedido,
                        'codbar' => $detalle->ean,
                        'descripcion' => $productoExterno->descripcion ?? 'Producto externo',
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'descuento' => $detalle->descuento,
                        'importe' => $detalle->importe,
                        'sucursal_surtido' => $detalle->sucursalSurtido,
                        'es_externo' => true
                    ];
                } else {
                    $producto = CatalogoGeneral::find($detalle->id_producto);
                    $detallesParaMostrar[] = (object)[
                        'id_detalle' => $detalle->id_detalle_pedido,
                        'codbar' => $producto->ean ?? $detalle->ean,
                        'descripcion' => $producto->descripcion ?? 'Producto no disponible',
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'descuento' => $detalle->descuento,
                        'importe' => $detalle->importe,
                        'sucursal_surtido' => $detalle->sucursalSurtido,
                        'es_externo' => false
                    ];
                }
            }
        } else {
            // Fallback: usar detalles de cotización (primera vez)
            foreach ($pedido->cotizacion->detalles as $detalle) {
                $esExterno = $detalle->es_externo == 1;
                
                if ($esExterno) {
                    $productoExterno = TmpCatalogo::find($detalle->id_producto);
                    $detallesParaMostrar[] = (object)[
                        'id_detalle' => $detalle->id_cotizacion_detalle,
                        'codbar' => $detalle->codbar,
                        'descripcion' => $detalle->descripcion,
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'descuento' => $detalle->descuento,
                        'importe' => $detalle->importe,
                        'sucursal_surtido' => $detalle->sucursalSurtido,
                        'es_externo' => true
                    ];
                } else {
                    $producto = CatalogoGeneral::find($detalle->id_producto);
                    $detallesParaMostrar[] = (object)[
                        'id_detalle' => $detalle->id_cotizacion_detalle,
                        'codbar' => $detalle->codbar,
                        'descripcion' => $detalle->descripcion,
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'descuento' => $detalle->descuento,
                        'importe' => $detalle->importe,
                        'sucursal_surtido' => $detalle->sucursalSurtido,
                        'es_externo' => false
                    ];
                }
            }
        }
        
        // Enriquecer con stock actual solo para productos normales con sucursal
        foreach ($detallesParaMostrar as $detalle) {
            if (!$detalle->es_externo && $detalle->sucursal_surtido) {
                $productoStock = CatalogoGeneral::where('ean', $detalle->codbar)
                    ->where('id_sucursal', $detalle->sucursal_surtido->id_sucursal)
                    ->first();
                $detalle->stock_actual = $productoStock ? $productoStock->inventario : 0;
            } else {
                $detalle->stock_actual = null;
            }
        }
        
        // Reemplazar los detalles originales con los procesados
        $pedido->detalles_procesados = $detallesParaMostrar;
        
        // Calcular si el usuario puede marcar como listo
        $pedido->sucursal_usuario = $sucursalAsignada;
        $pedido->usuario_puede_marcar_listo = $this->usuarioPuedeMarcarListo($pedido);
        
        return response()->json([
            'success' => true,
            'data' => $pedido
        ]);
    }

    /**
     * Obtener datos del pedido para edición
     */
    public function edit(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'pedidos_anticipo', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        $sucursalAsignada = auth()->user()->sucursal_asignada ?? 0;
        
        $pedido = OrdenPedido::with([
            'cotizacion' => function($q) {
                $q->with(['cliente', 'fase', 'sucursalAsignada']);
            },
            'cotizacion.detalles' => function($q) use ($sucursalAsignada) {
                if ($sucursalAsignada > 0) {
                    $q->where(function($sq) use ($sucursalAsignada) {
                        $sq->where('id_sucursal_surtido', $sucursalAsignada)
                        ->orWhere('es_externo', 1);
                    });
                }
            },
            'cotizacion.detalles.sucursalSurtido',
            'detalles',
            'detalles.sucursalSurtido',
            'sucursales.sucursal',
            'creador',
            'repartidor'
        ])->findOrFail($id);         

        // Enriquecer detalles con información del producto (normal o externo)
        foreach ($pedido->detalles as $detalle) {
            if ($detalle->es_externo) {
                // Cargar desde tmp_catalogo usando el EAN
                $detalle->producto_externo = TmpCatalogo::where('ean', $detalle->ean)->first();
                $detalle->nombre = $detalle->producto_externo->descripcion ?? 'Producto externo';
                $detalle->codbar = $detalle->ean;
                $detalle->num_familia = 'EXT';
                $detalle->inventario_disponible = 999;
            } else {
                // Cargar desde catalogo_general
                $producto = CatalogoGeneral::find($detalle->id_producto);
                if ($producto) {
                    $detalle->nombre = $producto->descripcion;
                    $detalle->codbar = $producto->ean ?? '';
                    $detalle->num_familia = $producto->num_familia ?? '';
                    $detalle->inventario_disponible = $producto->inventario ?? 0;
                } else {
                    // Si no se encuentra el producto (posiblemente fue eliminado)
                    $detalle->nombre = 'Producto no disponible';
                    $detalle->codbar = $detalle->ean ?? '';
                    $detalle->num_familia = '';
                    $detalle->inventario_disponible = 0;
                }
            }
            
            // Calcular stock actual si tiene sucursal asignada
            if (!$detalle->es_externo && $detalle->id_sucursal_surtido) {
                $productoStock = CatalogoGeneral::where('id_catalogo_general', $detalle->id_producto)
                    ->where('id_sucursal', $detalle->id_sucursal_surtido)
                    ->first();
                $detalle->stock_actual = $productoStock ? $productoStock->inventario : 0;
            } else {
                $detalle->stock_actual = null;
            }
        }
        
        // Si no hay detalles en orden_pedido_detalle, usar los de cotización (primera vez)
        if ($pedido->detalles->isEmpty()) {
            $detallesProcesados = [];
            
            foreach ($pedido->cotizacion->detalles as $detalle) {
                if ($detalle->es_externo == 1) {
                    $productoExterno = TmpCatalogo::find($detalle->id_producto);
                    $detalle->nombre = $productoExterno->descripcion ?? 'Producto externo';
                    $detalle->codbar = $productoExterno->ean ?? '';
                    $detalle->ean = $productoExterno->ean ?? '';
                    $detalle->es_externo = 1;
                    $detalle->inventario_disponible = 999;
                } else {
                    $producto = CatalogoGeneral::find($detalle->id_producto);
                    $detalle->nombre = $producto->descripcion ?? 'Producto no encontrado';
                    $detalle->codbar = $producto->ean ?? '';
                    $detalle->ean = $producto->ean ?? '';
                    $detalle->num_familia = $producto->num_familia ?? '';
                    $detalle->inventario_disponible = $producto->inventario ?? 0;
                    $detalle->es_externo = 0;
                }
                $detallesProcesados[] = $detalle;  // Agregar al array
            }
            
            // Asignar los detalles procesados al pedido
            $pedido->detalles = $detallesProcesados;
        }

        // Calcular si se debe mostrar la sección de asignación de repartidor
        $sucursalesPendientes = $pedido->sucursales->contains('status', 0);
        $todasSucursalesListas = $pedido->sucursales->isNotEmpty() && !$sucursalesPendientes;
        $mostrarAsignacionRepartidor = ($sucursalAsignada == 0 && $pedido->status == 2 && $todasSucursalesListas);

        // Agregar esta propiedad al objeto $pedido
        $pedido->mostrar_asignacion_repartidor = $mostrarAsignacionRepartidor;

        // ============================================
        // FORMATEAR FECHA Y HORA PARA EL FRONTEND
        // ============================================
        if ($pedido->fecha_entrega_sugerida) {
            try {
                // Usar Carbon para parsear la fecha
                $fecha = \Carbon\Carbon::parse($pedido->fecha_entrega_sugerida);
                $pedido->fecha_entrega_sugerida = $fecha->format('Y-m-d');
            } catch (\Exception $e) {
                // Si hay error, dejar como null
                $pedido->fecha_entrega_sugerida = null;
            }
        } else {
            $pedido->fecha_entrega_sugerida = null;
        }

        if ($pedido->hora_entrega_sugerida) {
            try {
                $hora = \Carbon\Carbon::parse($pedido->hora_entrega_sugerida);
                $pedido->hora_entrega_sugerida = $hora->format('H:i');
            } catch (\Exception $e) {
                $pedido->hora_entrega_sugerida = null;
            }
        } else {
            $pedido->hora_entrega_sugerida = null;
        }

        return response()->json([
            'success' => true,
            'data' => $pedido
        ]);
    }

    /**
     * Actualizar el pedido especificado.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'pedidos_anticipo', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        try {
            DB::beginTransaction();

            $pedido = OrdenPedido::findOrFail($id);

            // Validar que el pedido esté en proceso
            if ($pedido->status != 2) {
                return response()->json(['success' => false, 'message' => 'El pedido debe estar en proceso para editarlo'], 400);
            }

            // Validar datos
            $validated = $request->validate([
                'comentarios' => 'nullable|string|max:500',
                'id_repartidor' => 'nullable|exists:sqlsrvM.personal_empresa,id_personal_empresa',
                'id_convenio_general' => 'nullable|exists:sqlsrvM.cat_convenios,id_convenio',
                'productos' => 'required|array|min:1',
                'productos.*.id_detalle_pedido' => 'nullable|integer',
                'productos.*.id_producto' => 'nullable|integer',
                'productos.*.ean' => 'nullable|string|max:13',
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.precio_unitario' => 'required|numeric|min:0',
                'productos.*.descuento' => 'nullable|numeric|min:0|max:100',
                'productos.*.id_convenio' => 'nullable|exists:sqlsrvM.cat_convenios,id_convenio',
                'fecha_entrega_sugerida' => 'nullable|date',
                'hora_entrega_sugerida' => 'nullable|date_format:H:i',
                'productos.*.id_sucursal_surtido' => 'nullable|integer',
                'productos.*.es_agregado' => 'boolean',
                'productos.*.id_cotizacion_detalle' => 'nullable|integer',
            ]);

            // Actualizar datos básicos del pedido
            $pedido->comentarios = $validated['comentarios'] ?? null;
            $pedido->fecha_entrega_sugerida = $validated['fecha_entrega_sugerida'] ?? null;
            $pedido->hora_entrega_sugerida = $validated['hora_entrega_sugerida'] ?? null;
            $pedido->id_repartidor = $validated['id_repartidor'] ?? null;
            $pedido->save();

            // Actualizar sucursal de cada producto
            $sucursalesAfectadas = [];
            
            foreach ($validated['productos'] as $productoData) {
            if (!empty($productoData['id_detalle_pedido'])) {
                // Actualizar detalle existente
                $detalle = OrdenPedidoDetalle::find($productoData['id_detalle_pedido']);
                if ($detalle && $detalle->id_pedido == $id) {
                    $sucursalOriginal = $detalle->id_sucursal_surtido;
                    $sucursalNueva = $productoData['id_sucursal_surtido'] ?? null;
                    
                    if ($sucursalOriginal != $sucursalNueva) {
                        $detalle->update([
                            'id_sucursal_surtido' => $sucursalNueva,
                            'updated_at' => now()
                        ]);
                        
                        if ($sucursalOriginal) $sucursalesAfectadas[$sucursalOriginal] = true;
                        if ($sucursalNueva) $sucursalesAfectadas[$sucursalNueva] = true;
                    }
                }
            } else {
                // Crear nuevo detalle en orden_pedido_detalle (viene de cotización sin editar)
                $nuevoDetalle = OrdenPedidoDetalle::create([
                    'id_pedido' => $id,
                    'id_cotizacion_detalle' => $productoData['id_cotizacion_detalle'] ?? null,
                    'id_producto' => $productoData['id_producto'] ?? null,
                    'ean' => $productoData['ean'] ?? null,
                    'cantidad' => $productoData['cantidad'],
                    'precio_unitario' => $productoData['precio_unitario'],
                    'descuento' => $productoData['descuento'] ?? 0,
                    'importe' => $productoData['cantidad'] * $productoData['precio_unitario'] * (1 - ($productoData['descuento'] ?? 0) / 100),
                    'id_convenio' => $productoData['id_convenio'] ?? null,
                    'id_sucursal_surtido' => $productoData['id_sucursal_surtido'] ?? null,
                    'es_agregado' => false,
                    'se_elimino' => 0,
                    'created_at' => now()
                ]);
                
                // Marcar sucursal afectada
                if ($productoData['id_sucursal_surtido']) {
                    $sucursalesAfectadas[$productoData['id_sucursal_surtido']] = true;
                }
            }
        }

            // Actualizar sucursales en orden_pedido_sucursal
            $sucursalesEnUso = OrdenPedidoDetalle::where('id_pedido', $id)
                ->where('se_elimino', 0)
                ->whereNotNull('id_sucursal_surtido')
                ->distinct()
                ->pluck('id_sucursal_surtido')
                ->toArray();

            // Eliminar sucursales que ya no están en uso
            OrdenPedidoSucursal::where('id_pedido', $id)
                ->whereNotIn('id_sucursal', $sucursalesEnUso)
                ->delete();

            // Reiniciar solo las sucursales afectadas que estaban en Listo
            foreach ($sucursalesEnUso as $sucursalId) {
                $sucursalPedido = OrdenPedidoSucursal::where('id_pedido', $id)
                    ->where('id_sucursal', $sucursalId)
                    ->first();
                
                $fueAfectada = isset($sucursalesAfectadas[$sucursalId]);
                
                if ($sucursalPedido) {
                    if ($fueAfectada && $sucursalPedido->status == 1) {
                        $sucursalPedido->update([
                            'status' => 0,
                            'updated_at' => now()
                        ]);
                    }
                } else {
                    OrdenPedidoSucursal::create([
                        'id_pedido' => $id,
                        'id_sucursal' => $sucursalId,
                        'status' => 0,
                        'fecha_asignacion' => now(),
                        'created_at' => now()
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pedido actualizado correctamente'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . json_encode($e->errors())
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al actualizar pedido: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el pedido: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Asignar sucursales a los productos en el pedido.
     */
    public function asignarSucursales(Request $request, int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'pedidos_anticipo', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $pedido = OrdenPedido::with(['cotizacion.detalles'])->findOrFail($id);
            
            if ($pedido->status != 2) {
                return response()->json(['success' => false, 'message' => 'El pedido no está en proceso'], 400);
            }
            
            $asignaciones = $request->validate([
                'asignaciones' => 'required|array',
                'asignaciones.*.id_detalle' => 'required|integer',
                'asignaciones.*.id_sucursal' => 'required|integer|exists:sqlsrvM.sucursales,id_sucursal'
            ]);
            
            $sucursalesAsignadas = [];
            
            foreach ($asignaciones['asignaciones'] as $asignacion) {
                $detalle = CotizacionDetalle::findOrFail($asignacion['id_detalle']);
                
                if ($detalle->es_externo == 1) {
                    continue;
                }
                
                // Verificar stock disponible
                $producto = CatalogoGeneral::where('id_catalogo_general', $detalle->id_producto)
                    ->where('id_sucursal', $asignacion['id_sucursal'])
                    ->first();
                
                if (!$producto) {
                    throw new \Exception("Producto no encontrado en la sucursal seleccionada");
                }
                
                // Calcular stock disponible (considerando otros pedidos)
                $stockApartado = $this->calcularStockApartado($detalle->id_producto, $asignacion['id_sucursal'], $id);
                $stockDisponible = $producto->inventario - $stockApartado;
                
                if ($stockDisponible < $detalle->cantidad) {
                    throw new \Exception("Stock insuficiente para {$detalle->descripcion}. Disponible: {$stockDisponible}, Requerido: {$detalle->cantidad}");
                }
                
                // Actualizar detalle con sucursal asignada
                $detalle->id_sucursal_surtido = $asignacion['id_sucursal'];
                $detalle->save();
                
                $sucursalesAsignadas[$asignacion['id_sucursal']] = true;
            }
            
            // Registrar sucursales en orden_pedido_sucursal
            foreach (array_keys($sucursalesAsignadas) as $sucursalId) {
                $exists = OrdenPedidoSucursal::where('id_pedido', $id)
                    ->where('id_sucursal', $sucursalId)
                    ->exists();
                
                if (!$exists) {
                    OrdenPedidoSucursal::create([
                        'id_pedido' => $id,
                        'id_sucursal' => $sucursalId,
                        'status' => 0,
                        'fecha_asignacion' => now()
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Sucursales asignadas correctamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al asignar sucursales: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Marcar una sucursal como lista y reducir stock.
     */
    public function marcarListoSucursal(int $idPedidoSucursal): JsonResponse
    {
        $sucursalAsignada = auth()->user()->sucursal_asignada ?? 0;
        
        if ($sucursalAsignada == 0) {
            return response()->json(['success' => false, 'message' => 'Solo usuarios de sucursal pueden marcar como listo'], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $pedidoSucursal = OrdenPedidoSucursal::with('pedido')->findOrFail($idPedidoSucursal);
            
            if ($pedidoSucursal->id_sucursal != $sucursalAsignada) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso para esta sucursal'], 403);
            }
            
            if ($pedidoSucursal->status == 1) {
                return response()->json(['success' => false, 'message' => 'Ya fue marcado como listo'], 400);
            }
            
            // ============================================
            // REDUCIR STOCK DE PRODUCTOS NORMALES DE ESTA SUCURSAL
            // ============================================
            // Solo productos normales (con id_producto NO NULL y ean NO empieza con 'T')
            $detallesNormales = OrdenPedidoDetalle::where('id_pedido', $pedidoSucursal->id_pedido)
                ->where('id_sucursal_surtido', $sucursalAsignada)
                ->where('se_elimino', 0)
                ->whereNotNull('id_producto')
                ->where(function($q) {
                    $q->whereNull('ean')
                    ->orWhere('ean', 'NOT LIKE', 'T%');
                })
                ->get();
            
            $erroresStock = [];
            
            foreach ($detallesNormales as $detalle) {
                // Obtener nombre del producto (usando el EAN para buscar en cualquier sucursal)
                $productoInfo = CatalogoGeneral::where('ean', $detalle->ean)->first();
                $nombreProducto = $productoInfo->descripcion ?? 'Producto desconocido';
                
                // Buscar el producto en la sucursal específica por EAN
                $producto = CatalogoGeneral::where('ean', $detalle->ean)
                    ->where('id_sucursal', $sucursalAsignada)
                    ->where('activo', 1)
                    ->first();
                
                if (!$producto) {
                    $erroresStock[] = "Producto '{$nombreProducto}' (Código: {$detalle->ean}) no encontrado en esta sucursal";
                    continue;
                }
                
                if ($producto->inventario < $detalle->cantidad) {
                    $erroresStock[] = "Producto '{$nombreProducto}': Stock insuficiente (Disponible: {$producto->inventario}, Requerido: {$detalle->cantidad})";
                    continue;
                }
                
                // Reducir stock
                $producto->inventario -= $detalle->cantidad;
                $producto->save();
            }
            
            // Si hay errores de stock, no continuar
            if (!empty($erroresStock)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede marcar como listo:<br>' . implode('<br>', $erroresStock)
                ], 400);
            }
            
            // Marcar sucursal como lista
            $pedidoSucursal->status = 1;
            $pedidoSucursal->fecha_completado = now();
            $pedidoSucursal->save();
            
            DB::commit();
            
            // Mensaje según si hubo productos normales o solo externos
            if ($detallesNormales->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sucursal marcada como lista (solo productos sobre pedido, sin afectar stock)'
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Sucursal marcada como lista y stock actualizado correctamente'
                ]);
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al marcar sucursal como lista: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como listo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Asignar un repartidor al pedido.
     */
    public function asignarRepartidor(Request $request, int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'pedidos_anticipo', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            $validated = $request->validate([
                'id_repartidor' => 'required|exists:sqlsrvM.personal_empresa,id_personal_empresa'
            ]);
            
            $pedido = OrdenPedido::findOrFail($id);
            
            if ($pedido->status != 2) {
                return response()->json(['success' => false, 'message' => 'El pedido debe estar en proceso'], 400);
            }
            
            // Actualizar el pedido con el repartidor
            $pedido->id_repartidor = $validated['id_repartidor'];
            $pedido->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Repartidor asignado correctamente'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos: ' . json_encode($e->errors())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al asignar repartidor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Marcar pedido como entregado.
     */
    public function entregar(Request $request, int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'pedidos_anticipo', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            $pedido = OrdenPedido::findOrFail($id);
            
            if ($pedido->status != 2) {
                return response()->json(['success' => false, 'message' => 'El pedido no está en proceso'], 400);
            }
            
            if (!$pedido->id_repartidor) {
                return response()->json(['success' => false, 'message' => 'Debes asignar un repartidor primero'], 400);
            }
            
            $pedido->status = 3;
            $pedido->fecha_entrega_real = now();
            $pedido->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Pedido marcado como entregado'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al marcar entregado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como entregado'
            ], 500);
        }
    }
    
    /**
     * Cancelar orden (soft delete).
     */
    public function destroy(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'pedidos_anticipo', 'eliminar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $pedido = OrdenPedido::findOrFail($id);
            
            if ($pedido->status == 3) {
                return response()->json(['success' => false, 'message' => 'No se puede cancelar un pedido entregado'], 400);
            }
            
            $pedido->status = 1;
            $pedido->activo = 0;
            $pedido->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pedido cancelado correctamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cancelar pedido: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar el pedido'
            ], 500);
        }
    }
    
    /**
     * Generar PDF para el pedido.
     */
    public function pdf(int $id)
    {
        if (!auth()->user()->puede('ventas', 'pedidos_anticipo', 'ver')) {
            abort(403, 'No tienes permiso');
        }
        
        $sucursalAsignada = auth()->user()->sucursal_asignada ?? 0;
        
        $pedido = OrdenPedido::with([
            'cotizacion' => function($q) {
                $q->with(['cliente', 'sucursalAsignada']);
            },
            'cotizacion.detalles' => function($q) use ($sucursalAsignada) {
                if ($sucursalAsignada > 0) {
                    $q->where(function($sq) use ($sucursalAsignada) {
                        $sq->where('id_sucursal_surtido', $sucursalAsignada)
                        ->orWhere('es_externo', 1);
                    });
                }
            },
            'cotizacion.detalles.sucursalSurtido',
            'detalles' => function($q) use ($sucursalAsignada) {
                $q->where('se_elimino', 0);
                if ($sucursalAsignada > 0) {
                    $q->where('id_sucursal_surtido', $sucursalAsignada);
                }
            },
            'detalles.sucursalSurtido',
            'sucursales.sucursal',
            'repartidor'
        ])->findOrFail($id);
        
        // ============================================
        // ENRIQUECER DETALLES CON INFORMACIÓN DEL PRODUCTO
        // ============================================
        foreach ($pedido->detalles as $detalle) {
            $esExterno = is_null($detalle->id_producto) || ($detalle->ean && str_starts_with($detalle->ean, 'T'));
            
            if ($esExterno) {
                // Producto externo - buscar en tmp_catalogo
                $productoExterno = TmpCatalogo::where('ean', $detalle->ean)->first();
                $detalle->nombre = $productoExterno->descripcion ?? 'Producto externo';
                $detalle->codbar = $detalle->ean;
                $detalle->es_externo = 1;
            } else {
                // Producto normal - buscar en catalogo_general
                $producto = CatalogoGeneral::find($detalle->id_producto);
                if ($producto) {
                    $detalle->nombre = $producto->descripcion;
                    $detalle->codbar = $producto->ean ?? $detalle->ean;
                    $detalle->num_familia = $producto->num_familia ?? '';
                    $detalle->es_externo = 0;
                } else {
                    $detalle->nombre = 'Producto no disponible';
                    $detalle->codbar = $detalle->ean ?? '-';
                    $detalle->es_externo = 0;
                }
            }
        }
        
        $pdf = Pdf::loadView('ventas.pedidos.pdf', compact('pedido'));
        $pdf->setPaper('letter', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);
        
        return $pdf->download("Pedido_{$pedido->folio_pedido}.pdf");
    }
    
    /**
     * Obtener repartidores disponibles.
     */
    public function repartidoresDisponibles(): JsonResponse
    {
        try {
            $repartidores = DB::connection('sqlsrvM')
                ->table('rh_personal_servicios_domicilio as rsd')
                ->join('personal_empresa as pe', 'rsd.id_personal', '=', 'pe.id_personal_empresa')
                ->where('pe.Activo', 1)
                ->select(
                    'pe.id_personal_empresa',
                    DB::raw("CONCAT(pe.Nombre, ' ', pe.apPaterno, ' ', COALESCE(pe.apMaterno, '')) as nombre_completo"),
                    'rsd.id_sucursal',
                    'rsd.hora_entrada',
                    'rsd.hora_salida'
                )
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $repartidores
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en repartidoresDisponibles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener productos con stock por sucursal.
     */
    public function stockPorSucursal(int $productoId, Request $request): JsonResponse
    {
        if ($productoId <= 0) {
            return response()->json(['success' => true, 'data' => []]);
        }
        
        $producto = CatalogoGeneral::findOrFail($productoId);
        
        $sucursales = Sucursal::where('activo', 1)->get();
        
        $resultados = [];
        
        foreach ($sucursales as $sucursal) {
            $productoSucursal = CatalogoGeneral::where('ean', $producto->ean)
                ->where('id_sucursal', $sucursal->id_sucursal)
                ->where('activo', 1)
                ->first();
            
            if ($productoSucursal) {
                // Calcular stock apartado (opcional)
                $stockApartado = $this->calcularStockApartado($productoId, $sucursal->id_sucursal, null, null);
                $stockDisponible = max(0, $productoSucursal->inventario - $stockApartado);
                
                $resultados[] = [
                    'id_sucursal' => $sucursal->id_sucursal,
                    'nombre' => $sucursal->nombre,
                    'inventario' => $productoSucursal->inventario,
                    'disponible' => $stockDisponible,
                    'precio' => floatval($productoSucursal->precio)
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $resultados
        ]);
    }
    
    /**
     * Calcular el stock apartado para un producto en una sucursal.
     */
    private function calcularStockApartado(int $productoId, int $sucursalId, ?int $pedidoId = null, ?int $detalleId = null): int
    {
        $query = DB::connection('sqlsrv')->table('crm_cotizaciones_detalle as cd')
            ->join('crm_cotizaciones as c', 'cd.id_cotizacion', '=', 'c.id_cotizacion')
            ->join('orden_pedido as op', 'c.id_cotizacion', '=', 'op.id_cotizacion')
            ->where('cd.id_producto', $productoId)
            ->where('cd.id_sucursal_surtido', $sucursalId)
            ->where('cd.es_externo', 0)
            ->where('op.activo', 1)
            ->where('op.status', 2);
        
        if ($pedidoId) {
            $query->where('op.id_pedido', '!=', $pedidoId);
        }
        
        if ($detalleId) {
            $query->where('cd.id_detalle', '!=', $detalleId);
        }
        
        return (int) $query->sum('cd.cantidad');
    }
    
    /**
     * Mostrar vista de asignación de repartidor
     */
    public function vistaAsignarRepartidor(int $id): View
    {
        $pedido = OrdenPedido::with('cotizacion')->findOrFail($id);
        $sucursalAsignada = auth()->user()->sucursal_asignada ?? 0;
        $usuarioId = auth()->id();
        
        // Verificar si el usuario es repartidor (está en tabla rh_personal_servicios_domicilio)
        $esRepartidor = DB::connection('sqlsrvM')->table('rh_personal_servicios_domicilio')
            ->where('id_personal', $usuarioId)
            ->exists();
        
        // Verificar si tiene permisos de edición (CRM)
        $tienePermisoEditar = auth()->user()->puede('ventas', 'pedidos_anticipo', 'editar');
        
        // Usuario de sucursal: tiene sucursal asignada pero NO es repartidor
        $esUsuarioSucursal = ($sucursalAsignada > 0 && !$esRepartidor);
        
        // Permitir acceso a: CRM, usuarios de sucursal, repartidores
        if (!$tienePermisoEditar && !$esUsuarioSucursal && !$esRepartidor) {
            abort(403, 'No tienes permiso para acceder a esta sección');
        }
        
        // Si es repartidor pero el pedido no está asignado a él, redirigir
        if ($esRepartidor && $pedido->id_repartidor != $usuarioId) {
            abort(403, 'Este pedido no está asignado a ti');
        }
        
        // Validación SOLO para usuarios de sucursal (no para repartidores)
        if ($esUsuarioSucursal) {
            $tieneProducto = OrdenPedidoDetalle::where('id_pedido', $id)
                ->where('id_sucursal_surtido', $sucursalAsignada)
                ->where('se_elimino', 0)
                ->exists();
            
            if (!$tieneProducto) {
                abort(403, 'No tienes productos asignados en este pedido para tu sucursal');
            }
        }
        
        // Obtener sucursales para el select
        $sucursales = Sucursal::where('activo', 1)->get();
        
        return view('ventas.pedidos.asignar-repartidor', compact('pedido', 'sucursalAsignada', 'esRepartidor', 'sucursales'));
    }

    /**
     * Obtener repartidores con su status actualizado para la vista de asignación
     */
    public function repartidoresConStatus(int $pedidoId): JsonResponse
    {
        try {
            $sucursalAsignada = auth()->user()->sucursal_asignada ?? 0;
            $usuarioId = auth()->id();
            
            // Verificar si el usuario es repartidor
            $esRepartidor = DB::connection('sqlsrvM')->table('rh_personal_servicios_domicilio')
                ->where('id_personal', $usuarioId)
                ->exists();
            
            // Verificar permisos
            $tienePermisoEditar = auth()->user()->puede('ventas', 'pedidos_anticipo', 'editar');
            $esUsuarioSucursal = ($sucursalAsignada > 0 && !$esRepartidor);
            
            if (!$tienePermisoEditar && !$esUsuarioSucursal && !$esRepartidor) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
            }
            
            // Si es repartidor, verificar que el pedido le pertenece
            if ($esRepartidor) {
                $pedido = OrdenPedido::find($pedidoId);
                if (!$pedido || $pedido->id_repartidor != $usuarioId) {
                    return response()->json(['success' => false, 'message' => 'Este pedido no está asignado a ti'], 403);
                }
            }
            
            // Validación SOLO para usuarios de sucursal
            if ($esUsuarioSucursal) {
                $tieneProducto = OrdenPedidoDetalle::where('id_pedido', $pedidoId)
                    ->where('id_sucursal_surtido', $sucursalAsignada)
                    ->where('se_elimino', 0)
                    ->exists();
                
                if (!$tieneProducto) {
                    return response()->json(['success' => false, 'message' => 'No tienes productos asignados en este pedido para tu sucursal'], 403);
                }
            }
            
            // Obtener repartidores base
            $repartidoresQuery = PersonalEmpresa::whereIn('id_personal_empresa', function($q) {
                $q->select('id_personal')->from('rh_personal_servicios_domicilio');
            });
            
            if ($esRepartidor) {
                // Repartidor: solo ve su propio registro
                $repartidoresQuery->where('id_personal_empresa', $usuarioId);
            } elseif ($esUsuarioSucursal) {
                // Usuario de sucursal: solo repartidores de su sucursal
                $repartidoresQuery->where('sucursal_asignada', $sucursalAsignada);
            }
            // CRM (con permiso): ve todos los repartidores
            
            $repartidores = $repartidoresQuery->get();
            
            $hoy = now()->toDateString();
            $horaActual = now()->format('H:i:s');
            $repartidoresConStatus = [];
            
            foreach ($repartidores as $repartidor) {
                // Obtener el horario más reciente para este repartidor
                $horario = DB::connection('sqlsrvM')->table('rh_personal_servicios_domicilio')
                    ->select('hora_entrada', 'hora_salida', 'fecha')
                    ->where('id_personal', $repartidor->id_personal_empresa)
                    ->where('fecha', '<=', $hoy)
                    ->orderBy('fecha', 'desc')
                    ->first();
                
                // Verificar si tiene recorrido activo
                $recorridoActivo = DB::connection('sqlsrvM')->table('oper_recorridos_choferes')
                    ->where('id_personal', $repartidor->id_personal_empresa)
                    ->where('status', 0)
                    ->first();
                
                // Calcular status
                if ($recorridoActivo) {
                    $status = 'En recorrido';
                } elseif (!$horario) {
                    $status = 'Horario no asignado';
                } elseif ($horaActual >= $horario->hora_entrada && $horaActual <= $horario->hora_salida) {
                    $status = 'Disponible';
                } else {
                    $status = 'Fuera de horario';
                }
                
                $repartidoresConStatus[] = [
                    'id' => $repartidor->id_personal_empresa,
                    'nombre' => $repartidor->nombre_completo,
                    'sucursal' => $repartidor->sucursal_asignada,
                    'horario_entrada' => $horario->hora_entrada ?? null,
                    'horario_salida' => $horario->hora_salida ?? null,
                    'status' => $status
                ];
            }
            
            // Obtener entregas en curso (filtradas por rol)
            $entregasQuery = DB::connection('sqlsrvM')->table('oper_recorridos_choferes as rc')
                ->join('personal_empresa as pe', 'rc.id_personal', '=', 'pe.id_personal_empresa')
                ->where('rc.status', 0);
            
            if ($esRepartidor) {
                // Repartidor: solo sus propias entregas
                $entregasQuery->where('rc.id_personal', $usuarioId);
            } elseif ($esUsuarioSucursal) {
                // Usuario de sucursal: entregas de repartidores de su sucursal
                $entregasQuery->where('pe.sucursal_asignada', $sucursalAsignada);
            }
            // CRM: ve todas las entregas
            
            $entregasEnCurso = $entregasQuery->select(
                'rc.id',
                'pe.Nombre as repartidor_nombre',
                'pe.apPaterno as repartidor_apaterno',
                'rc.nombrecliente',
                'rc.Domicilio',
                'rc.hora_salida'
            )->get();
            
            // Calcular tiempo fuera para cada entrega
            foreach ($entregasEnCurso as $entrega) {
                if ($entrega->hora_salida) {
                    $horaInicio = \Carbon\Carbon::parse($entrega->hora_salida);
                    $diferencia = $horaInicio->diff(now());
                    $entrega->tiempo_fuera = sprintf('%02d:%02d:%02d', $diferencia->h, $diferencia->i, $diferencia->s);
                } else {
                    $entrega->tiempo_fuera = '00:00:00';
                }
            }
            
            return response()->json([
                'success' => true,
                'repartidores' => $repartidoresConStatus,
                'entregas_curso' => $entregasEnCurso,
                'es_repartidor' => $esRepartidor,
                'es_usuario_sucursal' => $esUsuarioSucursal,
                'sucursal_asignada' => $sucursalAsignada,
                'tiene_permiso' => $tienePermisoEditar
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en repartidoresConStatus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar datos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcular status del repartidor
     */
    private function calcularStatusRepartidor($horario, $horaActual, $recorridoActivo): string
    {
        if ($recorridoActivo) {
            return 'En recorrido';
        }
        
        if (!$horario || !$horario->hora_entrada || !$horario->hora_salida) {
            return 'Horario no asignado';
        }
        
        if ($horaActual >= $horario->hora_entrada && $horaActual <= $horario->hora_salida) {
            return 'Disponible';
        }
        
        return 'Fuera de horario';
    }

    /**
     * Iniciar un nuevo recorrido
     */
    public function iniciarRecorrido(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id_personal' => 'required|integer',
                'fecha' => 'required|date',
                'folio_ticket' => 'required|integer',
                'importeticket' => 'required|numeric',
                'nombrecliente' => 'required|string|max:150',
                'Domicilio' => 'required|string|max:250',
                'kminicial' => 'required|integer',
                'Solicitadoensucursal' => 'required|integer',
                'hora_salida' => 'required|string',
                'pedido_id' => 'required|integer'
            ]);
            
            // Verificar que el usuario sea repartidor
            $usuarioId = auth()->id();
            $esRepartidor = DB::connection('sqlsrvM')->table('rh_personal_servicios_domicilio')
                ->where('id_personal', $usuarioId)
                ->exists();
            
            if (!$esRepartidor) {
                return response()->json(['success' => false, 'message' => 'No eres un repartidor autorizado'], 403);
            }
            
            // Verificar que no tenga un recorrido activo para este pedido
            $recorridoActivo = DB::connection('sqlsrvM')->table('oper_recorridos_choferes')
                ->where('id_personal', $usuarioId)
                ->where('folio_ticket', $validated['pedido_id'])
                ->where('status', 0)
                ->first();
            
            if ($recorridoActivo) {
                return response()->json(['success' => false, 'message' => 'Ya tienes un recorrido activo para este pedido'], 400);
            }
            
            // Insertar recorrido
            $id = DB::connection('sqlsrvM')->table('oper_recorridos_choferes')->insertGetId([
                'id_personal' => $validated['id_personal'],
                'fecha' => $validated['fecha'],
                'folio_ticket' => $validated['folio_ticket'],
                'importeticket' => $validated['importeticket'],
                'nombrecliente' => $validated['nombrecliente'],
                'Domicilio' => $validated['Domicilio'],
                'kminicial' => $validated['kminicial'],
                'Solicitadoensucursal' => $validated['Solicitadoensucursal'],
                'hora_salida' => $validated['hora_salida'],
                'status' => 0,
                'created_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Recorrido iniciado correctamente',
                'id' => $id
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al iniciar recorrido: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar recorrido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalizar recorrido y marcar pedido como entregado
     */
    public function finalizarRecorrido(int $recorridoId, Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            $recorrido = DB::connection('sqlsrvM')->table('oper_recorridos_choferes')
                ->where('id', $recorridoId)
                ->first();
            
            if (!$recorrido) {
                return response()->json(['success' => false, 'message' => 'Recorrido no encontrado'], 404);
            }
            
            $kmFinal = $request->input('kmfinal');
            
            // Actualizar recorrido
            DB::connection('sqlsrvM')->table('oper_recorridos_choferes')
                ->where('id', $recorridoId)
                ->update([
                    'kmfinal' => $kmFinal,
                    'hora_regreso' => now()->format('H:i:s'),
                    'status' => 1, // Finalizado
                    'updated_at' => now()
                ]);
            
            // Buscar el pedido asociado (por folio_ticket o id_pedido)
            $pedido = OrdenPedido::where('id_pedido', $recorrido->folio_ticket)->first();
            
            if ($pedido && $pedido->status == 2) {
                $pedido->status = 3; // Entregado
                $pedido->fecha_entrega_real = now();
                $pedido->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Recorrido finalizado y pedido marcado como entregado'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al finalizar recorrido: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar recorrido'
            ], 500);
        }
    }
}