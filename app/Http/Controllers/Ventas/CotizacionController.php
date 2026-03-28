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
        $sucursalId = $request->input('sucursal_id', null);
        
        $productos = CatalogoGeneral::query();
        
        if ($termino) {
            $productos->where(function($query) use ($termino) {
                $query->where('descripcion', 'LIKE', "%{$termino}%")
                    ->orWhere('ean', 'LIKE', "%{$termino}%");
            });
        }
        
        if ($sucursalId) {
            $productos->where('id_sucursal', $sucursalId);
        }
        
        $productos = $productos->limit(20)->get(['id_catalogo_general', 'ean', 'descripcion', 'precio']);
        
        return response()->json([
            'success' => true,
            'data' => $productos->map(function($producto) {
                return [
                    'id' => $producto->id_catalogo_general,
                    'codbar' => $producto->ean,
                    'nombre' => $producto->descripcion,
                    'precio' => floatval($producto->precio)
                ];
            })
        ]);
    }
    
    public function catalogos(): JsonResponse
    {
        try {
            $fases = CatFase::where('activo', 1)->get(['id_fase', 'fase']);
            $clasificaciones = CatClasificacion::where('activo', 1)->get(['id_clasificacion', 'clasificacion']);
            $sucursales = Sucursal::where('activo', 1)->get(['id_sucursal', 'nombre']);
            
            // Obtener convenios (sin status)
            $convenios = CatConvenio::with(['detalles' => function($q) {
                $q->select('id_convenio', 'porcentaje_descuento');
            }])
            ->where('tipo', 'C')
            ->get(['id', 'nombre']);
            
            $conveniosFormateados = $convenios->map(function($convenio) {
                $descuento = $convenio->detalles->first()?->porcentaje_descuento ?? 0;
                return [
                    'id' => $convenio->id,
                    'nombre' => $convenio->nombre,
                    'porcentaje_descuento' => $descuento
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
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'crear')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        $validated = $request->validate([
            'id_cliente' => 'required|exists:catalogo_cliente_maestro,id_Cliente',
            'id_fase' => 'required|exists:cat_fases,id_fase',
            'id_clasificacion' => 'nullable|exists:cat_clasificaciones,id_clasificacion',
            'id_sucursal_asignada' => 'nullable|exists:sucursales,id_sucursal',
            'comentarios' => 'nullable|string|max:500',
            'articulos' => 'required|array|min:1',
            'articulos.*.id_producto' => 'required|exists:catalogo_general,id_catalogo_general',
            'articulos.*.cantidad' => 'required|integer|min:1',
            'articulos.*.precio_unitario' => 'required|numeric|min:0',
            'articulos.*.descuento' => 'nullable|numeric|min:0|max:100',
            'articulos.*.id_convenio' => 'nullable|exists:cat_convenios,id',
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
            
            $cotizacion = Cotizacion::create([
                'id_cliente' => $validated['id_cliente'],
                'id_fase' => $validated['id_fase'],
                'id_clasificacion' => $validated['id_clasificacion'] ?? null,
                'id_sucursal_asignada' => $validated['id_sucursal_asignada'] ?? null,
                'importe_total' => $importeTotal,
                'comentarios' => $validated['comentarios'],
                'activo' => 1
            ]);
            
            foreach ($articulosData as $detalle) {
                CotizacionDetalle::create(array_merge($detalle, [
                    'id_cotizacion' => $cotizacion->id_cotizacion
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
        
        $validated = $request->validate([
            'id_fase' => 'required|exists:cat_fases,id_fase',
            'id_clasificacion' => 'nullable|exists:cat_clasificaciones,id_clasificacion',
            'id_sucursal_asignada' => 'nullable|exists:sucursales,id_sucursal',
            'comentarios' => 'nullable|string|max:500',
            'articulos' => 'required|array|min:1',
            'articulos.*.id_producto' => 'required|exists:catalogo_general,id_catalogo_general',
            'articulos.*.cantidad' => 'required|integer|min:1',
            'articulos.*.precio_unitario' => 'required|numeric|min:0',
            'articulos.*.descuento' => 'nullable|numeric|min:0|max:100',
            'articulos.*.id_convenio' => 'nullable|exists:cat_convenios,id',
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
            
            $cotizacion->update([
                'id_fase' => $validated['id_fase'],
                'id_clasificacion' => $validated['id_clasificacion'] ?? null,
                'id_sucursal_asignada' => $validated['id_sucursal_asignada'] ?? null,
                'importe_total' => $importeTotal,
                'comentarios' => $validated['comentarios'],
            ]);
            
            $cotizacion->detalles()->delete();
            
            foreach ($articulosData as $detalle) {
                CotizacionDetalle::create(array_merge($detalle, [
                    'id_cotizacion' => $cotizacion->id_cotizacion
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
    
    public function productosPorSucursal(int $sucursalId): JsonResponse
    {
        $productos = CatalogoGeneral::where('id_sucursal', $sucursalId)
            ->where('activo', 1)
            ->where('inventario', '>', 0)
            ->get(['id_catalogo_general', 'ean', 'descripcion', 'precio', 'inventario']);
        
        return response()->json([
            'success' => true,
            'data' => $productos
        ]);
    }
}