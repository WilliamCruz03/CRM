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
        
        // Obtener productos apartados (de otras cotizaciones con certeza >= 75 y activas)
        $productosApartados = DB::table('crm_cotizaciones_detalle as cd')
            ->join('crm_cotizaciones as c', 'cd.id_cotizacion', '=', 'c.id_cotizacion')
            ->where('cd.apartado', 1)
            ->where('c.activo', 1)
            ->where('c.certeza', '>=', 75);
        
        // Excluir la cotización actual si se está editando
        if ($cotizacionId) {
            $productosApartados->where('c.id_cotizacion', '!=', $cotizacionId);
        }
        
        $productosApartados = $productosApartados
            ->select('cd.id_producto', 'cd.cantidad', 'cd.id_sucursal_surtido')
            ->get();
        
        $apartados = [];
        foreach ($productosApartados as $apartado) {
            $key = $apartado->id_producto . '_' . $apartado->id_sucursal_surtido;
            $apartados[$key] = ($apartados[$key] ?? 0) + $apartado->cantidad;
        }
        
        $productos = CatalogoGeneral::with('sucursal')
            ->where('activo', 1)
            ->where('inventario', '>', 0)
            ->where(function($query) use ($termino) {
                $query->where('descripcion', 'LIKE', "%{$termino}%")
                    ->orWhere('ean', 'LIKE', "%{$termino}%");
            })
            ->get(['id_catalogo_general', 'id_sucursal', 'ean', 'descripcion', 'precio', 'inventario', 'num_familia']);
        
        // Calcular stock disponible (inventario - apartados)
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
                'inventario' => $stockDisponible,
                'inventario_original' => $producto->inventario,
                'apartado' => $stockApartado,
                'num_familia' => $producto->num_familia
            ];
        })->filter(function($producto) {
            return $producto['inventario'] > 0;
        })->values();
        
        // Ordenar: primero los de la sucursal asignada
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
                'certeza' => 'nullable|integer|min:0|max:100',
                'comentarios' => 'nullable|string|max:500',
                'articulos' => 'required|array|min:1',
                'articulos.*.id_producto' => 'required|exists:catalogo_general,id_catalogo_general',
                'articulos.*.cantidad' => 'required|integer|min:1',
                'articulos.*.precio_unitario' => 'required|numeric|min:0',
                'articulos.*.descuento' => 'nullable|numeric|min:0|max:100',
                'articulos.*.id_convenio' => 'nullable|exists:cat_convenios,id_convenio',
                'articulos.*.id_sucursal_surtido' => 'nullable|exists:sucursales,id_sucursal'
            ]);
            
            \Log::info('Validación pasada:', $validated);
            
            DB::beginTransaction();
            
            $importeTotal = 0;
            $articulosData = [];
            
            foreach ($validated['articulos'] as $articulo) {
                $producto = CatalogoGeneral::find($articulo['id_producto']);
                if (!$producto) {
                    throw new \Exception('Producto no encontrado: ' . $articulo['id_producto']);
                }
                
                $descuento = $articulo['descuento'] ?? 0;
                $importe = $articulo['cantidad'] * $articulo['precio_unitario'] * (1 - $descuento / 100);
                $importeTotal += $importe;
                
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
            
            \Log::info('Articulos procesados:', $articulosData);

            $apartado = ($validated['certeza'] ?? 0) >= 75 ? 1 : 0;
            
            $cotizacion = Cotizacion::create([
                'id_cliente' => $validated['id_cliente'],
                'id_fase' => $validated['id_fase'],
                'id_clasificacion' => $validated['id_clasificacion'] ?? null,
                'id_sucursal_asignada' => $validated['id_sucursal_asignada'] ?? null,
                'certeza' => $validated['certeza'] ?? 0,
                'importe_total' => $importeTotal,
                'comentarios' => $validated['comentarios'],
                'activo' => 1
            ]);
            
            \Log::info('Cotización creada con ID: ' . $cotizacion->id_cotizacion);
            
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
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Error de validación:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear cotización: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
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
        
        $validated = $request->validate([
            'id_fase' => 'required|exists:cat_fases,id_fase',
            'id_clasificacion' => 'nullable|exists:cat_clasificaciones,id_clasificacion',
            'id_sucursal_asignada' => 'nullable|exists:sucursales,id_sucursal',
            'certeza' => 'nullable|integer|min:0|max:100',
            'comentarios' => 'nullable|string|max:500',
            'articulos' => 'required|array|min:1',
            'articulos.*.id_producto' => 'required|exists:catalogo_general,id_catalogo_general',
            'articulos.*.cantidad' => 'required|integer|min:1',
            'articulos.*.precio_unitario' => 'required|numeric|min:0',
            'articulos.*.descuento' => 'nullable|numeric|min:0|max:100',
            'articulos.*.id_convenio' => 'nullable|exists:cat_convenios,id_convenio',
            'articulos.*.id_sucursal_surtido' => 'nullable|exists:sucursales,id_sucursal'
        ]);
        
        try {
            DB::beginTransaction();
            
            $importeTotal = 0;
            $articulosData = [];
            
            foreach ($validated['articulos'] as $articulo) {
                $producto = CatalogoGeneral::find($articulo['id_producto']);
                $descuento = $articulo['descuento'] ?? 0;
                $importe = $articulo['cantidad'] * $articulo['precio_unitario'] * (1 - $descuento / 100);
                $importeTotal += $importe;
                
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
            
            // Calcular apartado ANTES de usarlo
            $apartado = ($validated['certeza'] ?? 0) >= 75 ? 1 : 0;
            
            $cotizacion->update([
                'id_fase' => $validated['id_fase'],
                'id_clasificacion' => $validated['id_clasificacion'] ?? null,
                'id_sucursal_asignada' => $validated['id_sucursal_asignada'] ?? null,
                'certeza' => $validated['certeza'] ?? 0,
                'importe_total' => $importeTotal,
                'comentarios' => $validated['comentarios'],
            ]);
            
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
    
    public function destroy(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'eliminar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            $cotizacion = Cotizacion::findOrFail($id);
            $cotizacion->update(['activo' => 0]);
            
            return response()->json([
                'success' => true,
                'message' => 'Cotización eliminada correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la cotización'
            ], 500);
        }
    }
    
    public function productosPorSucursal(int $sucursalId, Request $request): JsonResponse
    {
        $productoId = $request->input('producto_id');
        
        $query = CatalogoGeneral::with('sucursal')
            ->where('id_sucursal', $sucursalId)
            ->where('activo', 1);
        
        if ($productoId) {
            $query->where('id_catalogo_general', $productoId);
        }
        
        $productos = $query->get(['id_catalogo_general', 'ean', 'descripcion', 'precio', 'inventario', 'num_familia']);
        
        // Calcular stock disponible restando productos apartados de otras cotizaciones
        $cotizacionId = $request->input('cotizacion_id', null);
        
        $productosApartados = DB::table('crm_cotizaciones_detalle as cd')
            ->join('crm_cotizaciones as c', 'cd.id_cotizacion', '=', 'c.id_cotizacion')
            ->where('cd.apartado', 1)
            ->where('c.activo', 1)
            ->where('c.certeza', '>=', 75);
        
        if ($cotizacionId) {
            $productosApartados->where('c.id_cotizacion', '!=', $cotizacionId);
        }
        
        if ($productoId) {
            $productosApartados->where('cd.id_producto', $productoId);
        }
        
        $productosApartados = $productosApartados
            ->where('cd.id_sucursal_surtido', $sucursalId)
            ->select('cd.id_producto', 'cd.cantidad')
            ->get();
        
        $apartados = [];
        foreach ($productosApartados as $apartado) {
            $apartados[$apartado->id_producto] = ($apartados[$apartado->id_producto] ?? 0) + $apartado->cantidad;
        }
        
        $resultados = $productos->map(function($producto) use ($apartados) {
            $stockApartado = $apartados[$producto->id_catalogo_general] ?? 0;
            $stockDisponible = max(0, $producto->inventario - $stockApartado);
            
            return [
                'id' => $producto->id_catalogo_general,
                'codbar' => $producto->ean,
                'nombre' => $producto->descripcion,
                'precio' => floatval($producto->precio),
                'inventario' => $stockDisponible,
                'inventario_original' => $producto->inventario,
                'apartado' => $stockApartado,
                'num_familia' => $producto->num_familia,
                'nombre_sucursal' => $producto->sucursal->nombre ?? 'N/A'
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $resultados
        ]);
    }
}