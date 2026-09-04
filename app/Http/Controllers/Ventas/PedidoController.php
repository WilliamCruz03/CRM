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
use App\Models\Pedidos\PedidoCancelado;
use App\Models\Pedidos\OperRecorridosChoferes;
use App\Models\PermisoGranular;
use App\Models\TmpCatalogo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Events\PedidoMarcadoListo;
use App\Events\PedidoAsignadoRepartidor;
use App\Models\Notificacion;

class PedidoController extends Controller
{
    /**
     * Mostrar la lista de pedidos.
     */
    public function index(): View
    {
        $user = auth()->user();
        
        $puedeMostrar = $user->puede('ventas', 'pedidos_anticipo', 'mostrar');
        $puedeVer = $user->puede('ventas', 'pedidos_anticipo', 'ver');
        
        if (!$puedeMostrar && !$puedeVer) {
            abort(403, 'No tienes permiso para acceder a este módulo');
        }
        
        // Perfiles del usuario 
        $esCRM = $user->es_crm;
        $esSucursal = $user->es_sucursal;
        $esRepartidor = $user->es_repartidor;
        
        $sucursalAsignada = $user->sucursal_asignada_efectiva; // Sucursal efectiva según perfil
        $usuarioId = $user->id_personal_empresa;
        
        $permisos = [
            'mostrar' => $puedeMostrar,
            'ver' => $puedeVer,
            'crear' => $user->puede('ventas', 'pedidos_anticipo', 'crear'),
            'editar' => $user->puede('ventas', 'pedidos_anticipo', 'editar'),
            'eliminar' => $user->puede('ventas', 'pedidos_anticipo', 'eliminar'),
        ];
        
        $pedidos = collect();
        
        if ($puedeVer) {
            $query = OrdenPedido::with([
                'cotizacion.cliente', 
                'cotizacion.sucursalAsignada', 
                'sucursales.sucursal',
                'repartidor',
                'detalles' => function($q) {
                    $q->where('se_elimino', 0);
                }
            ])
            ->where('activo', 1)
            ->where('status', '!=', 1);
            
            // FILTRO DE VISIBILIDAD SEGÚN COMBINACIÓN DE PERFILES
            if ($esCRM) {
                // CRM: ve todos los pedidos (sin filtro)
                // No se aplica ningún filtro (incluso si tiene Sucursal o Repartidor, CRM domina)
            } elseif ($esSucursal && $esRepartidor && !$esCRM) {
                // Sucursal + Repartidor (sin CRM): ve pedidos de su sucursal O los asignados a él
                $query->where(function($q) use ($sucursalAsignada, $usuarioId) {
                    $q->whereHas('detalles', function($sub) use ($sucursalAsignada) {
                        $sub->where('id_sucursal_surtido', $sucursalAsignada)
                            ->where('se_elimino', 0);
                    })->orWhere('id_repartidor', $usuarioId);
                });
            } elseif ($esRepartidor) {
                // Solo Repartidor: solo pedidos asignados a él (independientemente del horario)
                $query->where('id_repartidor', $usuarioId);
            } elseif ($esSucursal && $sucursalAsignada > 0) {
                // Solo Sucursal: solo pedidos que tengan productos de su sucursal
                $query->whereHas('detalles', function($q) use ($sucursalAsignada) {
                    $q->where('id_sucursal_surtido', $sucursalAsignada)
                        ->where('se_elimino', 0);
                });
            } else {
                // Sin perfil específico: usar lógica anterior (compatibilidad)
                if ($sucursalAsignada > 0) {
                    $query->whereHas('detalles', function($q) use ($sucursalAsignada) {
                        $q->where('id_sucursal_surtido', $sucursalAsignada)
                            ->where('se_elimino', 0);
                    });
                }
            }
            
            $pedidos = $query->orderByRaw("
                CASE 
                    WHEN status = 2 THEN 1  -- En proceso (prioridad 1)
                    WHEN status = 3 THEN 2  -- Finalizado (prioridad 2)
                    WHEN status = 1 THEN 3  -- Cancelado (prioridad 3)
                    ELSE 4
                END, id_pedido DESC
            ")->paginate(15);
        }
        
        $ultimoId = OrdenPedido::max('id_pedido') ?? 0;
        
        return view('ventas.pedidos.index', compact(
            'pedidos', 'permisos', 'sucursalAsignada', 
            'esRepartidor', 'esCRM', 'esSucursal', 'ultimoId'
        ));
    }
    
    /**
     * Mostrar el pedido especificado.
     */
    public function show(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'pedidos_anticipo', 'ver')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        $user = auth()->user();
        $sucursalAsignada = $user->sucursal_asignada_efectiva;
        $esCRM = $user->es_crm;
        $esSucursal = $user->es_sucursal;

        // DETERMINAR SI DEBE FILTRAR POR SUCURSAL
        // Solo Sucursal (sin CRM) filtra por sucursal
        // CRM (con o sin Sucursal) NO filtra
        $filtrarPorSucursal = $esSucursal && !$esCRM;

        // Si es Sucursal (sin CRM) y no tiene productos asignados, error
        if ($filtrarPorSucursal && $sucursalAsignada > 0) {
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
            'cotizacion.detalles' => function($q) use ($filtrarPorSucursal, $sucursalAsignada) {
                if ($filtrarPorSucursal && $sucursalAsignada > 0) {
                    $q->where('es_externo', 1);
                }
            },
            'cotizacion.detalles.sucursalSurtido',
            'detalles' => function($q) use ($filtrarPorSucursal, $sucursalAsignada) {
                $q->where('se_elimino', 0);
                if ($filtrarPorSucursal && $sucursalAsignada > 0) {
                    $q->where('id_sucursal_surtido', $sucursalAsignada);
                }
            },
            'detalles.sucursalSurtido',
            'sucursales.sucursal',
            'creador',
            'repartidor'
        ])->findOrFail($id);

        // PROCESAR DETALLES PARA LA VISTA
        $detallesParaMostrar = [];

        if ($pedido->detalles->isNotEmpty()) {
            foreach ($pedido->detalles as $detalle) {
                $esExterno = str_starts_with($detalle->ean, 'T');

                if ($esExterno) {
                    $productoExterno = TmpCatalogo::where('ean', $detalle->ean)->first();
                    $detallesParaMostrar[] = (object)[
                        'id_detalle' => $detalle->id_detalle_pedido,
                        'codbar' => $detalle->ean,
                        'descripcion' => $productoExterno->descripcion ?? 'Sobre Pedido',
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'descuento' => $detalle->descuento,
                        'importe' => $detalle->importe,
                        'sucursal_surtido' => $detalle->sucursalSurtido,
                        'es_externo' => true
                    ];
                } else {
                    $producto = CatalogoGeneral::where('ean', $detalle->ean)->first();
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
            // Si no hay detalles en el pedido, usar los de la cotización
            foreach ($pedido->cotizacion->detalles as $detalle) {
                $esExterno = str_starts_with($detalle->codbar, 'T');

                if ($esExterno) {
                    $productoExterno = TmpCatalogo::where('ean', $detalle->codbar)->first();
                    $detallesParaMostrar[] = (object)[
                        'id_detalle' => $detalle->id_cotizacion_detalle,
                        'codbar' => $detalle->codbar,
                        'descripcion' => $productoExterno->descripcion ?? $detalle->descripcion,
                        'cantidad' => $detalle->cantidad,
                        'precio_unitario' => $detalle->precio_unitario,
                        'descuento' => $detalle->descuento,
                        'importe' => $detalle->importe,
                        'sucursal_surtido' => $detalle->sucursalSurtido,
                        'es_externo' => true
                    ];
                } else {
                    $producto = CatalogoGeneral::where('ean', $detalle->codbar)->first();
                    $detallesParaMostrar[] = (object)[
                        'id_detalle' => $detalle->id_cotizacion_detalle,
                        'codbar' => $detalle->codbar,
                        'descripcion' => $producto->descripcion ?? $detalle->descripcion,
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

        $pedido->detalles_procesados = $detallesParaMostrar;
        $pedido->sucursal_usuario = $filtrarPorSucursal ? $sucursalAsignada : 0;
        $pedido->usuario_puede_marcar_listo = $this->usuarioPuedeMarcarListo($pedido);

        // Obtener folio_ticket de orden_pedido_sucursal
        if ($filtrarPorSucursal && $sucursalAsignada > 0) {
            $sucursalData = OrdenPedidoSucursal::where('id_pedido', $id)
                ->where('id_sucursal', $sucursalAsignada)
                ->first();
        } else {
            $sucursalData = OrdenPedidoSucursal::where('id_pedido', $id)
                ->whereNotNull('folio_ticket')
                ->first();
        }

        if ($sucursalData) {
            $pedido->folio_ticket = $sucursalData->folio_ticket;
        } else {
            $pedido->folio_ticket = null;
        }
        $pedido->puede_editar = auth()->user()->puede('ventas', 'pedidos_anticipo', 'editar');

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
                    $q->where('es_externo', 1);
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
            // Determinar por EAN si es externo
            $esExterno = str_starts_with($detalle->ean, 'T');

            if ($esExterno) {
                // Cargar desde tmp_catalogo usando el EAN
                $productoExterno = TmpCatalogo::where('ean', $detalle->ean)->first();
                $detalle->nombre = $productoExterno->descripcion ?? 'Sobre Pedido';
                $detalle->codbar = $detalle->ean;
                $detalle->num_familia = 'EXT';
                $detalle->inventario_disponible = 999;
                $detalle->es_externo = 1;
                $detalle->inventario_global = 999;
            } else {
                // Cargar desde catalogo_general usando EAN
                $producto = CatalogoGeneral::where('ean', $detalle->ean)->first();
                if ($producto) {
                    $detalle->nombre = $producto->descripcion;
                    $detalle->codbar = $producto->ean ?? '';
                    $detalle->num_familia = $producto->num_familia ?? '';
                    $detalle->inventario_disponible = $producto->inventario ?? 0;
                    $detalle->es_externo = false;
                    
                    // Calcular stock global (suma de todas las sucursales para este EAN)
                    $stockGlobal = CatalogoGeneral::where('ean', $detalle->ean)->sum('inventario');
                    $detalle->inventario_global = $stockGlobal;
                } else {
                    // Si no se encuentra el producto (posiblemente fue eliminado)
                    $detalle->nombre = 'Producto no disponible';
                    $detalle->codbar = $detalle->ean ?? '';
                    $detalle->num_familia = '';
                    $detalle->inventario_disponible = 0;
                    $detalle->es_externo = false;
                    $detalle->inventario_global = 0;
                }
            }

            // Calcular stock actual si tiene sucursal asignada
            if (!$esExterno && $detalle->id_sucursal_surtido) {
                $productoStock = CatalogoGeneral::where('ean', $detalle->ean)
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
                    // Buscar por codbar
                    $productoExterno = TmpCatalogo::where('ean', $detalle->codbar)->first();
                    $detalle->nombre = $productoExterno->descripcion ?? 'Sobre Pedido';
                    $detalle->codbar = $productoExterno->ean ?? $detalle->codbar;
                    $detalle->ean = $productoExterno->ean ?? $detalle->codbar;
                    $detalle->es_externo = 1;
                    $detalle->inventario_disponible = 999;
                    $detalle->inventario_global = 999;
                } else {
                    // Buscar por codbar
                    $producto = CatalogoGeneral::where('ean', $detalle->codbar)->first();
                    $detalle->nombre = $producto->descripcion ?? 'Producto no encontrado';
                    $detalle->codbar = $producto->ean ?? $detalle->codbar;
                    $detalle->ean = $producto->ean ?? $detalle->codbar;
                    $detalle->num_familia = $producto->num_familia ?? '';
                    $detalle->inventario_disponible = $producto->inventario ?? 0;
                    $detalle->es_externo = false;
                    
                    // Calcular stock global
                    $stockGlobal = CatalogoGeneral::where('ean', $detalle->codbar)->sum('inventario');
                    $detalle->inventario_global = $stockGlobal;
                }
                $detallesProcesados[] = $detalle;
            }

            $pedido->detalles = $detallesProcesados;
        }

        // Calcular si se debe mostrar la sección de asignación de repartidor
        $sucursalesPendientes = $pedido->sucursales->contains('status', 0);
        $todasSucursalesListas = $pedido->sucursales->isNotEmpty() && !$sucursalesPendientes;
        $mostrarAsignacionRepartidor = ($sucursalAsignada == 0 && $pedido->status == 2 && $todasSucursalesListas);

        $pedido->mostrar_asignacion_repartidor = $mostrarAsignacionRepartidor;

        // Formatear fecha y hora para el frontend
        $fechaEntrega = null;
        $horaEntrega = null;

        if ($pedido->fecha_entrega_sugerida) {
            try {
                $fecha = \Carbon\Carbon::parse($pedido->fecha_entrega_sugerida);
                $fechaEntrega = $fecha->format('Y-m-d');
            } catch (\Exception $e) {
                $fechaEntrega = null;
            }
        }

        if ($pedido->hora_entrega_sugerida) {
            try {
                $hora = \Carbon\Carbon::parse($pedido->hora_entrega_sugerida);
                $horaEntrega = $hora->format('H:i');
            } catch (\Exception $e) {
                $horaEntrega = null;
            }
        }

        // Forzar es_externo en cada detalle según el EAN
        foreach ($pedido->detalles as $detalle) {
            $detalle->setAttribute('es_externo', str_starts_with($detalle->ean, 'T'));
        }

        $data = $pedido->toArray();
        $data['fecha_entrega_sugerida'] = $fechaEntrega;
        $data['hora_entrega_sugerida'] = $horaEntrega;

        $data['puede_editar'] = auth()->user()->puede('ventas', 'pedidos_anticipo', 'editar');

        return response()->json([
            'success' => true,
            'data' => $data
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
                        
                        // Calcular importe con el nuevo precio
                        $precioConDescuento = $productoData['precio_unitario'] * (1 - ($productoData['descuento'] ?? 0) / 100);
                        $importe = $productoData['cantidad'] * $precioConDescuento;
                        
                        $detalle->update([
                            'cantidad' => $productoData['cantidad'],
                            'precio_unitario' => $productoData['precio_unitario'],
                            'descuento' => $productoData['descuento'] ?? 0,
                            'importe' => $importe,
                            'id_sucursal_surtido' => $sucursalNueva,
                            'updated_at' => now()
                        ]);
                        
                        if ($sucursalOriginal != $sucursalNueva) {
                            if ($sucursalOriginal) $sucursalesAfectadas[$sucursalOriginal] = true;
                            if ($sucursalNueva) $sucursalesAfectadas[$sucursalNueva] = true;
                        }
                    }
                } else {
                    // Crear nuevo detalle en orden_pedido_detalle (viene de cotización sin editar)
                    $nuevoDetalle = OrdenPedidoDetalle::create([
                        'id_pedido' => $id,
                        'id_cotizacion_detalle' => $productoData['id_cotizacion_detalle'] ?? null,
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
        $user = auth()->user();
        
        // VERIFICAR PERMISOS BASE
        if (!$user->puede('ventas', 'pedidos_anticipo', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        // VERIFICAR QUE EL USUARIO PUEDA ASIGNAR SUCURSALES SEGÚN PERFIL
        if (!$user->puedeAsignarSucursal()) {
            return response()->json([
                'success' => false,
                'message' => 'Tu perfil no permite asignar sucursales'
            ], 403);
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
                $producto = CatalogoGeneral::where('ean', $detalle->ean)
                    ->where('id_sucursal', $asignacion['id_sucursal'])
                    ->first();
                
                if (!$producto) {
                    throw new \Exception("Producto no encontrado en la sucursal seleccionada");
                }
                
                // Calcular stock disponible (considerando otros pedidos)
                $stockApartado = $this->calcularStockApartado($detalle->ean, $asignacion['id_sucursal'], $id);
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
            \Log::error('Error al asignar sucursales: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Marcar una sucursal como lista
     * Solo para pedidos que NO tienen productos externos (EAN que empieza con 'T')
     */
    public function marcarListoSucursal(int $idPedidoSucursal, ?int $folioTicket = null): JsonResponse
    {
        $user = auth()->user();
        
        // Si los parámetros no vienen en la URL, obtenerlos del request
        if ($folioTicket === null) {
            $folioTicket = request()->input('folio_ticket');
        }
        
        // OBTENER SUCURSAL EFECTIVA SEGÚN PERFIL
        $sucursalAsignada = $user->sucursal_asignada_efectiva;
        
        // VERIFICAR PERMISOS SEGÚN PERFIL
        // Verificar permiso base de edición
        if (!$user->puede('ventas', 'pedidos_anticipo', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        // Verificar que el usuario tenga una sucursal asignada (Sucursal o Repartidor con horario)
        if ($sucursalAsignada == 0) {
            return response()->json([
                'success' => false, 
                'message' => 'No tienes una sucursal asignada para marcar como listo'
            ], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $pedidoSucursal = OrdenPedidoSucursal::with('pedido')->findOrFail($idPedidoSucursal);
            
            // VERIFICAR QUE LA SUCURSAL COINCIDA CON EL PERFIL
            if ($pedidoSucursal->id_sucursal != $sucursalAsignada) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No tienes permiso para esta sucursal'
                ], 403);
            }
            
            // VERIFICAR QUE EL USUARIO PUEDA MARCAR ESTE PEDIDO COMO LISTO
            $pedido = $pedidoSucursal->pedido;
            if (!$user->puedeMarcarListoPedido($pedido)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No tienes permiso para marcar este pedido como listo'
                ], 403);
            }
            
            // Validar que folioTicket sea un número positivo
            if ($folioTicket === null || $folioTicket <= 0) {
                return response()->json([
                    'success' => false, 
                    'message' => 'El folio ticket debe ser un número positivo'
                ], 400);
            }
            
            if ($pedidoSucursal->status == 1) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Ya fue marcado como listo'
                ], 400);
            }
            
            // ============================================
            // OBTENER TODOS LOS DETALLES DE ESTA SUCURSAL
            // ============================================
            $detalles = OrdenPedidoDetalle::where('id_pedido', $pedidoSucursal->id_pedido)
                ->where('id_sucursal_surtido', $sucursalAsignada)
                ->where('se_elimino', 0)
                ->get();
            
            // ============================================
            // SEPARAR EXTERNOS Y NORMALES
            // ============================================
            $externos = [];
            $normales = [];
            $noDisponibles = [];
            
            foreach ($detalles as $detalle) {
                if (str_starts_with($detalle->ean, 'T')) {
                    // Es un producto externo
                    $tmpProducto = TmpCatalogo::where('ean', $detalle->ean)->first();
                    
                    // Verificar si ya tiene EAN real
                    if ($tmpProducto && !str_starts_with($tmpProducto->ean, 'T')) {
                        // Ya tiene EAN real, actualizar automáticamente
                        if ($tmpProducto->ean !== $detalle->ean) {
                            $detalle->ean = $tmpProducto->ean;
                            $detalle->save();
                        }
                    } else {
                        // Necesita conversión
                        $externos[] = [
                            'id_detalle' => $detalle->id_detalle_pedido,
                            'nombre' => $tmpProducto->descripcion ?? 'Producto sobre pedido',
                            'ean_actual' => $detalle->ean,
                            'cantidad' => $detalle->cantidad
                        ];
                    }
                } else {
                    // Es un producto normal
                    $producto = CatalogoGeneral::where('ean', $detalle->ean)
                        ->where('id_sucursal', $sucursalAsignada)
                        ->first();
                    
                    if ($producto) {
                        $normales[] = $detalle;
                    } else {
                        $noDisponibles[] = $detalle->ean;
                    }
                }
            }
            
            // ============================================
            // VALIDAR PRODUCTOS NO DISPONIBLES
            // ============================================
            if (!empty($noDisponibles)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Hay productos no disponibles en esta sucursal. Contacte al administrador.',
                    'productos_no_disponibles' => $noDisponibles
                ], 400);
            }
            
            // ============================================
            // VALIDAR PRODUCTOS EXTERNOS PENDIENTES
            // ============================================
            if (!empty($externos)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Hay productos sobre pedido pendientes de conversión.',
                    'requiere_conversion' => true,
                    'productos_externos' => $externos
                ], 400);
            }
            
            // ============================================
            // MARCAR SUCURSAL COMO LISTA
            // ============================================
            $pedidoSucursal->status = 1;
            $pedidoSucursal->fecha_completado = now();
            $pedidoSucursal->folio_ticket = $folioTicket;
            $pedidoSucursal->save();

            // VERIFICAR SI TODAS LAS SUCURSALES ESTÁN LISTAS
            $todasListas = $this->verificarTodasSucursalesListas($pedidoSucursal->id_pedido);

            if ($todasListas) {
                try {
                    $pedido = $pedidoSucursal->pedido;
                    $sucursalNombre = $pedidoSucursal->sucursal->nombre ?? 'Sucursal desconocida';
                    
                    // Obtener IDs de todas las sucursales que marcaron listo (para la notificación)
                    $sucursalesListas = OrdenPedidoSucursal::where('id_pedido', $pedido->id_pedido)
                        ->where('status', 1)
                        ->with('sucursal')
                        ->get()
                        ->pluck('sucursal.nombre')
                        ->implode(', ');
                    
                    // Enviar evento con información de qué sucursales ya marcaron
                    event(new PedidoMarcadoListo($pedido, $sucursalNombre, $sucursalesListas));
                    
                } catch (\Exception $e) {
                    \Log::error('Error al enviar notificación Reverb: ' . $e->getMessage());
                }
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Sucursal marcada como lista correctamente'
            ]);
            
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
     * Verificar si todas las sucursales asignadas a un pedido ya marcaron como listo
     */
    private function verificarTodasSucursalesListas(int $pedidoId): bool
    {
        // Obtener total de sucursales asignadas a este pedido
        $totalSucursales = OrdenPedidoSucursal::where('id_pedido', $pedidoId)->count();
        
        // Obtener sucursales que ya marcaron como listo (status = 1)
        $sucursalesListas = OrdenPedidoSucursal::where('id_pedido', $pedidoId)
            ->where('status', 1)
            ->count();
        
        // Todas están listas si el número de sucursales listas es igual al total
        return $totalSucursales > 0 && $totalSucursales === $sucursalesListas;
    }

    public function verificarPermisoEditar(int $id): JsonResponse
    {
        try {
            $puedeEditar = auth()->user()->puede('ventas', 'pedidos_anticipo', 'editar');
            
            return response()->json([
                'success' => true,
                'puede_editar' => $puedeEditar
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en verificarPermisoEditar: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar permiso'
            ], 500);
        }
    }
    
    /**
     * Asignar un repartidor a uno o múltiples pedidos.
     */
    public function asignarRepartidor(Request $request): JsonResponse
    {
        $user = auth()->user();
        
        // VERIFICAR PERMISOS BASE
        if (!$user->puede('ventas', 'pedidos_anticipo', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        // VERIFICAR QUE EL USUARIO PUEDA ASIGNAR REPARTIDOR (SOLO CRM)
        if (!$user->puedeAsignarRepartidor()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo los usuarios con perfil CRM pueden asignar repartidores'
            ], 403);
        }
        
        try {
            $validated = $request->validate([
                'id_repartidor' => 'required|exists:sqlsrvM.personal_empresa,id_personal_empresa',
                'pedidos_ids' => 'required|array|min:1',
                'pedidos_ids.*' => 'exists:orden_pedido,id_pedido'
            ]);
            
            $repartidorId = $validated['id_repartidor'];
            $pedidosIds = $validated['pedidos_ids'];
            
            // VERIFICAR QUE EL REPARTIDOR SELECCIONADO TENGA PERFIL DE REPARTIDOR
            $repartidor = PersonalEmpresa::find($repartidorId);
            if (!$repartidor) {
                return response()->json([
                    'success' => false,
                    'message' => 'El repartidor seleccionado no existe'
                ], 400);
            }
            
            // Verificar que la persona seleccionada sea repartidor
            if (!$repartidor->es_repartidor) {
                return response()->json([
                    'success' => false,
                    'message' => "La persona seleccionada ({$repartidor->nombre}) no tiene perfil de repartidor"
                ], 400);
            }
            
            // Verificar que el repartidor tenga horario para hoy
            if (!$repartidor->tieneHorarioRepartidor()) {
                return response()->json([
                    'success' => false,
                    'message' => "El repartidor {$repartidor->nombre} no tiene horario asignado para hoy"
                ], 400);
            }
            
            $asignados = 0;
            $errores = [];
            
            foreach ($pedidosIds as $pedidoId) {
                $pedido = OrdenPedido::find($pedidoId);
                
                if (!$pedido) {
                    $errores[] = "Pedido ID {$pedidoId} no encontrado";
                    continue;
                }
                
                if ($pedido->status != 2) {
                    $errores[] = "Pedido {$pedido->folio_pedido} no está en proceso (status: {$pedido->status})";
                    continue;
                }
                
                // Verificar que no tenga ya un repartidor asignado
                if ($pedido->id_repartidor && $pedido->id_repartidor != $repartidorId) {
                    $errores[] = "Pedido {$pedido->folio_pedido} ya tiene un repartidor asignado";
                    continue;
                }
                
                $pedido->id_repartidor = $repartidorId;
                $pedido->save();
                
                // DISPARAR EVENTO DE NOTIFICACIÓN PARA EL REPARTIDOR
                try {
                    event(new PedidoAsignadoRepartidor($pedido, $repartidor));
                } catch (\Exception $e) {
                    \Log::error('Error al enviar notificación de asignación: ' . $e->getMessage());
                }
                
                // ELIMINAR NOTIFICACIONES DE "PEDIDO LISTO" (COMPARTIDAS Y PERSONALES)
                try {
                    // Eliminar notificaciones compartidas (id_usuario = 0)
                    $eliminadasCompartidas = Notificacion::where('tipo', 'pedido_listo')
                        ->where('id_usuario', 0)
                        ->where('datos_extra', 'LIKE', '%"pedido_id":' . $pedido->id_pedido . '%')
                        ->delete();
                    
                    // Eliminar notificaciones personales de CRM
                    $eliminadasPersonales = Notificacion::where('tipo', 'pedido_listo')
                        ->where('id_usuario', '>', 0)
                        ->where('datos_extra', 'LIKE', '%"pedido_id":' . $pedido->id_pedido . '%')
                        ->delete();
                    
                    if ($eliminadasCompartidas > 0 || $eliminadasPersonales > 0) {
                    }
                } catch (\Exception $e) {
                    \Log::error('Error al eliminar notificaciones de pedido listo: ' . $e->getMessage());
                }
                
                $asignados++;
            }
            
            $message = "{$asignados} pedido(s) asignado(s) al repartidor {$repartidor->nombre}";
            if (!empty($errores)) {
                $message .= ". Errores: " . implode(', ', $errores);
            }
            
            return response()->json([
                'success' => $asignados > 0,
                'message' => $message,
                'asignados' => $asignados,
                'errores' => $errores
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
        $user = auth()->user();
        
        // VERIFICAR PERMISOS BASE
        if (!$user->puede('ventas', 'pedidos_anticipo', 'editar')) {
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
            
            // VERIFICAR QUE EL USUARIO PUEDA ENTREGAR ESTE PEDIDO SEGÚN PERFIL
            $puedeEntregar = false;
            
            if ($user->es_crm) {
                // CRM puede entregar cualquier pedido
                $puedeEntregar = true;
            } elseif ($user->es_sucursal) {
                // Sucursal: solo si tiene productos de su sucursal
                $sucursalId = $user->sucursal_asignada_efectiva;
                $tieneProductos = $pedido->detalles()
                    ->where('se_elimino', 0)
                    ->where('id_sucursal_surtido', $sucursalId)
                    ->exists();
                $puedeEntregar = $tieneProductos;
            } elseif ($user->es_repartidor) {
                // Repartidor: solo si está asignado a él
                $puedeEntregar = ($pedido->id_repartidor == $user->id_personal_empresa);
            }
            
            if (!$puedeEntregar) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para entregar este pedido'
                ], 403);
            }
            
            // MARCAR PEDIDO COMO ENTREGADO
            $pedido->status = 3;
            $pedido->fecha_entrega_real = now();
            $pedido->save();
            
            // CERRAR RECORRIDO ACTIVO SI EXISTE
            $recorridoActivo = DB::connection('sqlsrvM')
                ->table('oper_recorridos_choferes')
                ->where('id_personal', $pedido->id_repartidor)
                ->where('status', 0)
                ->first();
            
            if ($recorridoActivo) {
                // Verificar si este pedido está en el recorrido
                $pedidoEnRecorrido = DB::connection('sqlsrv')
                    ->table('recorrido_pedidos')
                    ->where('id_recorrido', $recorridoActivo->id_recorrido)
                    ->where('id_pedido', $pedido->id_pedido)
                    ->exists();
                
                if ($pedidoEnRecorrido) {
                    // Marcar el pedido como entregado en el recorrido
                    DB::connection('sqlsrv')
                        ->table('recorrido_pedidos')
                        ->where('id_recorrido', $recorridoActivo->id_recorrido)
                        ->where('id_pedido', $pedido->id_pedido)
                        ->update([
                            'entregado' => 1,
                            'fecha_entrega' => now(),
                            'updated_at' => now()
                        ]);
                    
                    // Verificar si todos los pedidos del recorrido están entregados
                    $pedidosPendientes = DB::connection('sqlsrv')
                        ->table('recorrido_pedidos')
                        ->where('id_recorrido', $recorridoActivo->id_recorrido)
                        ->where('entregado', 0)
                        ->count();
                    
                    if ($pedidosPendientes === 0) {
                        // Todos los pedidos entregados, finalizar recorrido
                        DB::connection('sqlsrvM')
                            ->table('oper_recorridos_choferes')
                            ->where('id_recorrido', $recorridoActivo->id_recorrido)
                            ->update([
                                'status' => 1,
                                'fecha_fin' => now()
                            ]);
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Pedido marcado como entregado'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al marcar entregado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar como entregado: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Cancelar orden (soft delete) con motivo.
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
            
            // Validar que el motivo esté presente
            $motivo = request()->input('motivo');
            if (empty($motivo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe proporcionar un motivo para la cancelación'
                ], 400);
            }
            
            // Guardar en pedido_cancelado
            PedidoCancelado::create([
                'id_pedido' => $pedido->id_pedido,
                'motivo' => $motivo,
                'cancelado_por' => auth()->id(),
                'fecha_cancelacion' => now()
            ]);
            
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
            \Log::error('Error al cancelar pedido: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar el pedido: ' . $e->getMessage()
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
        
        $pedido = OrdenPedido::with([
            'cotizacion' => function($q) {
                $q->with(['cliente', 'sucursalAsignada']);
            },
            'detalles' => function($q) {
                $q->where('se_elimino', 0);
            },
            'detalles.sucursalSurtido',
            'sucursales.sucursal',
            'repartidor'
        ])->findOrFail($id);
        
        // AGRUPAR PRODUCTOS POR EAN
        $productosAgrupados = [];
        
        foreach ($pedido->detalles as $detalle) {
            $ean = $detalle->ean;
            $esExternoPorEAN = str_starts_with($ean, 'T');
            $nombreProducto = '';
            $codbar = $ean;
            $esExterno = 0;
            
            // Buscar información del producto
            if (!$esExternoPorEAN) {
                $producto = CatalogoGeneral::where('ean', $ean)->first();
                if ($producto) {
                    $nombreProducto = $producto->descripcion;
                    $codbar = $producto->ean ?? $ean;
                    $esExterno = 0;
                }
            }
            
            // Si es externo o no se encontró en catalogo_general
            if (empty($nombreProducto)) {
                $productoExterno = TmpCatalogo::where('ean', $ean)->first();
                if ($productoExterno) {
                    $nombreProducto = $productoExterno->descripcion;
                    $codbar = $ean;
                    $esExterno = 1;
                } else {
                    $producto = CatalogoGeneral::where('ean', $ean)->first();
                    if ($producto) {
                        $nombreProducto = $producto->descripcion;
                        $codbar = $producto->ean ?? $ean;
                        $esExterno = 0;
                    } else {
                        $nombreProducto = 'Producto no disponible';
                        $codbar = $ean;
                        $esExterno = 0;
                    }
                }
            }
            
            $key = $ean;
            if (!isset($productosAgrupados[$key])) {
                $productosAgrupados[$key] = [
                    'ean' => $ean,
                    'codbar' => $codbar,
                    'nombre' => $nombreProducto,
                    'cantidad' => 0,
                    'precio_unitario' => $detalle->precio_unitario,
                    'descuento' => $detalle->descuento,
                    'es_externo' => $esExterno,
                    'sucursal' => $detalle->sucursalSurtido
                ];
            }
            $productosAgrupados[$key]['cantidad'] += $detalle->cantidad;
        }
        
        // Reemplazar detalles con productos agrupados
        $pedido->detalles = collect(array_values($productosAgrupados));
        
        $pdf = Pdf::loadView('ventas.pedidos.pdf', compact('pedido'));
        $pdf->setPaper('letter', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
        ]);
        
        return $pdf->stream("Pedido_{$pedido->folio_pedido}.pdf");
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
    public function stockPorSucursal(Request $request): JsonResponse
    {
        try {
            $ean = $request->input('ean');
            
            if (empty($ean)) {
                return response()->json(['success' => false, 'message' => 'EAN requerido'], 400);
            }
            
            $productos = CatalogoGeneral::where('ean', $ean)
                ->where('activo', 1)
                ->with('sucursal')
                ->get();
            
            $data = [];
            foreach ($productos as $producto) {
                if ($producto->sucursal) {
                    $data[] = [
                        'id_sucursal' => $producto->id_sucursal,
                        'nombre' => $producto->sucursal->nombre,
                        'inventario' => (float) $producto->inventario
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en stockPorSucursal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar stock'
            ], 500);
        }
    }

    public function stockGlobal(Request $request): JsonResponse
    {
        try {
            $ean = $request->input('ean');
            
            if (empty($ean)) {
                return response()->json(['success' => false, 'message' => 'EAN requerido'], 400);
            }
            
            // Obtener stock por sucursal para este EAN
            $productos = CatalogoGeneral::where('ean', $ean)
                ->where('activo', 1)
                ->with('sucursal')
                ->get();
            
            $data = [];
            foreach ($productos as $producto) {
                if ($producto->sucursal) {
                    $data[] = [
                        'id_sucursal' => $producto->id_sucursal,
                        'nombre' => $producto->sucursal->nombre,
                        'inventario' => $producto->inventario ?? 0
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en nventarioGlobal: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al consultar inventario global'
            ], 500);
        }
    }
        
    /**
     * Calcular el stock apartado para un producto en una sucursal.
     */
    private function calcularStockApartado(string $ean, ?int $pedidoId = null, ?int $detalleId = null): int
    {
        $query = DB::connection('sqlsrv')->table('crm_cotizaciones_detalle as cd')
            ->join('crm_cotizaciones as c', 'cd.id_cotizacion', '=', 'c.id_cotizacion')
            ->join('orden_pedido as op', 'c.id_cotizacion', '=', 'op.id_cotizacion')
            ->where('cd.codbar', $ean)
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
        
        // Determinar si es repartidor (tiene horario para hoy)
        $esRepartidor = $this->esRepartidor($usuarioId);
        
        $permisos = [
            'ver' => auth()->user()->puede('ventas', 'pedidos_anticipo', 'ver'),
            'crear' => auth()->user()->puede('ventas', 'pedidos_anticipo', 'crear'),
            'editar' => auth()->user()->puede('ventas', 'pedidos_anticipo', 'editar'),
            'eliminar' => auth()->user()->puede('ventas', 'pedidos_anticipo', 'eliminar'),
        ];
        
        $esUsuarioSucursal = ($sucursalAsignada > 0 && !$esRepartidor);
        
        $tieneAcceso = $esRepartidor || 
                    ($sucursalAsignada == 0 && $permisos['crear']) ||
                    ($esUsuarioSucursal && $permisos['crear']);
        
        if (!$tieneAcceso) {
            abort(403, 'No tienes permiso para acceder a esta sección');
        }
        
        if ($esRepartidor && $pedido->id_repartidor != $usuarioId) {
            abort(403, 'Este pedido no está asignado a ti');
        }
        
        if ($esUsuarioSucursal) {
            $tieneProducto = OrdenPedidoDetalle::where('id_pedido', $id)
                ->where('id_sucursal_surtido', $sucursalAsignada)
                ->where('se_elimino', 0)
                ->exists();
            
            if (!$tieneProducto) {
                abort(403, 'No tienes productos asignados en este pedido para tu sucursal');
            }
        }
        
        $puedeIniciarRecorrido = $esRepartidor && $permisos['crear'];
        $sucursales = Sucursal::where('activo', 1)->get();
        
        return view('ventas.pedidos.asignar-repartidor', compact('pedido', 'sucursalAsignada', 'esRepartidor', 'sucursales', 'permisos', 'puedeIniciarRecorrido'));
    }

    /**
     * Obtener repartidores con su status actualizado para la vista de asignación
     */
    public function repartidoresConStatus(int $pedidoId): JsonResponse
    {
        try {
            $user = auth()->user();
            $sucursalAsignada = $user->sucursal_asignada ?? 0;
            $usuarioId = $user->id_personal_empresa;

            // OBTENER PERFILES DEL USUARIO
            $esCRM = $user->es_crm;
            $esSucursal = $user->es_sucursal;
            $esRepartidor = $user->es_repartidor;
            
            $tienePermisoVer = $user->puede('ventas', 'pedidos_anticipo', 'ver');
            $tienePermisoCrear = $user->puede('ventas', 'pedidos_anticipo', 'crear');
            $tienePermisoEditar = $user->puede('ventas', 'pedidos_anticipo', 'editar');

            // VERIFICAR ACCESO SEGÚN PERFIL
            $tieneAcceso = false;
            
            if ($esRepartidor) {
                // Repartidor siempre tiene acceso a sus propios datos
                $tieneAcceso = true;
            } elseif ($sucursalAsignada > 0 && $tienePermisoCrear) {
                // Usuario de sucursal con permisos
                $tieneAcceso = true;
            } elseif ($sucursalAsignada == 0 && ($tienePermisoCrear || $tienePermisoEditar)) {
                // CRM con permisos
                $tieneAcceso = true;
            }
            
            if (!$tieneAcceso) {
                return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
            }
            
            // Validaciones de pedido...
            if ($pedidoId > 0) {
                if ($esRepartidor) {
                    $pedido = OrdenPedido::find($pedidoId);
                    if (!$pedido || $pedido->id_repartidor != $usuarioId) {
                        return response()->json(['success' => false, 'message' => 'Este pedido no está asignado a ti'], 403);
                    }
                }
                
                if ($sucursalAsignada > 0 && !$esRepartidor) {
                    $tieneProducto = OrdenPedidoDetalle::where('id_pedido', $pedidoId)
                        ->where('id_sucursal_surtido', $sucursalAsignada)
                        ->where('se_elimino', 0)
                        ->exists();
                    
                    if (!$tieneProducto) {
                        return response()->json(['success' => false, 'message' => 'No tienes productos asignados en este pedido para tu sucursal'], 403);
                    }
                }
            }
            
            $hoy = now()->format('Y-m-d');
            
            // CONSULTA DE REPARTIDORES CON HORARIO
            $repartidoresQuery = PersonalEmpresa::whereIn('id_personal_empresa', function($q) use ($hoy) {
                $q->select('id_personal')
                    ->from('rh_personal_servicios_domicilio')
                    ->where('fecha', $hoy);
            });
            
            // FILTROS SEGÚN PERFIL DEL USUARIO ACTUAL
            if ($esRepartidor && !$esCRM) {
                // Solo Repartidor (sin CRM): solo ve su propio horario
                $repartidoresQuery->where('id_personal_empresa', $usuarioId);
            } elseif ($esSucursal && !$esCRM) {
                // Solo Sucursal (sin CRM): solo ve repartidores de su sucursal
                $repartidoresQuery->where('sucursal_asignada', $sucursalAsignada);
            } elseif ($esCRM) {
                // CRM (con o sin otros perfiles): ve todos los repartidores
                // No se aplica filtro adicional
            } else {
                // Compatibilidad: si tiene sucursal asignada pero no es CRM ni Repartidor
                if ($sucursalAsignada > 0) {
                    $repartidoresQuery->where('sucursal_asignada', $sucursalAsignada);
                }
            }
            
            $repartidores = $repartidoresQuery->get();
            
            $horaActual = now()->format('H:i:s');
            $repartidoresConStatus = [];
            
            foreach ($repartidores as $repartidor) {
                $horario = DB::connection('sqlsrvM')->table('rh_personal_servicios_domicilio')
                    ->select('hora_entrada', 'hora_salida', 'fecha')
                    ->where('id_personal', $repartidor->id_personal_empresa)
                    ->where('fecha', $hoy)
                    ->first();
                
                $recorridoActivo = DB::connection('sqlsrvM')->table('oper_recorridos_choferes')
                    ->where('id_personal', $repartidor->id_personal_empresa)
                    ->where('status', 0)
                    ->first();
                
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
                    'horario_entrada' => $horario ? substr($horario->hora_entrada, 0, 5) : null,
                    'horario_salida' => $horario ? substr($horario->hora_salida, 0, 5) : null,
                    'status' => $status
                ];
            }
            
            // OBTENER ENTREGAS EN CURSO
            $entregasQuery = DB::connection('sqlsrvM')->table('oper_recorridos_choferes as rc')
                ->join('personal_empresa as pe', 'rc.id_personal', '=', 'pe.id_personal_empresa')
                ->where('rc.status', 0);
            
            // FILTROS SEGÚN PERFIL PARA ENTREGAS EN CURSO
            if ($esRepartidor && !$esCRM) {
                // Solo Repartidor (sin CRM): solo ve sus entregas
                $entregasQuery->where('rc.id_personal', $usuarioId);
            } elseif ($esSucursal && !$esCRM) {
                // Solo Sucursal (sin CRM): solo ve entregas de su sucursal
                $entregasQuery->where('pe.sucursal_asignada', $sucursalAsignada);
            } elseif ($esCRM) {
                // CRM (con o sin otros perfiles): ve todas las entregas
                // No se aplica filtro adicional
            } else {
                // Compatibilidad
                if ($sucursalAsignada > 0) {
                    $entregasQuery->where('pe.sucursal_asignada', $sucursalAsignada);
                }
            }

            $entregasEnCurso = $entregasQuery->select(
                'rc.id',
                'rc.folio_ticket',
                'pe.Nombre as repartidor_nombre',
                'pe.apPaterno as repartidor_apaterno',
                'rc.nombrecliente',
                'rc.Domicilio',
                'rc.hora_salida'
            )->get();
            
            return response()->json([
                'success' => true,
                'repartidores' => $repartidoresConStatus,
                'entregas_curso' => $entregasEnCurso,
                'es_repartidor' => $esRepartidor,
                'es_usuario_sucursal' => ($sucursalAsignada > 0 && !$esRepartidor),
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

    public function vistaRecorridoRepartidor(): View
    {
        $user = auth()->user();
        $usuarioId = $user->id_personal_empresa;
        
        $esRepartidor = $user->es_repartidor;
        $esCRM = $user->es_crm;
        $esSucursal = $user->es_sucursal;
        
        if (!$esRepartidor) {
            abort(403, 'No tienes permiso');
        }
        
        // Permisos para la vista
        $permisos = [
            'ver' => $user->puede('ventas', 'pedidos_anticipo', 'ver'),
            'crear' => $user->puede('ventas', 'pedidos_anticipo', 'crear'),
            'editar' => $user->puede('ventas', 'pedidos_anticipo', 'editar'),
            'eliminar' => $user->puede('ventas', 'pedidos_anticipo', 'eliminar'),
        ];
        
        // Repartidor con horario puede iniciar recorrido
        $puedeIniciarRecorrido = $user->tieneHorarioRepartidor();
        
        // Crear pedido virtual
        $pedido = new \stdClass();
        $pedido->id_pedido = 0;
        $pedido->folio_pedido = 'MIS PEDIDOS';
        $pedido->importe_total = 0;
        $pedido->id_repartidor = $usuarioId;
        $pedido->cotizacion = new \stdClass();
        $pedido->cotizacion->nombre_cliente = 'Selecciona tus pedidos';
        
        $sucursalAsignada = $user->sucursal_asignada_efectiva;
        $sucursales = Sucursal::where('activo', 1)->get();
        $modoSoloLectura = false;
        
        return view('ventas.pedidos.asignar-repartidor', compact(
            'pedido', 'sucursalAsignada', 'esRepartidor', 'sucursales', 
            'permisos', 'modoSoloLectura', 'puedeIniciarRecorrido',
            'esCRM', 'esSucursal'
        ));
    }

    /**
     * Verifica si el usuario es repartidor (tiene horario para hoy)
     * Ahora combina: permiso es_repartidor + horario en rh_personal_servicios_domicilio
     */
    public function esRepartidor($usuarioId = null): bool
    {
        $usuarioId = $usuarioId ?? auth()->id();
        $user = PersonalEmpresa::find($usuarioId);
        
        if (!$user) {
            return false;
        }
        
        // Usar el nuevo método del modelo (permiso + horario)
        return $user->es_repartidor;
    }

    /**
     * Iniciar un nuevo recorrido con múltiples pedidos.
     */
    public function iniciarRecorrido(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'pedidos' => 'required|array|min:1',
                'pedidos.*.id_pedido' => 'required|integer|exists:orden_pedido,id_pedido',
                'pedidos.*.folio_ticket' => 'required|integer|min:0',
                'pedidos.*.nombrecliente' => 'required|string',
                'pedidos.*.Domicilio' => 'required|string',
                'pedidos.*.importeticket' => 'required|numeric|min:0',
                'pedidos.*.sucursal' => 'required|integer',
                'kminicial' => 'required|integer|min:0',
                'hora_salida' => 'required|string'
            ]);
            
            // Verificar que el usuario sea repartidor
            $usuarioId = auth()->id();
            $esRepartidor = DB::connection('sqlsrvM')->table('rh_personal_servicios_domicilio')
                ->where('id_personal', $usuarioId)
                ->exists();
            
            if (!$esRepartidor) {
                return response()->json(['success' => false, 'message' => 'No eres un repartidor autorizado'], 403);
            }
            
            // Verificar que no tenga ningún recorrido activo
            $recorridoActivo = DB::connection('sqlsrvM')->table('oper_recorridos_choferes')
                ->where('id_personal', $usuarioId)
                ->where('status', 0)
                ->exists();

            if ($recorridoActivo) {
                return response()->json(['success' => false, 'message' => 'Ya tienes un recorrido activo. Finalízalo primero.'], 400);
            }
            
            $kminicial = $validated['kminicial'];
            $horaSalida = $validated['hora_salida'];
            $fecha = now()->toDateString();
            $pedidosGuardados = 0;
            $errores = [];
            
            foreach ($validated['pedidos'] as $index => $pedidoData) {
                $pedido = OrdenPedido::where('id_pedido', $pedidoData['id_pedido'])
                    ->where('id_repartidor', $usuarioId)
                    ->where('status', 2)
                    ->first();
                
                if (!$pedido) {
                    $errores[] = "Pedido ID {$pedidoData['id_pedido']} no existe, no está asignado a ti o ya fue entregado";
                    continue;
                }
                
                // Guardar en oper_recorridos_choferes (datos del recorrido por pedido)
                $idRecorrido = DB::connection('sqlsrvM')->table('oper_recorridos_choferes')->insertGetId([
                    'id_personal' => $usuarioId,
                    'fecha' => $fecha,
                    'folio_ticket' => $pedidoData['folio_ticket'],
                    'importeticket' => $pedidoData['importeticket'],
                    'nombrecliente' => $pedidoData['nombrecliente'],
                    'Domicilio' => $pedidoData['Domicilio'],
                    'kminicial' => $kminicial,
                    'Solicitadoensucursal' => $pedidoData['sucursal'],
                    'hora_salida' => $horaSalida,
                    'status' => 0,
                ]);
                
                // Guardar en tabla pivote (relación recorrido-pedido)
                DB::connection('sqlsrv')->table('recorrido_pedidos')->insert([
                    'id_recorrido' => $idRecorrido,
                    'id_pedido' => $pedido->id_pedido,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $pedidosGuardados++;
            }
            
            if ($pedidosGuardados === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo iniciar el recorrido. ' . implode(', ', $errores)
                ], 400);
            }
            
            return response()->json([
                'success' => true,
                'message' => "Recorrido iniciado correctamente con {$pedidosGuardados} pedido(s)",
                'pedidos_guardados' => $pedidosGuardados,
                'errores' => $errores
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . json_encode($e->errors())
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error al iniciar recorrido: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar recorrido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalizar recorrido y marcar TODOS los pedidos del recorrido como entregados.
     */
    public function finalizarRecorrido(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            
            $usuarioId = auth()->id();
            
            $validated = $request->validate([
                'kmfinal' => 'required|integer|min:0',
                'recorridos_ids' => 'required|array|min:1',
                'recorridos_ids.*' => 'integer',
                'hora_regreso' => 'required|string'
            ]);
            
            $kmFinal = $validated['kmfinal'];
            $recorridosIds = $validated['recorridos_ids'];
            $horaRegreso = $validated['hora_regreso'];
            
            // Obtener los recorridos activos del repartidor
            $recorridosActivos = DB::connection('sqlsrvM')->table('oper_recorridos_choferes')
                ->where('id_personal', $usuarioId)
                ->where('status', 0)
                ->whereIn('id', $recorridosIds)
                ->get();
            
            if ($recorridosActivos->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No se encontraron recorridos activos para finalizar'], 400);
            }
            
            $pedidosActualizados = 0;
            
            foreach ($recorridosActivos as $recorrido) {
                // Obtener los pedidos asociados a este recorrido desde la tabla pivote
                $pedidosIds = DB::connection('sqlsrv')
                    ->table('recorrido_pedidos')
                    ->where('id_recorrido', $recorrido->id)
                    ->pluck('id_pedido')
                    ->toArray();
                
                // Actualizar los pedidos a status 3 (Entregado)
                $actualizados = OrdenPedido::whereIn('id_pedido', $pedidosIds)
                    ->where('status', 2)
                    ->update([
                        'status' => 3,
                        'fecha_entrega_real' => now()
                    ]);
                
                $pedidosActualizados += $actualizados;
                
                // Actualizar el registro del recorrido
                DB::connection('sqlsrvM')->table('oper_recorridos_choferes')
                    ->where('id', $recorrido->id)
                    ->update([
                        'kmfinal' => $kmFinal,
                        'hora_regreso' => $horaRegreso,
                        'status' => 1,
                    ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Recorrido finalizado. {$pedidosActualizados} pedido(s) marcado(s) como entregados",
                'pedidos_actualizados' => $pedidosActualizados
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . json_encode($e->errors())
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al finalizar recorrido: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al finalizar recorrido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener los pedidos pendientes asignados al repartidor.
     */
    public function pedidosPendientesRepartidor(): JsonResponse
    {
        try {
            $usuarioId = auth()->id();
            
            $esRepartidor = $this->esRepartidor($usuarioId);
            
            if (!$esRepartidor) {
                return response()->json(['success' => false, 'message' => 'No eres un repartidor'], 403);
            }
            
            // Obtener IDs de recorridos activos para este repartidor
            $recorridosActivos = DB::connection('sqlsrvM')->table('oper_recorridos_choferes')
                ->where('id_personal', $usuarioId)
                ->where('status', 0)
                ->pluck('id')
                ->toArray();
            
            // Obtener IDs de pedidos que ya están en recorridos activos
            $pedidosEnRecorrido = [];
            if (!empty($recorridosActivos)) {
                $pedidosEnRecorrido = DB::connection('sqlsrv')
                    ->table('recorrido_pedidos')
                    ->whereIn('id_recorrido', $recorridosActivos)
                    ->pluck('id_pedido')
                    ->toArray();
            }
            
            // Obtener pedidos asignados a este repartidor, en proceso, sin recorrido activo
            $pedidos = OrdenPedido::with(['cotizacion.cliente', 'sucursales.sucursal', 'detalles.sucursalSurtido'])
                ->where('id_repartidor', $usuarioId)
                ->where('status', 2)
                ->when(!empty($pedidosEnRecorrido), function($query) use ($pedidosEnRecorrido) {
                    return $query->whereNotIn('id_pedido', $pedidosEnRecorrido);
                })
                ->whereHas('sucursales', function($q) {
                    $q->where('status', 1);
                })
                ->whereDoesntHave('sucursales', function($q) {
                    $q->where('status', 0);
                })
                ->orderBy('created_at', 'asc')
                ->get();
            
            // Formatear datos para la vista
            $pedidosFormateados = [];
            foreach ($pedidos as $pedido) {
                // Obtener sucursales únicas de los detalles
                $sucursales = $pedido->detalles
                    ->where('se_elimino', 0)
                    ->whereNotNull('id_sucursal_surtido')
                    ->map(function($detalle) {
                        return [
                            'id_sucursal' => $detalle->id_sucursal_surtido,
                            'nombre' => $detalle->sucursalSurtido->nombre ?? 'Sin nombre'
                        ];
                    })
                    ->unique('id_sucursal')
                    ->values()
                    ->toArray();
                
                // Verificar si todas las sucursales están listas
                $todasSucursalesListas = true;
                foreach ($pedido->sucursales as $sucursalPedido) {
                    if ($sucursalPedido->status != 1) {
                        $todasSucursalesListas = false;
                        break;
                    }
                }
                
                // Solo incluir pedidos con todas las sucursales listas
                if (!$todasSucursalesListas) {
                    continue;
                }
                
                // Obtener datos del cliente de manera segura
                $nombreCliente = 'N/A';
                $domicilio = 'N/A';
                $importeticket = 0;
                
                if ($pedido->cotizacion) {
                    $nombreCliente = $pedido->cotizacion->nombre_cliente ?? 'N/A';
                    $importeticket = $pedido->detalles->sum('importe');
                    
                    if ($pedido->cotizacion->cliente) {
                        $domicilio = $pedido->cotizacion->cliente->Domicilio ?? 'N/A';
                    }
                }
                
                // Usar reset() para obtener el primer elemento del array
                $primerSucursal = reset($sucursales);
                $sucursalId = $primerSucursal['id_sucursal'] ?? 0;
                
                $pedidosFormateados[] = [
                    'id_pedido' => $pedido->id_pedido,
                    'folio_pedido' => $pedido->folio_pedido,
                    'folio_ticket' => $pedido->sucursales->first()?->folio_ticket ?? null,
                    'nombrecliente' => $nombreCliente,
                    'Domicilio' => $domicilio,
                    'importeticket' => $importeticket,
                    'sucursal' => $sucursalId,
                    'sucursales' => $sucursales,
                    'sucursales_listas' => $todasSucursalesListas
                ];
            }
            
            return response()->json([
                'success' => true,
                'pedidos' => $pedidosFormateados,
                'total_pendientes' => count($pedidosFormateados)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en pedidosPendientesRepartidor: ' . $e->getMessage() . ' - Line: ' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar pedidos pendientes: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener los pedidos pendientes para el CRM (solo pedidos con todas las sucursales listas)
     */
    public function pedidosPendientesCRM(): JsonResponse
    {
        try {
            // Obtener pedidos en proceso (status=2), sin repartidor asignado
            $pedidos = OrdenPedido::with(['cotizacion.cliente', 'sucursales.sucursal', 'detalles.sucursalSurtido'])
                ->where('status', 2)
                ->whereNull('id_repartidor')
                ->whereHas('sucursales', function($q) {
                    $q->where('status', 1);
                })
                ->whereDoesntHave('sucursales', function($q) {
                    $q->where('status', 0);
                })
                ->orderBy('created_at', 'asc')
                ->get();

            $pedidosFormateados = [];
            foreach ($pedidos as $pedido) {
                // Obtener sucursales únicas de los detalles
                $sucursales = $pedido->detalles
                    ->where('se_elimino', 0)
                    ->whereNotNull('id_sucursal_surtido')
                    ->map(function($detalle) {
                        return [
                            'id_sucursal' => $detalle->id_sucursal_surtido,
                            'nombre' => $detalle->sucursalSurtido->nombre ?? 'Sin nombre'
                        ];
                    })
                    ->unique('id_sucursal')
                    ->values()
                    ->toArray();
                
                // Verificar si todas las sucursales están listas
                $todasSucursalesListas = true;
                foreach ($pedido->sucursales as $sucursalPedido) {
                    if ($sucursalPedido->status != 1) {
                        $todasSucursalesListas = false;
                        break;
                    }
                }

                if (!$todasSucursalesListas) {
                    continue;
                }

                // Obtener el folio_ticket desde la primera sucursal
                $primerSucursal = $pedido->sucursales->first();
                $folioTicket = $primerSucursal ? $primerSucursal->folio_ticket : null;

                $nombreCliente = 'N/A';
                $domicilio = 'N/A';
                $importeticket = 0;

                if ($pedido->cotizacion) {
                    $nombreCliente = $pedido->cotizacion->nombre_cliente ?? 'N/A';
                    $importeticket = $pedido->detalles->sum('importe');
                    
                    if ($pedido->cotizacion->cliente) {
                        $domicilio = $pedido->cotizacion->cliente->Domicilio ?? 'N/A';
                    }
                }

                $primerSucursalArray = reset($sucursales);
                $sucursalId = $primerSucursalArray['id_sucursal'] ?? 0;

                $pedidosFormateados[] = [
                    'id_pedido' => $pedido->id_pedido,
                    'folio_pedido' => $pedido->folio_pedido,
                    'folio_ticket' => $folioTicket,
                    'nombrecliente' => $nombreCliente,
                    'Domicilio' => $domicilio,
                    'importeticket' => $importeticket,
                    'sucursal' => $sucursalId,
                    'sucursales' => $sucursales,
                    'sucursales_listas' => $todasSucursalesListas
                ];
            }

            return response()->json([
                'success' => true,
                'pedidos' => $pedidosFormateados
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en pedidosPendientesCRM: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar pedidos pendientes: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Verificar si el usuario puede marcar la sucursal como listo.
     */
    private function usuarioPuedeMarcarListo(OrdenPedido $pedido): bool
    {
        $sucursalAsignada = auth()->user()->sucursal_asignada_efectiva ?? 0;
        
        if ($sucursalAsignada == 0) {
            return false;
        }
        
        // Verificar si existe un registro en orden_pedido_sucursal para esta sucursal y pedido
        $pedidoSucursal = OrdenPedidoSucursal::where('id_pedido', $pedido->id_pedido)
            ->where('id_sucursal', $sucursalAsignada)
            ->first();
        
        return $pedidoSucursal && $pedidoSucursal->status == 0;
    }

    /**
     * Vista para asignación múltiple de repartidores (sin pedido específico)
     */
    public function vistaAsignacionMultiple(): View
    {
        $user = auth()->user();
        $sucursalAsignada = $user->sucursal_asignada ?? 0;
        
        // OBTENER PERFILES DEL USUARIO
        $esCRM = $user->es_crm;
        $esSucursal = $user->es_sucursal;
        $esRepartidor = $user->es_repartidor;
        
        // Verificar permiso de CREAR
        $tienePermisoCrear = $user->puede('ventas', 'pedidos_anticipo', 'crear');
        if (!$tienePermisoCrear) {
            abort(403, 'No tienes permiso para acceder a esta sección');
        }

        // Definir permisos para la vista
        $permisos = [
            'ver' => $user->puede('ventas', 'pedidos_anticipo', 'ver'),
            'crear' => $tienePermisoCrear,
            'editar' => $user->puede('ventas', 'pedidos_anticipo', 'editar'),
            'eliminar' => $user->puede('ventas', 'pedidos_anticipo', 'eliminar'),
        ];

        // Crear pedido virtual
        $pedido = new \stdClass();
        $pedido->id_pedido = 0;
        $pedido->folio_pedido = 'Selecciona pedidos';
        $pedido->importe_total = 0;
        $pedido->id_repartidor = null;
        $pedido->cotizacion = new \stdClass();

        $sucursales = Sucursal::where('activo', 1)->get();
        
        // DETERMINAR MODO SOLO LECTURA SEGÚN PERFIL
        $modoSoloLectura = $esSucursal && !$esCRM;
        if ($esRepartidor) {
            $modoSoloLectura = false;
        }
        
        // DETERMINAR SI PUEDE INICIAR RECORRIDO
        // Repartidor con horario puede iniciar recorrido
        $puedeIniciarRecorrido = $esRepartidor && $user->tieneHorarioRepartidor();
        
        if ($modoSoloLectura) {
            $pedido->cotizacion->nombre_cliente = 'Pedidos de tu sucursal';
        } else {
            $pedido->cotizacion->nombre_cliente = 'Múltiples pedidos';
        }

        return view('ventas.pedidos.asignar-repartidor', compact(
            'pedido', 'sucursalAsignada', 'esRepartidor', 'sucursales', 
            'permisos', 'modoSoloLectura', 'esCRM', 'esSucursal', 'puedeIniciarRecorrido'
        ));
    }

    /**
     * Obtener los productos externos (tmp_catalogo) de un pedido para conversión de EAN
     */
    public function productosExternos(int $pedidoId, Request $request): JsonResponse
    {
        try {
            $sucursalId = $request->input('sucursal_id');
            
            // Consulta sin filtro de sucursal para ver si existe el externo
            $querySinFiltro = OrdenPedidoDetalle::where('id_pedido', $pedidoId)
                ->where('se_elimino', 0)
                ->where('ean', 'LIKE', 'T%')
                ->get();
            
            // Consulta con filtro de sucursal
            $query = OrdenPedidoDetalle::where('id_pedido', $pedidoId)
                ->where('se_elimino', 0)
                ->where('ean', 'LIKE', 'T%');
            
            // Filtrar por sucursal si se proporciona
            if ($sucursalId) {
                $query->where('id_sucursal_surtido', $sucursalId);
            }
            
            $detalles = $query->get();
            
            $externos = [];
            foreach ($detalles as $detalle) {
                $tmpProducto = TmpCatalogo::where('ean', $detalle->ean)->first();
                
                if (!$tmpProducto) {
                    $externos[] = [
                        'id_detalle' => $detalle->id_detalle_pedido,
                        'descripcion' => 'Sobre Pedido (no encontrado en catálogo)',
                        'ean_original' => $detalle->ean,
                        'cantidad' => $detalle->cantidad
                    ];
                    continue;
                }
                
                if (!str_starts_with($tmpProducto->ean, 'T')) {
                    if ($tmpProducto->ean !== $detalle->ean) {
                        $detalle->ean = $tmpProducto->ean;
                        $detalle->save();
                    }
                    continue;
                }
                
                $externos[] = [
                    'id_detalle' => $detalle->id_detalle_pedido,
                    'descripcion' => $tmpProducto->descripcion ?? 'Producto sobre pedido',
                    'ean_original' => $detalle->ean,
                    'cantidad' => $detalle->cantidad
                ];
            }
            
            return response()->json([
                'success' => true,
                'data' => $externos
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en productosExternos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar productos externos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Marcar productos externos como listos (convertir EAN) y luego marcar la sucursal como lista
     */
    public function marcarListoConEAN(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            $sucursalAsignada = $user->sucursal_asignada ?? 0;

            if ($sucursalAsignada == 0) {
                return response()->json(['success' => false, 'message' => 'Accion solo para usuarios de sucursal'], 403);
            }

            $validated = $request->validate([
                'pedido_id' => 'required|integer|exists:orden_pedido,id_pedido',
                'productos_externos' => 'nullable|array',
                'productos_externos.*.id_detalle' => 'required|integer',
                'productos_externos.*.nuevo_ean' => 'required|string|max:20',
                'folio_ticket' => 'required|integer|min:1'
            ]);

            $pedidoId = $validated['pedido_id'];
            $pedido = OrdenPedido::with(['sucursales'])->findOrFail($pedidoId);

            $sucursalPedido = $pedido->sucursales->firstWhere('id_sucursal', $sucursalAsignada);
            if (!$sucursalPedido) {
                return response()->json(['success' => false, 'message' => 'Esta sucursal no tiene productos en este pedido'], 400);
            }

            $folioTicket = $request->input('folio_ticket');

            // Validar que sea un número positivo
            if ($folioTicket <= 0) {
                return response()->json(['success' => false, 'message' => 'El folio ticket debe ser un número positivo'], 400);
            }

            if ($sucursalPedido->status == 1) {
                return response()->json(['success' => false, 'message' => 'Esta sucursal ya fue marcada como lista'], 400);
            }

            // 1. CONVERTIR EANs DE PRODUCTOS EXTERNOS
            $conversionesExitosas = 0;

            if (!empty($validated['productos_externos'])) {
                foreach ($validated['productos_externos'] as $producto) {
                    $detallePedido = OrdenPedidoDetalle::find($producto['id_detalle']);
                    
                    if ($detallePedido && $detallePedido->id_pedido == $pedidoId) {
                        $eanAnterior = $detallePedido->ean;
                        $nuevoEan = $producto['nuevo_ean'];

                        if (str_starts_with($eanAnterior, 'T')) {
                            // Actualizar EAN en orden_pedido_detalle
                            $detallePedido->ean = $nuevoEan;
                            $detallePedido->save();

                            // Gestionar tmp_catalogo
                            $tmpProducto = TmpCatalogo::where('ean', $eanAnterior)->first();
                            if ($tmpProducto) {
                                // Actualizar el EAN del temporal al nuevo EAN
                                $tmpProducto->ean = $nuevoEan;
                                // Siempre marcar el temporal como inactivo
                                $tmpProducto->activo = 0;
                                $tmpProducto->save();
                            }

                            // Actualizar EAN en cotizacion_detalle
                            if ($detallePedido->id_cotizacion_detalle) {
                                $cotizacionDetalle = CotizacionDetalle::find($detallePedido->id_cotizacion_detalle);
                                if ($cotizacionDetalle) {
                                    $cotizacionDetalle->codbar = $nuevoEan;
                                    $cotizacionDetalle->save();
                                } else {
                                    \Log::warning("CotizacionDetalle no encontrado para ID: {$detallePedido->id_cotizacion_detalle}");
                                }
                            } else {
                                \Log::warning("OrdenPedidoDetalle {$detallePedido->id_detalle_pedido} no tiene id_cotizacion_detalle");
                            }

                            $conversionesExitosas++;
                        }
                    }
                }
            }

            // 2. MARCAR SUCURSAL COMO LISTA (DENTRO DE LA MISMA TRANSACCIÓN)
            $this->marcarListoSucursal(
                $sucursalPedido->id_pedido_sucursal, 
                $validated['folio_ticket']
            );

            // 3. CONFIRMAR AMBAS OPERACIONES
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Sucursal marcada como lista correctamente. {$conversionesExitosas} producto(s) convertidos."
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . json_encode($e->errors())
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al marcar listo con EAN: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convertir un producto temporal (EAN que empieza con T) a EAN real
     * y actualizar todos los registros relacionados
     */
    private function convertirProductoTemporal(string $eanTemporal, string $eanReal, ?int $idSucursal = null): bool
    {
        try {
            // 1. Buscar el producto temporal (para obtener sus datos)
            $tmpProducto = TmpCatalogo::where('ean', $eanTemporal)->first();
            
            if (!$tmpProducto) {
                return false;
            }
            
            // 2. Buscar el producto real en catalogo_general
            $productoReal = CatalogoGeneral::where('ean', $eanReal)->first();

            if (!$productoReal) {
                // Solo crear si NO existe
                $productoReal = CatalogoGeneral::create([
                    'ean' => $eanReal,
                    'descripcion' => $tmpProducto->descripcion,
                    'precio' => $tmpProducto->precio,
                    'id_sucursal' => $idSucursal ?? 1,
                    'inventario' => 0,  // Stock inicial 0, luego se reducirá al marcar como listo
                    'num_familia' => 'EXT'
                ]);
            } else {
                // Si ya existe, actualizar datos por si cambiaron
                $productoReal->descripcion = $tmpProducto->descripcion;
                $productoReal->precio = $tmpProducto->precio;
                $productoReal->save();
            }
            
            // 3. PRIMERO: Actualizar TODOS los orden_pedido_detalle con EAN temporal
            // Actualizamos coincidencia exacta
            $actualizadosPedidos = OrdenPedidoDetalle::where('ean', $eanTemporal)
            ->update(['ean' => $eanReal]);
            
            // 4. SEGUNDO: Actualizar TODOS los crm_cotizaciones_detalle con codbar temporal
            $actualizadosCotizaciones = DB::connection('sqlsrv')->table('crm_cotizaciones_detalle')
                ->where('codbar', $eanTemporal)
                ->orWhere('codbar', 'LIKE', '%' . $eanTemporal . '%')
                ->update(['codbar' => $eanReal]);
            
            
            // 5. TERCERO: Actualizar y marcar temporal como inactivo
            $tmpProducto->ean = $eanReal; // Actualizamos el EAn
            $tmpProducto->activo = 0; // Marcamos como inactivo
            $tmpProducto->save();
            
            return true;
            
        } catch (\Exception $e) {
            \Log::error("Error al convertir producto temporal: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener el ID de la sucursal para un pedido (para marcar listo)
     */
    public function obtenerSucursalIdPedido(int $pedidoId): JsonResponse
    {
        try {
            $sucursalAsignada = auth()->user()->sucursal_asignada ?? 0;
            
            if ($sucursalAsignada == 0) {
                return response()->json(['success' => false, 'message' => 'Usuario sin sucursal asignada'], 403);
            }
            
            $sucursalPedido = OrdenPedidoSucursal::where('id_pedido', $pedidoId)
                ->where('id_sucursal', $sucursalAsignada)
                ->first();
            
            if (!$sucursalPedido) {
                return response()->json(['success' => false, 'message' => 'Sucursal no encontrada en este pedido'], 404);
            }
            
            return response()->json([
                'success' => true,
                'sucursal_id' => $sucursalPedido->id_pedido_sucursal
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en obtenerSucursalIdPedido: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reprogramar un producto que no llegó
     */
    public function reprogramarProducto(Request $request): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'pedidos_anticipo', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $validated = $request->validate([
                'pedido_id' => 'required|integer|exists:orden_pedido,id_pedido',
                'detalle_id' => 'required|integer|exists:orden_pedido_detalle,id_detalle_pedido',
                'motivo' => 'required|string|max:500',
                'sucursal_id' => 'required|integer|exists:sqlsrvM.sucursales,id_sucursal',
                'producto_data' => 'required|array',
                'producto_data.ean' => 'required|string',
                'producto_data.nombre' => 'required|string',
                'producto_data.cantidad' => 'required|integer|min:1',
                'producto_data.precio_unitario' => 'required|numeric|min:0',
                'producto_data.descuento' => 'nullable|numeric',
                'producto_data.importe' => 'required|numeric',
                'producto_data.es_externo' => 'nullable|boolean',
                'producto_data.id_cotizacion_detalle' => 'nullable|integer'
            ]);
            
            $pedidoOriginal = OrdenPedido::findOrFail($validated['pedido_id']);
            $detalleOriginal = OrdenPedidoDetalle::findOrFail($validated['detalle_id']);
            
            // 1. Marcar el detalle original como eliminado (se_elimino = 1)
            $detalleOriginal->se_elimino = 1;
            $detalleOriginal->save();
            
            // 2. Guardar en tabla de reprogramación
            DB::connection('sqlsrv')->table('orden_pedido_reprogramado')->insert([
                'id_pedido_detalle' => $validated['detalle_id'],
                'id_sucursal' => $validated['sucursal_id'],
                'motivo' => $validated['motivo'],
                'created_at' => now(),
                'created_by' => auth()->id()
            ]);
            
            // 3. Crear nuevo pedido con solo este producto
            $folioNuevoPedido = $this->generarFolioPedido();
            
            $nuevoPedido = OrdenPedido::create([
                'id_cotizacion' => $pedidoOriginal->id_cotizacion,
                'folio_pedido' => $folioNuevoPedido,
                'status' => 2, // En proceso
                'fecha_pedido' => now(),
                'creado_por' => auth()->id(),
                'activo' => 1
            ]);
            
            // 4. Crear detalle del nuevo pedido
            $productoData = $validated['producto_data'];
            OrdenPedidoDetalle::create([
                'id_pedido' => $nuevoPedido->id_pedido,
                'id_cotizacion_detalle' => $productoData['id_cotizacion_detalle'] ?? null,
                'ean' => $productoData['ean'],
                'cantidad' => $productoData['cantidad'],
                'precio_unitario' => $productoData['precio_unitario'],
                'descuento' => $productoData['descuento'] ?? 0,
                'importe' => $productoData['importe'],
                'es_externo' => $productoData['es_externo'] ?? 0,
                'se_elimino' => 0,
                'id_sucursal_surtido' => $validated['sucursal_id']
            ]);
            
            // 5. Asignar sucursal al nuevo pedido
            OrdenPedidoSucursal::create([
                'id_pedido' => $nuevoPedido->id_pedido,
                'id_sucursal' => $validated['sucursal_id'],
                'status' => 0,
                'fecha_asignacion' => now()
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Producto reprogramado correctamente. Nuevo pedido generado: ' . $folioNuevoPedido,
                'nuevo_pedido_id' => $nuevoPedido->id_pedido,
                'nuevo_folio' => $folioNuevoPedido
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al reprogramar producto: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al reprogramar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reprogramarMulti(Request $request): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'pedidos_anticipo', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $validated = $request->validate([
                'pedido_id' => 'required|integer|exists:orden_pedido,id_pedido',
                'motivo' => 'required|string|max:500',
                'sucursal_id' => 'required|integer|exists:sqlsrvM.sucursales,id_sucursal',
                'productos' => 'required|array|min:1',
                'productos.*.detalle_id' => 'required|integer|exists:orden_pedido_detalle,id_detalle_pedido',
                'productos.*.producto_data' => 'required|array',
                'productos.*.producto_data.ean' => 'required|string',
                'productos.*.producto_data.nombre' => 'required|string',
                'productos.*.producto_data.cantidad' => 'required|integer|min:1',
                'productos.*.producto_data.precio_unitario' => 'required|numeric|min:0',
                'productos.*.producto_data.descuento' => 'nullable|numeric',
                'productos.*.producto_data.importe' => 'required|numeric',
                'productos.*.producto_data.es_externo' => 'nullable|boolean',
                'productos.*.producto_data.id_cotizacion_detalle' => 'nullable|integer'
            ]);
            
            $pedidoOriginal = OrdenPedido::findOrFail($validated['pedido_id']);
            $pedidosCreados = [];
            $detallesIdsReprogramados = [];
            
            foreach ($validated['productos'] as $productoItem) {
                $detalleOriginal = OrdenPedidoDetalle::findOrFail($productoItem['detalle_id']);
                $detallesIdsReprogramados[] = $productoItem['detalle_id'];
                
                // 1. Marcar el detalle original como eliminado
                $detalleOriginal->se_elimino = 1;
                $detalleOriginal->save();
                
                // 2. Guardar en tabla de reprogramación
                DB::connection('sqlsrv')->table('orden_pedido_reprogramado')->insert([
                    'id_pedido_detalle' => $productoItem['detalle_id'],
                    'id_sucursal' => $validated['sucursal_id'],
                    'motivo' => $validated['motivo'],
                    'created_at' => now(),
                    'created_by' => auth()->id()
                ]);
                
                // 3. Crear nuevo pedido
                $folioNuevoPedido = $this->generarFolioPedido();
                
                $nuevoPedido = OrdenPedido::create([
                    'id_cotizacion' => $pedidoOriginal->id_cotizacion,
                    'folio_pedido' => $folioNuevoPedido,
                    'status' => 2,
                    'fecha_pedido' => now(),
                    'creado_por' => auth()->id(),
                    'activo' => 1
                ]);
                
                // 4. Crear detalle del nuevo pedido
                $productoData = $productoItem['producto_data'];
                OrdenPedidoDetalle::create([
                    'id_pedido' => $nuevoPedido->id_pedido,
                    'id_cotizacion_detalle' => $productoData['id_cotizacion_detalle'] ?? null,
                    'ean' => $productoData['ean'],
                    'cantidad' => $productoData['cantidad'],
                    'precio_unitario' => $productoData['precio_unitario'],
                    'descuento' => $productoData['descuento'] ?? 0,
                    'importe' => $productoData['importe'],
                    'es_externo' => $productoData['es_externo'] ?? 0,
                    'se_elimino' => 0,
                    'id_sucursal_surtido' => $validated['sucursal_id']
                ]);
                
                // 5. Asignar sucursal al nuevo pedido
                OrdenPedidoSucursal::create([
                    'id_pedido' => $nuevoPedido->id_pedido,
                    'id_sucursal' => $validated['sucursal_id'],
                    'status' => 0,
                    'fecha_asignacion' => now()
                ]);
                
                $pedidosCreados[] = $folioNuevoPedido;
            }
            
            // Verificar si el pedido original quedó sin productos activos
            $productosRestantes = OrdenPedidoDetalle::where('id_pedido', $pedidoOriginal->id_pedido)
                ->where('se_elimino', 0)
                ->count();
            
            if ($productosRestantes === 0) {
                // El pedido original quedó vacío, lo cancelamos (status 4 = Cancelado)
                $pedidoOriginal->status = 4;
                $pedidoOriginal->save();
                
                // Opcional: Registrar en comentarios por qué se canceló
                $comentarioActual = $pedidoOriginal->comentarios ?? '';
                $nuevoComentario = "[{$comentarioActual}]\n[AUTOMÁTICO] Pedido cancelado porque todos sus productos fueron reprogramados.";
                $pedidoOriginal->comentarios = $nuevoComentario;
                $pedidoOriginal->save();
            }
            
            DB::commit();
            
            $mensaje = count($pedidosCreados) . ' producto(s) reprogramado(s) correctamente. Nuevos pedidos: ' . implode(', ', $pedidosCreados);
            if ($productosRestantes === 0) {
                $mensaje .= ' El pedido original fue cancelado por quedar sin productos.';
            }
            
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'nuevos_pedidos' => $pedidosCreados,
                'pedido_original_cancelado' => $productosRestantes === 0
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al reprogramar múltiples productos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al reprogramar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar folio único para pedido
     */
    private function generarFolioPedido()
    {
        $fecha = now();
        $prefijo = 'OP-' . $fecha->format('Ymd') . '-';
        
        $ultimoPedido = OrdenPedido::where('folio_pedido', 'LIKE', $prefijo . '%')
            ->orderBy('folio_pedido', 'desc')
            ->first();
        
        if ($ultimoPedido) {
            $ultimoNumero = (int) substr($ultimoPedido->folio_pedido, -4);
            $nuevoNumero = $ultimoNumero + 1;
        } else {
            $nuevoNumero = 1;
        }
        
        return $prefijo . str_pad($nuevoNumero, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Refrescar la tabla de pedidos vía AJAX (para polling)
     */
    public function refrescarTabla(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $puedeVer = $user->puede('ventas', 'pedidos_anticipo', 'ver');
            
            if (!$puedeVer) {
                return response()->json(['success' => false, 'message' => 'Sin permiso'], 403);
            }
            
            // OBTENER PERFILES DEL USUARIO
            $esCRM = $user->es_crm;
            $esSucursal = $user->es_sucursal;
            $esRepartidor = $user->es_repartidor;
            $sucursalAsignada = $user->sucursal_asignada_efectiva;
            $usuarioId = $user->id_personal_empresa;
            
            $permisos = [
                'ver' => $puedeVer,
                'crear' => $user->puede('ventas', 'pedidos_anticipo', 'crear'),
                'editar' => $user->puede('ventas', 'pedidos_anticipo', 'editar'),
                'eliminar' => $user->puede('ventas', 'pedidos_anticipo', 'eliminar'),
            ];
            
            // Obtener filtros del request
            $statusFilter = $request->input('status_filter', 'todos');
            $searchTerm = $request->input('search_term', '');
            $ultimoId = $request->input('ultimo_id', 0);
            
            // Construir query base
            $query = OrdenPedido::with([
                'cotizacion.cliente', 
                'cotizacion.sucursalAsignada', 
                'sucursales.sucursal',
                'repartidor',
                'detalles' => function($q) {
                    $q->where('se_elimino', 0);
                }
            ])->where('activo', 1);
            
            // Excluir cancelados siempre (status = 1 y status = 4)
            $query->whereNotIn('status', [1, 4]);
            
            // FILTRO DE VISIBILIDAD SEGÚN COMBINACIÓN DE PERFILES
            if ($esCRM) {
                // CRM: ve todos los pedidos (sin filtro)
                // No se aplica ningún filtro (incluso si tiene Sucursal o Repartidor, CRM domina)
            } elseif ($esSucursal && $esRepartidor && !$esCRM) {
                // Sucursal + Repartidor (sin CRM): ve pedidos de su sucursal O los asignados a él
                $query->where(function($q) use ($sucursalAsignada, $usuarioId) {
                    $q->whereHas('detalles', function($sub) use ($sucursalAsignada) {
                        $sub->where('id_sucursal_surtido', $sucursalAsignada)
                            ->where('se_elimino', 0);
                    })->orWhere('id_repartidor', $usuarioId);
                });
            } elseif ($esRepartidor) {
                // Solo Repartidor: solo pedidos asignados a él (independientemente del horario)
                $query->where('id_repartidor', $usuarioId);
            } elseif ($esSucursal && $sucursalAsignada > 0) {
                // Solo Sucursal: solo pedidos que tengan productos de su sucursal
                $query->whereHas('detalles', function($q) use ($sucursalAsignada) {
                    $q->where('id_sucursal_surtido', $sucursalAsignada)
                    ->where('se_elimino', 0);
                });
            } else {
                // Sin perfil específico: usar lógica anterior (compatibilidad)
                if ($sucursalAsignada > 0) {
                    $query->whereHas('detalles', function($q) use ($sucursalAsignada) {
                        $q->where('id_sucursal_surtido', $sucursalAsignada)
                        ->where('se_elimino', 0);
                    });
                }
            }
            
            // Búsqueda por término
            if (!empty($searchTerm)) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('folio_pedido', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('cotizacion', function($sub) use ($searchTerm) {
                        $sub->where('folio', 'LIKE', "%{$searchTerm}%");
                    })
                    ->orWhereHas('cotizacion', function($sub) use ($searchTerm) {
                        $sub->whereExists(function($exists) use ($searchTerm) {
                            $exists->from('fp_central_matriz.dbo.catalogo_cliente_maestro')
                                ->whereColumn('crm_cotizaciones.id_cliente', '=', 'fp_central_matriz.dbo.catalogo_cliente_maestro.id_Cliente')
                                ->where(function($where) use ($searchTerm) {
                                    $where->where('Nombre', 'LIKE', "%{$searchTerm}%")
                                        ->orWhere('apPaterno', 'LIKE', "%{$searchTerm}%")
                                        ->orWhere('apMaterno', 'LIKE', "%{$searchTerm}%")
                                        ->orWhereRaw("CONCAT(Nombre, ' ', apPaterno, ' ', COALESCE(apMaterno, '')) LIKE ?", ["%{$searchTerm}%"]);
                                });
                        });
                    });
                });
            }
            
            // Aplicar filtro de status
            if ($statusFilter === 'proceso') {
                $query->where('status', 2);
            } elseif ($statusFilter === 'finalizados') {
                $query->where('status', 3);
            }
            
            // Verificar si hay nuevos registros
            $nuevoIdMaximo = $query->max('id_pedido') ?? 0;
            
            // Ordenar y paginar
            $pedidos = $query->orderByRaw("
                CASE 
                    WHEN status = 2 THEN 1  -- En proceso (prioridad 1)
                    WHEN status = 3 THEN 2  -- Finalizado (prioridad 2)
                    WHEN status = 1 THEN 3  -- Cancelado (prioridad 3)
                    ELSE 4
                END, id_pedido DESC
            ")->paginate(15);
            
            $html = view('ventas.pedidos.partials.tabla-pedidos', compact(
                'pedidos', 'sucursalAsignada', 'esRepartidor', 'esCRM', 'esSucursal', 'permisos'
            ))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'ultimo_id' => $nuevoIdMaximo,
                'hay_nuevos' => $nuevoIdMaximo > $ultimoId
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en refrescarTabla pedidos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refrescar los datos de asignación de repartidores vía AJAX (para polling)
     * Reutiliza los métodos existentes: repartidoresConStatus, pedidosPendientesRepartidor, pedidosPendientesCRM
     */
    public function refrescarAsignacion(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $esRepartidor = $user->es_repartidor;
            $esCRM = $user->es_crm;
            $esSucursal = $user->es_sucursal;
            $sucursalAsignada = $user->sucursal_asignada_efectiva;
            $usuarioId = $user->id_personal_empresa;

            // Verificar permisos básicos
            $puedeVer = $user->puede('ventas', 'pedidos_anticipo', 'ver');
            if (!$puedeVer) {
                return response()->json(['success' => false, 'message' => 'Sin permiso'], 403);
            }

            // Obtener últimos IDs conocidos desde el request
            $ultimoIdRepartidor = (int) $request->input('ultimo_id_repartidor', 0);
            $ultimoIdEntrega = (int) $request->input('ultimo_id_entrega', 0);
            $ultimoIdPedido = (int) $request->input('ultimo_id_pedido', 0);

            // 1. OBTENER REPARTIDORES Y ENTREGAS EN CURSO
            // Solo si tiene permisos para ver repartidores (CRM o Sucursal)
            $repartidores = [];
            $entregasCurso = [];
            $maxIdRepartidor = 0;
            $maxIdEntrega = 0;
            $repartidoresCambiaron = false;
            $entregasCambiaron = false;

            if ($esCRM || $esSucursal) {
                $repartidoresResponse = $this->repartidoresConStatus(0);
                $repartidoresData = $repartidoresResponse->getData();

                if ($repartidoresData->success ?? false) {
                    // Filtrar repartidores según perfil
                    if ($esSucursal && !$esCRM) {
                        // Solo Sucursal: ver solo repartidores de su sucursal
                        $repartidores = array_filter($repartidoresData->repartidores ?? [], function($rep) use ($sucursalAsignada) {
                            return $rep->sucursal == $sucursalAsignada;
                        });
                        // Filtrar entregas de su sucursal
                        $entregasCurso = array_filter($repartidoresData->entregas_curso ?? [], function($ent) use ($sucursalAsignada) {
                            return $ent->sucursal == $sucursalAsignada;
                        });
                    } else {
                        // CRM: ve todos
                        $repartidores = $repartidoresData->repartidores ?? [];
                        $entregasCurso = $repartidoresData->entregas_curso ?? [];
                    }

                    // DETECCIÓN DE CAMBIOS EN REPARTIDORES
                    if (!empty($repartidores)) {
                        $repartidoresArray = is_array($repartidores) ? $repartidores : (array) $repartidores;
                        $ids = array_column($repartidoresArray, 'id');
                        $maxIdRepartidor = !empty($ids) ? max($ids) : 0;
                        
                        if ($maxIdRepartidor > $ultimoIdRepartidor) {
                            $repartidoresCambiaron = true;
                        }
                        
                        if (!$repartidoresCambiaron && $ultimoIdRepartidor > 0) {
                            $cantidadActual = count($repartidoresArray);
                            $cantidadAnterior = $request->input('cantidad_repartidores', 0);
                            if ($cantidadActual != $cantidadAnterior) {
                                $repartidoresCambiaron = true;
                            }
                        }
                    }

                    // DETECCIÓN DE CAMBIOS EN ENTREGAS EN CURSO
                    if (!empty($entregasCurso)) {
                        $entregasArray = is_array($entregasCurso) ? $entregasCurso : (array) $entregasCurso;
                        $ids = array_column($entregasArray, 'id');
                        $maxIdEntrega = !empty($ids) ? max($ids) : 0;
                        
                        if ($maxIdEntrega > $ultimoIdEntrega) {
                            $entregasCambiaron = true;
                        }
                    }

                    if ($ultimoIdEntrega > 0 && empty($entregasCurso)) {
                        $entregasCambiaron = true;
                    }

                    if ($ultimoIdEntrega > 0 && $maxIdEntrega < $ultimoIdEntrega) {
                        $entregasCambiaron = true;
                    }

                    if (!$entregasCambiaron) {
                        $cantidadActual = !empty($entregasCurso) ? count((array)$entregasCurso) : 0;
                        $cantidadAnterior = $request->input('cantidad_entregas', 0);
                        if ($cantidadActual != $cantidadAnterior) {
                            $entregasCambiaron = true;
                        }
                    }
                }
            }

            // 2. OBTENER PEDIDOS PENDIENTES (SEGÚN PERFIL)
            $pedidosPendientes = null;
            $pedidosCRM = null;
            $maxIdPedido = 0;
            $pedidosCambiaron = false;

            if ($esRepartidor) {
                // Repartidor: usar método pedidosPendientesRepartidor()
                $pedidosResponse = $this->pedidosPendientesRepartidor();
                $pedidosData = $pedidosResponse->getData();

                if ($pedidosData->success ?? false) {
                    $pedidosPendientes = $pedidosData->pedidos ?? [];

                    if (!empty($pedidosPendientes)) {
                        $pedidosArray = is_array($pedidosPendientes) ? $pedidosPendientes : (array) $pedidosPendientes;
                        $ids = array_column($pedidosArray, 'id_pedido');
                        $maxIdPedido = !empty($ids) ? max($ids) : 0;
                        if ($maxIdPedido > $ultimoIdPedido) {
                            $pedidosCambiaron = true;
                        }
                    }
                }

            } elseif ($esCRM) {
                // CRM: usar método pedidosPendientesCRM()
                $pedidosResponse = $this->pedidosPendientesCRM();
                $pedidosData = $pedidosResponse->getData();

                if ($pedidosData->success ?? false) {
                    $pedidosCRM = $pedidosData->pedidos ?? [];

                    if (!empty($pedidosCRM)) {
                        $pedidosArray = is_array($pedidosCRM) ? $pedidosCRM : (array) $pedidosCRM;
                        $ids = array_column($pedidosArray, 'id_pedido');
                        $maxIdPedido = !empty($ids) ? max($ids) : 0;
                        if ($maxIdPedido > $ultimoIdPedido) {
                            $pedidosCambiaron = true;
                        }
                    }
                }
            }

            // Determinar si hubo cambios en general
            $hayCambios = $repartidoresCambiaron || $entregasCambiaron || $pedidosCambiaron;

            // 3. CONSTRUIR RESPUESTA
            $response = [
                'success' => true,
                'hay_cambios' => $hayCambios,
                'ultimo_id_repartidor' => $maxIdRepartidor,
                'ultimo_id_entrega' => $maxIdEntrega,
                'ultimo_id_pedido' => $maxIdPedido,
                'cantidad_repartidores' => !empty($repartidores) ? count((array)$repartidores) : 0,
                'cantidad_entregas' => !empty($entregasCurso) ? count((array)$entregasCurso) : 0,
            ];

            // Solo incluir los datos si hubo cambios
            if ($hayCambios) {
                if ($repartidoresCambiaron) {
                    $response['repartidores'] = $repartidores;
                }
                if ($entregasCambiaron) {
                    $response['entregas_curso'] = $entregasCurso;
                }
                if ($pedidosCambiaron) {
                    if ($esRepartidor) {
                        $response['pedidos_pendientes'] = $pedidosPendientes;
                    } elseif ($esCRM) {
                        $response['pedidos_crm'] = $pedidosCRM;
                    }
                }
            }

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Error en refrescarAsignacion: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'success' => false,
                'message' => 'Error al refrescar datos: ' . $e->getMessage()
            ], 500);
        }
    }
}