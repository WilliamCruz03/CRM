<?php
// app/Http/Controllers/Ventas/CotizacionController.php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Cotizaciones\Cotizacion;
use App\Models\Cotizaciones\CotizacionDetalle;
use App\Models\Cotizaciones\CatFase;
use App\Models\Cotizaciones\CatClasificacion;
use App\Models\Cotizaciones\CatConvenio;
use App\Models\Cotizaciones\CatConvenioDetalle;
use App\Models\Cotizaciones\CatFamilia;
use App\Models\Cliente;
use App\Models\Sucursal;
use App\Models\CatalogoGeneral;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CotizacionController extends Controller
{
    public function index(): View
    {
        $puedeVer = auth()->user()->puede('ventas', 'cotizaciones', 'ver');
        $puedeCrear = auth()->user()->puede('ventas', 'cotizaciones', 'crear');
        
        if (!$puedeVer && !$puedeCrear) {
            abort(403, 'No tienes permiso para acceder a este módulo');
        }
        
        $cotizaciones = [];
        if ($puedeVer) {
            $cotizaciones = Cotizacion::with(['cliente', 'fase', 'clasificacion', 'sucursalAsignada'])
                ->activas()
                ->orderBy('id_cotizacion', 'desc')
                ->get();
        }
        
        $permisos = [
            'ver' => $puedeVer,
            'crear' => $puedeCrear,
            'editar' => auth()->user()->puede('ventas', 'cotizaciones', 'editar'),
            'eliminar' => auth()->user()->puede('ventas', 'cotizaciones', 'eliminar'),
        ];
        
        return view('ventas.cotizaciones.index', compact('cotizaciones', 'permisos'));
    }
    
    public function buscarClientes(Request $request): JsonResponse
    {
        $termino = $request->input('q', '');
        
        $clientes = Cliente::whereIn('status', ['CLIENTE', 'PROSPECTO'])
            ->where(function($query) use ($termino) {
                $query->where('Nombre', 'LIKE', "%{$termino}%")
                    ->orWhere('apPaterno', 'LIKE', "%{$termino}%")
                    ->orWhere('apMaterno', 'LIKE', "%{$termino}%")
                    ->orWhere('email1', 'LIKE', "%{$termino}%");
            })
            ->limit(10)
            ->get(['id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'email1', 'titulo']);
        
        return response()->json([
            'success' => true,
            'data' => $clientes->map(function($cliente) {
                return [
                    'id' => $cliente->id_Cliente,
                    'nombre' => $cliente->nombre_completo,
                    'email' => $cliente->email1
                ];
            })
        ]);
    }
    
public function buscarProductos(Request $request): JsonResponse
    {
        $termino = $request->input('q', '');
        $sucursalAsignadaId = $request->input('sucursal_asignada_id', null);
        $cotizacionId = $request->input('cotizacion_id', null);
        
        if ($cotizacionId && is_numeric($cotizacionId)) {
            $cotizacionId = (int) $cotizacionId;
        }

        // Obtener productos apartados SOLO de cotizaciones con certeza = Alta (3)
        $productosApartadosQuery = DB::table('crm_cotizaciones_detalle as cd')
            ->join('crm_cotizaciones as c', 'cd.id_cotizacion', '=', 'c.id_cotizacion')
            ->where('cd.apartado', 1)
            ->where('c.activo', 1)
            ->where('c.certeza', 3); // Solo certeza Alta

        if ($cotizacionId && $cotizacionId > 0) {
            $productosApartadosQuery->where('c.id_cotizacion', '!=', $cotizacionId);
        }

        $productosApartados = $productosApartadosQuery
            ->select('cd.id_producto', 'cd.cantidad', 'cd.id_sucursal_surtido')
            ->get();

        // Agrupar apartados
        $apartados = [];
        foreach ($productosApartados as $apartado) {
            $key = $apartado->id_producto . '_' . $apartado->id_sucursal_surtido;
            $apartados[$key] = ($apartados[$key] ?? 0) + $apartado->cantidad;
        }

        // Buscar productos
        $productos = CatalogoGeneral::with('sucursal')
            ->where('activo', 1)
            ->where('inventario', '>', 0)
            ->where(function($query) use ($termino) {
                $query->where('descripcion', 'LIKE', "%{$termino}%")
                    ->orWhere('ean', 'LIKE', "%{$termino}%");
            })
            ->get(['id_catalogo_general', 'id_sucursal', 'ean', 'descripcion', 'precio', 'inventario', 'num_familia']);

        // Calcular stock disponible
        $productosConStock = $productos->map(function($producto) use ($apartados) {
            $key = $producto->id_catalogo_general . '_' . $producto->id_sucursal;
            $stockApartado = $apartados[$key] ?? 0;
            $stockDisponible = $producto->inventario - $stockApartado;

            return [
                'id' => $producto->id_catalogo_general,
                'id_sucursal' => $producto->id_sucursal,
                'nombre_sucursal' => $producto->sucursal->nombre ?? 'N/A',
                'codbar' => $producto->ean,
                'nombre' => $producto->descripcion,
                'precio' => floatval($producto->precio),
                'inventario' => max(0, $stockDisponible),
                'inventario_original' => $producto->inventario,
                'apartado' => $stockApartado,
                'num_familia' => $producto->num_familia
            ];
        })->filter(function($producto) {
            return $producto['inventario'] > 0;
        })->values();

        // Ordenar
        $productosOrdenados = $productosConStock->sortByDesc(function($producto) use ($sucursalAsignadaId) {
            return $producto['id_sucursal'] == $sucursalAsignadaId ? 1 : 0;
        })->values();

        return response()->json([
            'success' => true,
            'data' => $productosOrdenados
        ]);
    }
    
    public function catalogos(): JsonResponse
    {
        try {
            $fases = CatFase::where('activo', 1)->get(['id_fase', 'fase']);
            $clasificaciones = CatClasificacion::where('activo', 1)->get(['id_clasificacion', 'clasificacion']);
            $sucursales = Sucursal::where('activo', 1)->get(['id_sucursal', 'nombre']);
            
            // Obtener convenios con sus familias y descuentos (sin porcentaje global)
            $convenios = CatConvenio::with(['familias' => function($q) {
                $q->select('cat_familias.id_familia', 'cat_familias.num_familia', 'cat_convenios_familias.porcentaje_descuento');
            }])
            ->where('activo', 1)
            ->where('tipo', 'C')
            ->get(['id_convenio', 'nombre']);
            
            $conveniosFormateados = $convenios->map(function($convenio) {
                return [
                    'id' => $convenio->id_convenio,
                    'nombre' => $convenio->nombre,
                    'familias' => $convenio->familias->map(function($familia) {
                        return [
                            'num_familia' => $familia->num_familia,
                            'descuento' => $familia->pivot->porcentaje_descuento
                        ];
                    })
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'fases' => $fases,
                    'clasificaciones' => $clasificaciones,
                    'sucursales' => $sucursales,
                    'convenios' => $conveniosFormateados
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en catalogos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar catálogos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function store(Request $request): JsonResponse
    {
        \Log::info('=== GUARDAR COTIZACIÓN ===');
        \Log::info('Datos recibidos:', $request->all());
        
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'crear')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        try {
            $validated = $request->validate([
                'id_cliente' => 'required|exists:catalogo_cliente_maestro,id_Cliente',
                'id_fase' => 'required|exists:cat_fases,id_fase',
                'id_clasificacion' => 'nullable|exists:cat_clasificaciones,id_clasificacion',
                'id_sucursal_asignada' => 'nullable|exists:sucursales,id_sucursal',
                'certeza' => 'nullable|integer|in:1,2,3', // 1=Baja,2=Media,3=Alta
                'comentarios' => 'nullable|string|max:500',
                'articulos' => 'required|array|min:1',
                'articulos.*.id_producto' => 'required|exists:catalogo_general,id_catalogo_general',
                'articulos.*.cantidad' => 'required|integer|min:1',
                'articulos.*.precio_unitario' => 'required|numeric|min:0',
                'articulos.*.descuento' => 'nullable|numeric|min:0|max:100',
                'articulos.*.id_convenio' => 'nullable|exists:cat_convenios,id_convenio',
                'articulos.*.id_sucursal_surtido' => 'nullable|exists:sucursales,id_sucursal'
            ]);

            DB::beginTransaction();

            $importeTotal = 0;
            $articulosData = [];
            $stockDisponible = true; // Se asume que todos los productos tienen stock en sucursal asignada
            $sucursalAsignadaId = $validated['id_sucursal_asignada'];

            foreach ($validated['articulos'] as $articulo) {
                $producto = CatalogoGeneral::find($articulo['id_producto']);
                if (!$producto) {
                    throw new \Exception('Producto no encontrado: ' . $articulo['id_producto']);
                }

                $descuento = $articulo['descuento'] ?? 0;
                $importe = $articulo['cantidad'] * $articulo['precio_unitario'] * (1 - $descuento / 100);
                $importeTotal += $importe;

                // Verificar stock en sucursal asignada (si existe)
                if ($sucursalAsignadaId && $articulo['id_sucursal_surtido'] == $sucursalAsignadaId) {
                    $stockProducto = $producto->inventario;
                    if ($stockProducto < $articulo['cantidad']) {
                        $stockDisponible = false;
                    }
                }

                $articulosData[] = [
                    'id_producto' => $articulo['id_producto'],
                    'codbar' => $producto->ean,
                    'descripcion' => $producto->descripcion,
                    'cantidad' => $articulo['cantidad'],
                    'precio_unitario' => $articulo['precio_unitario'],
                    'descuento' => $descuento,
                    'importe' => $importe,
                    'id_convenio' => $articulo['id_convenio'] ?? null,
                    'id_sucursal_surtido' => $articulo['id_sucursal_surtido'] ?? null,
                ];
            }

            // Mapear certeza a entero (ya viene como 1,2,3 desde el frontend)
            $certeza = $validated['certeza'] ?? 0;
            $apartado = ($certeza == 3) ? 1 : 0; // Solo aparta si certeza Alta

            // Calcular fecha de entrega sugerida
            $fechaCreacion = now();
            $fechaEntrega = Cotizacion::calcularFechaEntregaSugerida($fechaCreacion, $stockDisponible);

            $cotizacion = Cotizacion::create([
                'id_cliente' => $validated['id_cliente'],
                'id_fase' => $validated['id_fase'],
                'id_clasificacion' => $validated['id_clasificacion'] ?? null,
                'id_sucursal_asignada' => $validated['id_sucursal_asignada'] ?? null,
                'certeza' => $certeza,
                'importe_total' => $importeTotal,
                'comentarios' => $validated['comentarios'],
                'fecha_entrega_sugerida' => $fechaEntrega,
                'activo' => 1,
                'enviado' => 0,
                'version' => 1,
            ]);

            foreach ($articulosData as $detalle) {
                CotizacionDetalle::create(array_merge($detalle, [
                    'id_cotizacion' => $cotizacion->id_cotizacion,
                    'apartado' => $apartado
                ]));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cotización creada correctamente',
                'data' => $cotizacion->load('detalles', 'cliente', 'fase')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear cotización: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la cotización: ' . $e->getMessage()
            ], 500);
        }
    }
    
public function show(int $id): JsonResponse
{
    if (!auth()->user()->puede('ventas', 'cotizaciones', 'ver')) {
        return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
    }
    
    $cotizacion = Cotizacion::with([
        'cliente', 'fase', 'clasificacion', 'sucursalAsignada',
        'detalles.producto', 'detalles.convenio', 'detalles.sucursalSurtido'
    ])->findOrFail($id);
    
    return response()->json([
        'success' => true,
        'data' => $cotizacion
    ]);
}
    
    public function update(Request $request, int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        $cotizacion = Cotizacion::findOrFail($id);
        $esEnviada = $cotizacion->enviado;

        // Validar si se está editando la misma cotización o creando nueva versión
        $opcion = $request->input('opcion', 'editar'); // 'editar' o 'nueva_version'

        if ($opcion === 'nueva_version') {
            return $this->crearVersion($request, $id);
        }

        // Si es enviada, no se puede editar la misma
        if ($esEnviada) {
            return response()->json([
                'success' => false,
                'message' => 'Esta cotización ya fue enviada. Solo puede crear una nueva versión.'
            ], 403);
        }

        $validated = $request->validate([
            'id_fase' => 'required|exists:cat_fases,id_fase',
            'id_clasificacion' => 'nullable|exists:cat_clasificaciones,id_clasificacion',
            'id_sucursal_asignada' => 'nullable|exists:sucursales,id_sucursal',
            'certeza' => 'nullable|integer|in:1,2,3',
            'comentarios' => 'nullable|string|max:500',
            'articulos' => 'required|array|min:1',
            'articulos.*.id_producto' => 'required|exists:catalogo_general,id_catalogo_general',
            'articulos.*.cantidad' => 'required|integer|min:1',
            'articulos.*.precio_unitario' => 'required|numeric|min:0',
            'articulos.*.descuento' => 'nullable|numeric|min:0|max:100',
            'articulos.*.id_convenio' => 'nullable|exists:cat_convenios,id_convenio',
            'articulos.*.id_sucursal_surtido' => 'nullable|exists:sucursales,id_sucursal'
        ]);

        // Verificar similitud antes de actualizar
        $similitud = $this->calcularSimilitud($cotizacion, $validated['articulos']);
        if ($similitud < 50) {
            return response()->json([
                'success' => false,
                'message' => 'Los productos han cambiado significativamente. ¿Deseas crear una nueva versión?',
                'similitud' => $similitud,
                'requiere_confirmacion' => true
            ], 409); // Conflict
        }

        // Si la similitud es suficiente, continuar con la actualización normal
        return $this->actualizarCotizacion($cotizacion, $validated);
    }

    /**
     * Crear una nueva versión a partir de una existente
     */
    public function crearVersion(Request $request, int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'crear')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        try {
            DB::beginTransaction();

            $cotizacionOriginal = Cotizacion::with('detalles')->findOrFail($id);

            // Desactivar la cotización original
            $cotizacionOriginal->activo = 0;
            $cotizacionOriginal->save();

            // Preparar datos para la nueva cotización
            $nuevaCotizacionData = $cotizacionOriginal->toArray();
            unset($nuevaCotizacionData['id_cotizacion']);
            $nuevaCotizacionData['folio'] = Cotizacion::generarFolio();
            $nuevaCotizacionData['fecha_creacion'] = now();
            $nuevaCotizacionData['fecha_ultima_modificacion'] = now();
            $nuevaCotizacionData['creado_por'] = auth()->id();
            $nuevaCotizacionData['modificado_por'] = null;
            $nuevaCotizacionData['activo'] = 1;
            $nuevaCotizacionData['enviado'] = 0;
            $nuevaCotizacionData['fecha_envio'] = null;
            $nuevaCotizacionData['cotizacion_origen_id'] = $cotizacionOriginal->id_cotizacion;
            $nuevaCotizacionData['version'] = $cotizacionOriginal->version + 1;

            // Recalcular fecha de entrega según los datos de la nueva cotización (mismos productos)
            $stockDisponible = $this->verificarStockSucursal($cotizacionOriginal->detalles, $cotizacionOriginal->id_sucursal_asignada);
            $nuevaCotizacionData['fecha_entrega_sugerida'] = Cotizacion::calcularFechaEntregaSugerida(now(), $stockDisponible);

            $nuevaCotizacion = Cotizacion::create($nuevaCotizacionData);

            // Copiar detalles
            foreach ($cotizacionOriginal->detalles as $detalle) {
                $nuevoDetalle = $detalle->toArray();
                unset($nuevoDetalle['id_cotizacion_detalle']);
                $nuevoDetalle['id_cotizacion'] = $nuevaCotizacion->id_cotizacion;
                $nuevoDetalle['fecha_actualizacion'] = now();
                $nuevoDetalle['apartado'] = ($nuevaCotizacion->certeza == 3) ? 1 : 0;
                CotizacionDetalle::create($nuevoDetalle);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Nueva versión creada correctamente',
                'data' => $nuevaCotizacion->load('detalles', 'cliente', 'fase')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear nueva versión: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear nueva versión: ' . $e->getMessage()
            ], 500);
        }
    }

 /**
     * Actualizar una cotización existente
     */
    protected function actualizarCotizacion(Cotizacion $cotizacion, array $validated): JsonResponse
    {
        try {
            DB::beginTransaction();

            $importeTotal = 0;
            $articulosData = [];
            $stockDisponible = true;
            $sucursalAsignadaId = $validated['id_sucursal_asignada'];

            foreach ($validated['articulos'] as $articulo) {
                $producto = CatalogoGeneral::find($articulo['id_producto']);
                if (!$producto) {
                    throw new \Exception('Producto no encontrado: ' . $articulo['id_producto']);
                }

                $descuento = $articulo['descuento'] ?? 0;
                $importe = $articulo['cantidad'] * $articulo['precio_unitario'] * (1 - $descuento / 100);
                $importeTotal += $importe;

                if ($sucursalAsignadaId && $articulo['id_sucursal_surtido'] == $sucursalAsignadaId) {
                    $stockProducto = $producto->inventario;
                    if ($stockProducto < $articulo['cantidad']) {
                        $stockDisponible = false;
                    }
                }

                $articulosData[] = [
                    'id_producto' => $articulo['id_producto'],
                    'codbar' => $producto->ean,
                    'descripcion' => $producto->descripcion,
                    'cantidad' => $articulo['cantidad'],
                    'precio_unitario' => $articulo['precio_unitario'],
                    'descuento' => $descuento,
                    'importe' => $importe,
                    'id_convenio' => $articulo['id_convenio'] ?? null,
                    'id_sucursal_surtido' => $articulo['id_sucursal_surtido'] ?? null,
                ];
            }

            $certeza = $validated['certeza'] ?? 0;
            $apartado = ($certeza == 3) ? 1 : 0;

            // Recalcular fecha de entrega
            $fechaEntrega = Cotizacion::calcularFechaEntregaSugerida(now(), $stockDisponible);

            $cotizacion->update([
                'id_fase' => $validated['id_fase'],
                'id_clasificacion' => $validated['id_clasificacion'] ?? null,
                'id_sucursal_asignada' => $validated['id_sucursal_asignada'] ?? null,
                'certeza' => $certeza,
                'importe_total' => $importeTotal,
                'comentarios' => $validated['comentarios'],
                'fecha_entrega_sugerida' => $fechaEntrega,
                'enviado' => 0, // Si se edita, se marca como no enviado (aunque ya estuviera enviado, no debería llegar aquí porque la opción de editar está deshabilitada para enviadas)
            ]);

            // Eliminar detalles existentes y crear nuevos
            $cotizacion->detalles()->delete();
            foreach ($articulosData as $detalle) {
                CotizacionDetalle::create(array_merge($detalle, [
                    'id_cotizacion' => $cotizacion->id_cotizacion,
                    'apartado' => $apartado
                ]));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cotización actualizada correctamente',
                'data' => $cotizacion->load('detalles', 'cliente', 'fase')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar cotización: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la cotización: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calcular similitud entre productos actuales y nuevos (porcentaje de productos que coinciden)
     */
    protected function calcularSimilitud(Cotizacion $cotizacion, array $nuevosArticulos): int
    {
        $productosActuales = $cotizacion->detalles->pluck('id_producto')->toArray();
        $productosNuevos = collect($nuevosArticulos)->pluck('id_producto')->toArray();

        $coincidencias = count(array_intersect($productosActuales, $productosNuevos));
        $totalActual = count($productosActuales);
        if ($totalActual == 0) return 0;

        return round(($coincidencias / $totalActual) * 100);
    }

    /**
     * Verificar si todos los productos tienen stock en la sucursal asignada
     */
    protected function verificarStockSucursal($detalles, $sucursalId): bool
    {
        if (!$sucursalId) return true;

        foreach ($detalles as $detalle) {
            $producto = CatalogoGeneral::find($detalle->id_producto);
            if ($producto && $producto->id_sucursal == $sucursalId && $producto->inventario < $detalle->cantidad) {
                return false;
            }
        }
        return true;
    }

    /**
     * Marcar cotización como enviada
     */
    public function enviar(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        try {
            $cotizacion = Cotizacion::findOrFail($id);
            if ($cotizacion->enviado) {
                return response()->json(['success' => false, 'message' => 'La cotización ya fue enviada'], 400);
            }

            $cotizacion->update([
                'enviado' => true,
                'fecha_envio' => now(),
                // Opcional: si certeza es Alta, cambiar fase a Completada (ajustar según tus catálogos)
                // 'id_fase' => $cotizacion->certeza == 3 ? CatFase::where('fase', 'Completada')->first()->id_fase : $cotizacion->id_fase,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cotización enviada correctamente',
                'data' => $cotizacion
            ]);

        } catch (\Exception $e) {
            Log::error('Error al enviar cotización: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la cotización: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'eliminar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            $cotizacion = Cotizacion::findOrFail($id);
            
            // Eliminar los detalles primero
            $cotizacion->detalles()->delete();
            
            // Eliminar la cotización físicamente
            $cotizacion->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Cotización eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar cotización: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la cotización: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function productosPorSucursal(int $sucursalId, Request $request): JsonResponse
    {
        $productoId = $request->input('producto_id');
        $ean = $request->input('ean');
        $cotizacionId = $request->input('cotizacion_id', null);
        
        \Log::info('productosPorSucursal llamado', [
            'sucursalId' => $sucursalId,
            'productoId' => $productoId,
            'ean' => $ean,
            'cotizacionId' => $cotizacionId
        ]);
        
        // Si no se proporcionó EAN pero sí productoId, obtener el EAN del producto original
        if (empty($ean) && $productoId) {
            $productoOriginal = CatalogoGeneral::find($productoId);
            if (!$productoOriginal) {
                return response()->json(['success' => true, 'data' => []]);
            }
            $ean = $productoOriginal->ean;
        }
        
        // Si no hay EAN ni productoId, no podemos buscar
        if (empty($ean)) {
            \Log::warning('productosPorSucursal: No se proporcionó EAN ni productoId');
            return response()->json(['success' => true, 'data' => []]);
        }
        
        // Buscar por EAN en la sucursal específica
        $query = CatalogoGeneral::with('sucursal')
            ->where('id_sucursal', $sucursalId)
            ->where('ean', $ean)
            ->where('activo', 1);
        
        $productos = $query->get(['id_catalogo_general', 'ean', 'descripcion', 'precio', 'inventario', 'num_familia']);
        
        \Log::info('Productos encontrados por EAN', [
            'sucursalId' => $sucursalId,
            'ean' => $ean,
            'cantidad' => $productos->count(),
            'productos' => $productos->pluck('id_catalogo_general')->toArray()
        ]);
        
        if ($productos->isEmpty()) {
            return response()->json(['success' => true, 'data' => []]);
        }
        
        // Calcular stock disponible restando productos apartados
        $productosApartados = DB::table('crm_cotizaciones_detalle as cd')
            ->join('crm_cotizaciones as c', 'cd.id_cotizacion', '=', 'c.id_cotizacion')
            ->where('cd.apartado', 1)
            ->where('c.activo', 1)
            ->where('c.certeza', '>=', 75)
            ->where('cd.id_sucursal_surtido', $sucursalId);
        
        // Buscar por los IDs de productos encontrados (puede haber múltiples con el mismo EAN?)
        $productosIds = $productos->pluck('id_catalogo_general')->toArray();
        if (!empty($productosIds)) {
            $productosApartados->whereIn('cd.id_producto', $productosIds);
        }
        
        if ($cotizacionId) {
            $productosApartados->where('c.id_cotizacion', '!=', $cotizacionId);
        }
        
        $productosApartados = $productosApartados
            ->select('cd.id_producto', 'cd.cantidad')
            ->get();
        
        \Log::info('Productos apartados encontrados', [
            'sucursalId' => $sucursalId,
            'productosIds' => $productosIds,
            'apartados' => $productosApartados->toArray()
        ]);
        
        $apartados = [];
        foreach ($productosApartados as $apartado) {
            $apartados[$apartado->id_producto] = ($apartados[$apartado->id_producto] ?? 0) + $apartado->cantidad;
        }
        
        $resultados = $productos->map(function($producto) use ($apartados) {
            $stockApartado = $apartados[$producto->id_catalogo_general] ?? 0;
            $stockDisponible = max(0, $producto->inventario - $stockApartado);

            //Asegurar que nombre_sucursal siempre tenga un valor
            $nombreSucursal = $producto->sucursal->nombre ?? 'Sin sucursal';
            
            // Log para depuración
            \Log::info('Mapeando producto para respuesta', [
                'producto_id' => $producto->id_catalogo_general,
                'sucursal_id' => $producto->id_sucursal,
                'sucursal_nombre' => $nombreSucursal,
                'descripcion' => $producto->descripcion
            ]);
            
            return [
                'id' => $producto->id_catalogo_general,
                'codbar' => $producto->ean,
                'nombre' => $producto->descripcion,
                'precio' => floatval($producto->precio),
                'inventario' => $stockDisponible,
                'inventario_original' => $producto->inventario,
                'apartado' => $stockApartado,
                'num_familia' => $producto->num_familia,
                'nombre_sucursal' => $nombreSucursal  // Asegurar que no sea null
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $resultados
        ]);
    }
}