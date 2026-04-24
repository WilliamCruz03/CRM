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
        
        $permisos = [
            'mostrar' => $puedeMostrar,
            'ver' => $puedeVer,
            'crear' => auth()->user()->puede('ventas', 'pedidos_anticipo', 'crear'),
            'editar' => auth()->user()->puede('ventas', 'pedidos_anticipo', 'editar'),
            'eliminar' => auth()->user()->puede('ventas', 'pedidos_anticipo', 'eliminar'),
        ];
        
        $pedidos = collect();
        
        if ($puedeVer) {
            $query = OrdenPedido::with(['cotizacion.cliente', 'cotizacion.sucursalAsignada', 'sucursales.sucursal'])
                ->where('activo', 1);
            
            // Aplicar filtro por sucursal si el usuario tiene sucursal asignada
            if ($sucursalAsignada > 0) {
                $query->whereHas('sucursales', function($q) use ($sucursalAsignada) {
                    $q->where('id_sucursal', $sucursalAsignada);
                });
            }
            
            $pedidos = $query->orderBy('id_pedido', 'desc')->paginate(15);
        }
        
        return view('ventas.pedidos.index', compact('pedidos', 'permisos', 'sucursalAsignada'));
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
            'cotizacion.detalles.producto',
            'cotizacion.detalles.sucursalSurtido',
            'sucursales.sucursal',
            'creador',
            'repartidor'
        ])->findOrFail($id);
        
        // Enriquecer detalles con información de stock
        foreach ($pedido->cotizacion->detalles as $detalle) {
            if ($detalle->es_externo == 0 && $detalle->id_sucursal_surtido) {
                $producto = CatalogoGeneral::where('id_catalogo_general', $detalle->id_producto)
                    ->where('id_sucursal', $detalle->id_sucursal_surtido)
                    ->first();
                $detalle->stock_actual = $producto ? $producto->inventario : 0;
            } else {
                $detalle->stock_actual = null;
            }
        }
        
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
            }
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
                'productos.*.id_sucursal_surtido' => 'nullable|integer',
                'productos.*.es_agregado' => 'boolean',
                'productos.*.id_cotizacion_detalle' => 'nullable|integer'
            ]);

            // Actualizar datos básicos del pedido
            $pedido->comentarios = $validated['comentarios'] ?? null;
            $pedido->id_repartidor = $validated['id_repartidor'] ?? null;
            $pedido->save();

            // Obtener IDs de los detalles actuales para saber cuáles eliminar
            $idsRecibidos = [];
            $sucursalesAfectadas = [];

            // Procesar cada producto
            foreach ($validated['productos'] as $productoData) {
                $importe = $productoData['cantidad'] * $productoData['precio_unitario'] * (1 - ($productoData['descuento'] ?? 0) / 100);

                if (isset($productoData['id_detalle_pedido']) && $productoData['id_detalle_pedido']) {
                    // Actualizar detalle existente
                    $detalle = OrdenPedidoDetalle::find($productoData['id_detalle_pedido']);
                    if ($detalle && $detalle->id_pedido == $id) {
                        // Si estaba marcado como eliminado, lo reactivamos
                        $detalle->update([
                            'cantidad' => $productoData['cantidad'],
                            'precio_unitario' => $productoData['precio_unitario'],
                            'descuento' => $productoData['descuento'] ?? 0,
                            'importe' => $importe,
                            'id_convenio' => $productoData['id_convenio'] ?? null,
                            'id_sucursal_surtido' => $productoData['id_sucursal_surtido'] ?? null,
                            'se_elimino' => 0,  // Reactivar si estaba eliminado
                            'updated_at' => now()
                        ]);
                        $idsRecibidos[] = $detalle->id_detalle_pedido;
                        
                        // Registrar sucursal afectada
                        if ($productoData['id_sucursal_surtido']) {
                            $sucursalesAfectadas[$productoData['id_sucursal_surtido']] = true;
                        }
                    }
                } else {
                    // Detectar si es externo (sin id_producto o con ean que empieza con T)
                    $esExterno = empty($productoData['id_producto']) || 
                                (isset($productoData['ean']) && str_starts_with($productoData['ean'], 'T'));

                    $nuevoDetalle = OrdenPedidoDetalle::create([
                        'id_pedido' => $id,
                        'id_cotizacion_detalle' => $productoData['id_cotizacion_detalle'] ?? null,
                        'id_producto' => $esExterno ? null : $productoData['id_producto'],  // NULL si externo
                        'ean' => $productoData['ean'] ?? null,
                        'cantidad' => $productoData['cantidad'],
                        'precio_unitario' => $productoData['precio_unitario'],
                        'descuento' => $productoData['descuento'] ?? 0,
                        'importe' => $importe,
                        'id_convenio' => $productoData['id_convenio'] ?? null,
                        'id_sucursal_surtido' => $productoData['id_sucursal_surtido'] ?? null,
                        'es_agregado' => true,
                        'se_elimino' => 0,
                        'created_at' => now()
                    ]);
                    $idsRecibidos[] = $nuevoDetalle->id_detalle_pedido;
                    
                    // Registrar sucursal afectada
                    if ($productoData['id_sucursal_surtido']) {
                        $sucursalesAfectadas[$productoData['id_sucursal_surtido']] = true;
                    }
                }
            }

            // Validar que todos los productos normales tengan sucursal asignada
            foreach ($validated['productos'] as $productoData) {
                if (empty($productoData['es_externo']) && empty($productoData['id_sucursal_surtido'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Todos los productos deben tener una sucursal de surtido asignada'
                    ], 422);
                }
            }

            // Marcar como eliminados los detalles que no fueron enviados (productos removidos)
            // con lógica diferenciada según es_agregado
            $detallesEliminar = OrdenPedidoDetalle::where('id_pedido', $id)
            ->whereNotIn('id_detalle_pedido', $idsRecibidos)
            ->get();

            foreach ($detallesEliminar as $detalle) {
                if ($detalle->es_agregado == 1) {
                    // Producto agregado manualmente → eliminación física
                    $detalle->delete();
                } else {
                    // Producto que viene de cotización → soft-delete
                    $detalle->update([
                        'se_elimino' => 1,
                        'updated_at' => now()
                    ]);
                }
            }

            // Obtener todas las sucursales que están en uso en los detalles del pedido
            $sucursalesEnUso = OrdenPedidoDetalle::where('id_pedido', $id)
                ->whereNotNull('id_sucursal_surtido')
                ->where('se_elimino', 0)
                ->distinct()
                ->pluck('id_sucursal_surtido')
                ->toArray();

            // Eliminar sucursales que ya no están en uso
            OrdenPedidoSucursal::where('id_pedido', $id)
                ->whereNotIn('id_sucursal', $sucursalesEnUso)
                ->delete();

            // Insertar las sucursales que faltan (solo si no existen)
            foreach ($sucursalesEnUso as $sucursalId) {
                $existe = OrdenPedidoSucursal::where('id_pedido', $id)
                    ->where('id_sucursal', $sucursalId)
                    ->exists();
                
                if (!$existe) {
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
            \Log::error('Stack trace: ' . $e->getTraceAsString());
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
     * Marcar sucursal como lista.
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
            
            $pedidoSucursal->status = 1;
            $pedidoSucursal->fecha_completado = now();
            $pedidoSucursal->save();
            
            // El trigger actualizará automáticamente el status del pedido
            // cuando todas las sucursales estén listas
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Sucursal marcada como lista'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al marcar sucursal como lista: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como listo'
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
            'sucursales.sucursal',
            'repartidor'
        ])->findOrFail($id);
        
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
        $cotizacionId = $request->input('cotizacion_id', null);
        $detalleId = $request->input('detalle_id', null);
        
        $producto = CatalogoGeneral::findOrFail($productoId);
        
        $sucursales = Sucursal::where('activo', 1)->get();
        
        $resultados = [];
        
        foreach ($sucursales as $sucursal) {
            $productoSucursal = CatalogoGeneral::where('ean', $producto->ean)
                ->where('id_sucursal', $sucursal->id_sucursal)
                ->where('activo', 1)
                ->first();
            
            if ($productoSucursal) {
                $stockApartado = $this->calcularStockApartado($productoId, $sucursal->id_sucursal, $cotizacionId, $detalleId);
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
     * Verificar si el usuario puede marcar la sucursal como lista.
     */
    private function usuarioPuedeMarcarListo(OrdenPedido $pedido): bool
    {
        $sucursalAsignada = auth()->user()->sucursal_asignada ?? 0;
        
        if ($sucursalAsignada == 0) {
            return false;
        }
        
        $pedidoSucursal = OrdenPedidoSucursal::where('id_pedido', $pedido->id_pedido)
            ->where('id_sucursal', $sucursalAsignada)
            ->first();
        
        return $pedidoSucursal && $pedidoSucursal->status == 0;
    }
}