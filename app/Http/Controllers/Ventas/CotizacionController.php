<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Cotizaciones\Cotizacion;
use App\Models\Cotizaciones\CotizacionDetalle;
use App\Models\Cotizaciones\CatFase;
use App\Models\Cotizaciones\CatClasificacion;
use App\Models\Cotizaciones\CatConvenio;
use App\Models\Cliente;
use App\Models\Sucursal;
use App\Models\CatalogoGeneral;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

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
        
        $clientes = Cliente::whereIn('status', Cliente::getActiveStatuses())
            ->where(function($query) use ($termino) {
                $query->where('Nombre', 'LIKE', "%{$termino}%")
                    ->orWhere('apPaterno', 'LIKE', "%{$termino}%")
                    ->orWhere('apMaterno', 'LIKE', "%{$termino}%")
                    ->orWhere('telefono1', 'LIKE', "%{$termino}%")
                    ->orWhere('telefono2', 'LIKE', "%{$termino}%")
                    ->orWhere('email1', 'LIKE', "%{$termino}%")
                    ->orWhere('domicilio', 'LIKE', "%{$termino}%");
            })
            ->limit(10)
            ->get(['id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'email1', 'telefono1', 'telefono2', 'titulo', 'domicilio']);
        
        return response()->json([
            'success' => true,
            'data' => $clientes->map(function($cliente) {
                // Construir contacto con prioridad
                $contactoPrincipal = $cliente->telefono1 ?: ($cliente->telefono2 ?: $cliente->email1 ?: $cliente->domicilio);
                
                // Construir HTML de contacto para mostrar
                $contactoHtml = '';
                if ($cliente->telefono1) {
                    $contactoHtml .= "<i class='bi bi-telephone'></i> {$cliente->telefono1}<br>";
                }
                if ($cliente->telefono2) {
                    $contactoHtml .= "<i class='bi bi-telephone'></i> {$cliente->telefono2} (sec)<br>";
                }
                if ($cliente->email1) {
                    $contactoHtml .= "<i class='bi bi-envelope'></i> {$cliente->email1}<br>";
                }
                if ($cliente->domicilio) {
                    $contactoHtml .= "<i class='bi bi-house'></i> {$cliente->domicilio}";
                }
                
                return [
                    'id' => $cliente->id_Cliente,
                    'nombre' => $cliente->nombre_completo,
                    'contacto_principal' => $contactoPrincipal,
                    'contacto_html' => $contactoHtml ?: 'Sin contacto'
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
            ->where('c.certeza', 3);

        if ($cotizacionId && $cotizacionId > 0) {
            $productosApartadosQuery->where('c.id_cotizacion', '!=', $cotizacionId);
        }

        $productosApartados = $productosApartadosQuery
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
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'crear')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        try {
            $validated = $request->validate([
                'id_cliente' => 'required|exists:catalogo_cliente_maestro,id_Cliente',
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

            DB::beginTransaction();

            $importeTotal = 0;
            $articulosData = [];
            $stockDisponible = true;
            $sucursalAsignadaId = $validated['id_sucursal_asignada'] ?? null;

            foreach ($validated['articulos'] as $articulo) {
                $producto = CatalogoGeneral::find($articulo['id_producto']);
                if (!$producto) {
                    throw new \Exception('Producto no encontrado: ' . $articulo['id_producto']);
                }

                $descuento = $articulo['descuento'] ?? 0;
                $importe = $articulo['cantidad'] * $articulo['precio_unitario'] * (1 - $descuento / 100);
                $importeTotal += $importe;

                if ($sucursalAsignadaId && $articulo['id_sucursal_surtido'] == $sucursalAsignadaId) {
                    if ($producto->inventario < $articulo['cantidad']) {
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
    
    /**
     * Update the specified quotation.
     * Handles: edit current, overwrite, new independent (sin versión)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        $cotizacion = Cotizacion::findOrFail($id);
        $esEnviada = $cotizacion->enviado;
        $accion = $request->input('accion', 'editar'); // 'editar', 'sobrescribir', 'nueva_sin_version'

        // Caso: crear nueva cotización independiente (sin versión)
        if ($accion === 'nueva_sin_version') {
            return $this->crearNuevaSinVersion($request);
        }

        // Si es enviada, no se puede editar la misma
        if ($esEnviada && $accion !== 'sobrescribir') {
            return response()->json([
                'success' => false,
                'message' => 'Esta cotización ya fue enviada. Solo puede crear una nueva versión o una nueva cotización sin versión.'
            ], 403);
        }

        // Validación común
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

        // Verificar similitud solo si no se fuerza sobrescribir
        $forzar = $request->input('forzar', false) || $accion === 'sobrescribir';
        if (!$forzar) {
            $similitud = $this->calcularSimilitud($cotizacion, $validated['articulos']);
            if ($similitud < 50) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los productos han cambiado significativamente. ¿Qué deseas hacer?',
                    'similitud' => $similitud,
                    'requiere_confirmacion' => true
                ], 409);
            }
        }

        // Actualizar la cotización actual (sobrescribir o editar normal)
        return $this->actualizarCotizacion($cotizacion, $validated);
    }

    /**
     * Prepare data to create a new version (preload modal)
     */
    public function prepararNuevaVersion(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'crear')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        $cotizacionOriginal = Cotizacion::with('detalles', 'cliente')->findOrFail($id);
        
        $datosPrecarga = [
            'id_cotizacion_origen' => $cotizacionOriginal->id_cotizacion,
            'id_cliente' => $cotizacionOriginal->id_cliente,
            'cliente_nombre' => $cotizacionOriginal->nombre_cliente,
            'cliente_email' => $cotizacionOriginal->cliente->email1 ?? '',
            'id_fase' => $cotizacionOriginal->id_fase,
            'id_clasificacion' => $cotizacionOriginal->id_clasificacion,
            'id_sucursal_asignada' => $cotizacionOriginal->id_sucursal_asignada,
            'certeza' => $cotizacionOriginal->certeza,
            'comentarios' => $cotizacionOriginal->comentarios,
            'articulos' => $cotizacionOriginal->detalles->map(function($detalle) {
                return [
                    'id_producto' => $detalle->id_producto,
                    'codbar' => $detalle->codbar,
                    'nombre' => $detalle->descripcion,
                    'precio' => $detalle->precio_unitario,
                    'cantidad' => $detalle->cantidad,
                    'descuento' => $detalle->descuento,
                    'id_convenio' => $detalle->id_convenio,
                    'id_sucursal_surtido' => $detalle->id_sucursal_surtido,
                    'num_familia' => $detalle->producto->num_familia ?? '',
                    'inventario_disponible' => $detalle->producto->inventario ?? 0,
                    'nombre_sucursal_surtido' => $detalle->sucursalSurtido->nombre ?? ''
                ];
            })
        ];
        
        return response()->json([
            'success' => true,
            'data' => $datosPrecarga
        ]);
    }

    /**
     * Save a new version (with versioning, previous becomes inactive)
     */
    public function guardarNuevaVersion(Request $request, int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'crear')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $cotizacionOriginal = Cotizacion::findOrFail($id);
            
            $validated = $request->validate([
                'id_cliente' => 'required|exists:catalogo_cliente_maestro,id_Cliente',
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
            
            // Desactivar la cotización original (se convierte en histórica)
            $cotizacionOriginal->activo = 0;
            $cotizacionOriginal->save();
            
            $importeTotal = 0;
            $articulosData = [];
            $stockDisponible = true;
            $sucursalAsignadaId = $validated['id_sucursal_asignada'];
            
            foreach ($validated['articulos'] as $articulo) {
                $producto = CatalogoGeneral::find($articulo['id_producto']);
                if (!$producto) {
                    throw new \Exception('Producto no encontrado');
                }
                
                $descuento = $articulo['descuento'] ?? 0;
                $importe = $articulo['cantidad'] * $articulo['precio_unitario'] * (1 - $descuento / 100);
                $importeTotal += $importe;
                
                if ($sucursalAsignadaId && $articulo['id_sucursal_surtido'] == $sucursalAsignadaId) {
                    if ($producto->inventario < $articulo['cantidad']) {
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
            $fechaEntrega = Cotizacion::calcularFechaEntregaSugerida(now(), $stockDisponible);
            
            // Obtener ID de fase "En proceso" para la nueva versión
            $faseEnProceso = CatFase::where('fase', 'En proceso')->first();
            $faseEnProcesoId = $faseEnProceso ? $faseEnProceso->id_fase : 1;
            
            $nuevaCotizacion = Cotizacion::create([
                'folio' => Cotizacion::generarFolio(),
                'id_cliente' => $validated['id_cliente'],
                'id_fase' => $faseEnProcesoId, // Forzar fase "En proceso"
                'id_clasificacion' => $validated['id_clasificacion'] ?? null,
                'id_sucursal_asignada' => $validated['id_sucursal_asignada'] ?? null,
                'certeza' => $certeza,
                'importe_total' => $importeTotal,
                'comentarios' => $validated['comentarios'],
                'fecha_entrega_sugerida' => $fechaEntrega,
                'activo' => 1,
                'enviado' => 0,
                'version' => $cotizacionOriginal->version + 1,
                'cotizacion_origen_id' => $cotizacionOriginal->id_cotizacion,
            ]);
            
            foreach ($articulosData as $detalle) {
                CotizacionDetalle::create(array_merge($detalle, [
                    'id_cotizacion' => $nuevaCotizacion->id_cotizacion,
                    'apartado' => $apartado
                ]));
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Nueva versión creada correctamente',
                'data' => $nuevaCotizacion->load('detalles', 'cliente', 'fase')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al guardar nueva versión: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear nueva versión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a completely new independent quotation (no version relation)
     */
    protected function crearNuevaSinVersion(Request $request): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'crear')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        try {
            $validated = $request->validate([
                'id_cliente' => 'required|exists:catalogo_cliente_maestro,id_Cliente',
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

            DB::beginTransaction();

            $importeTotal = 0;
            $articulosData = [];
            $stockDisponible = true;
            $sucursalAsignadaId = $validated['id_sucursal_asignada'] ?? null;

            foreach ($validated['articulos'] as $articulo) {
                $producto = CatalogoGeneral::find($articulo['id_producto']);
                if (!$producto) {
                    throw new \Exception('Producto no encontrado: ' . $articulo['id_producto']);
                }

                $descuento = $articulo['descuento'] ?? 0;
                $importe = $articulo['cantidad'] * $articulo['precio_unitario'] * (1 - $descuento / 100);
                $importeTotal += $importe;

                if ($sucursalAsignadaId && $articulo['id_sucursal_surtido'] == $sucursalAsignadaId) {
                    if ($producto->inventario < $articulo['cantidad']) {
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
            $fechaEntrega = Cotizacion::calcularFechaEntregaSugerida(now(), $stockDisponible);

            $nuevaCotizacion = Cotizacion::create([
                'folio' => Cotizacion::generarFolio(),
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
                'cotizacion_origen_id' => null,
            ]);

            foreach ($articulosData as $detalle) {
                CotizacionDetalle::create(array_merge($detalle, [
                    'id_cotizacion' => $nuevaCotizacion->id_cotizacion,
                    'apartado' => $apartado
                ]));
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Nueva cotización creada correctamente (sin versión)',
                'data' => $nuevaCotizacion->load('detalles', 'cliente', 'fase')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear nueva cotización sin versión: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear nueva cotización: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update existing quotation (overwrite)
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
                    if ($producto->inventario < $articulo['cantidad']) {
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
            $fechaEntrega = Cotizacion::calcularFechaEntregaSugerida(now(), $stockDisponible);

            $cotizacion->update([
                'id_fase' => $validated['id_fase'],
                'id_clasificacion' => $validated['id_clasificacion'] ?? null,
                'id_sucursal_asignada' => $validated['id_sucursal_asignada'] ?? null,
                'certeza' => $certeza,
                'importe_total' => $importeTotal,
                'comentarios' => $validated['comentarios'],
                'fecha_entrega_sugerida' => $fechaEntrega,
                'enviado' => 0, // If edited, mark as not sent
            ]);

            // Replace details
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
     * Calculate similarity between old and new products (%)
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
     * Verify stock availability in assigned branch
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
     * Mark as sent (used by PDF generation first time)
     */
/**
 * Mark as sent (used by PDF generation first time)
 */
    public function marcarComoEnviada(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        try {
            $cotizacion = Cotizacion::findOrFail($id);
            
            if ($cotizacion->enviado) {
                return response()->json(['success' => false, 'message' => 'La cotización ya fue enviada'], 400);
            }

            // Obtener ID de fase "Completada"
            $faseCompletada = CatFase::where('fase', 'Completada')->first();
            $faseCompletadaId = $faseCompletada ? $faseCompletada->id_fase : 2;
            
            $cotizacion->update([
                'enviado' => true,
                'fecha_envio' => now(),
                'id_fase' => $faseCompletadaId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cotización marcada como enviada y completada',
                'data' => $cotizacion
            ]);

        } catch (\Exception $e) {
            Log::error('Error al marcar cotización como enviada: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al marcar la cotización: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF ticket (first time also marks as sent)
     */
    public function ticket(int $id)
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'ver')) {
            abort(403, 'No tienes permiso');
        }
        
        $cotizacion = Cotizacion::with([
            'cliente', 'fase', 'clasificacion', 'sucursalAsignada',
            'detalles.producto', 'detalles.convenio', 'detalles.sucursalSurtido'
        ])->findOrFail($id);
        
        if (!$cotizacion->enviado) {
            $faseCompletada = CatFase::where('fase', 'Completada')->first();
            $faseCompletadaId = $faseCompletada ? $faseCompletada->id_fase : 2;
            
            $cotizacion->update([
                'enviado' => true,
                'fecha_envio' => now(),
                'id_fase' => $faseCompletadaId,
            ]);
            $cotizacion->refresh();
        }
        
        $pdf = Pdf::loadView('ventas.cotizaciones.ticket', compact('cotizacion'));
        $pdf->setPaper('letter', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'marginTop' => 5,
            'marginRight' => 10,
            'marginBottom' => 5,
            'marginLeft' => 10,
        ]);
        
        return $pdf->download("Cotizacion_{$cotizacion->folio}.pdf");
    }

    /**
     * Preview ticket in browser
     */
    public function previewTicket(int $id)
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'ver')) {
            abort(403, 'No tienes permiso');
        }
        
        $cotizacion = Cotizacion::with([
            'cliente', 'fase', 'clasificacion', 'sucursalAsignada',
            'detalles.producto', 'detalles.convenio', 'detalles.sucursalSurtido'
        ])->findOrFail($id);
        
        $pdf = Pdf::loadView('ventas.cotizaciones.ticket', compact('cotizacion'));
        $pdf->setPaper('letter', 'portrait');
        
        return $pdf->stream("Cotizacion_{$cotizacion->folio}.pdf");
    }

    /**
     * Delete quotation (soft delete physically)
     */
    public function destroy(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'eliminar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            $cotizacion = Cotizacion::findOrFail($id);
            $cotizacion->detalles()->delete();
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
        
        if (empty($ean) && $productoId) {
            $productoOriginal = CatalogoGeneral::find($productoId);
            if (!$productoOriginal) {
                return response()->json(['success' => true, 'data' => []]);
            }
            $ean = $productoOriginal->ean;
        }
        
        if (empty($ean)) {
            return response()->json(['success' => true, 'data' => []]);
        }
        
        $query = CatalogoGeneral::with('sucursal')
            ->where('id_sucursal', $sucursalId)
            ->where('ean', $ean)
            ->where('activo', 1);
        
        $productos = $query->get(['id_catalogo_general', 'ean', 'descripcion', 'precio', 'inventario', 'num_familia']);
        
        if ($productos->isEmpty()) {
            return response()->json(['success' => true, 'data' => []]);
        }
        
        $productosApartados = DB::table('crm_cotizaciones_detalle as cd')
            ->join('crm_cotizaciones as c', 'cd.id_cotizacion', '=', 'c.id_cotizacion')
            ->where('cd.apartado', 1)
            ->where('c.activo', 1)
            ->where('c.certeza', 3)
            ->where('cd.id_sucursal_surtido', $sucursalId);
        
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
        
        $apartados = [];
        foreach ($productosApartados as $apartado) {
            $apartados[$apartado->id_producto] = ($apartados[$apartado->id_producto] ?? 0) + $apartado->cantidad;
        }
        
        $resultados = $productos->map(function($producto) use ($apartados) {
            $stockApartado = $apartados[$producto->id_catalogo_general] ?? 0;
            $stockDisponible = max(0, $producto->inventario - $stockApartado);
            $nombreSucursal = $producto->sucursal->nombre ?? 'Sin sucursal';
            
            return [
                'id' => $producto->id_catalogo_general,
                'codbar' => $producto->ean,
                'nombre' => $producto->descripcion,
                'precio' => floatval($producto->precio),
                'inventario' => $stockDisponible,
                'inventario_original' => $producto->inventario,
                'apartado' => $stockApartado,
                'num_familia' => $producto->num_familia,
                'nombre_sucursal' => $nombreSucursal
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $resultados
        ]);
    }

    /**
     * Get all previous versions of a quotation (exclude current active one)
     */
    public function versiones(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'ver')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            $cotizacion = Cotizacion::findOrFail($id);
            
            $versiones = collect();
            
            // Buscar versiones anteriores (por cotizacion_origen_id)
            $actual = $cotizacion;
            while ($actual->cotizacion_origen_id) {
                $anterior = Cotizacion::with(['detalles.producto', 'detalles.convenio', 'detalles.sucursalSurtido'])
                    ->find($actual->cotizacion_origen_id);
                if ($anterior) {
                    $versiones->push($anterior);
                    $actual = $anterior;
                } else {
                    break;
                }
            }
            
            // Ordenar por versión descendente (más reciente primero)
            $versiones = $versiones->sortByDesc('version')->values();
            
            return response()->json([
                'success' => true,
                'data' => $versiones->map(function($v) {
                    return [
                        'id_cotizacion' => $v->id_cotizacion,
                        'folio' => $v->folio,
                        'version' => $v->version,
                        'activo' => $v->activo,
                        'certeza' => $v->certeza,
                        'certeza_nombre' => $v->certeza_nombre,
                        'fecha_creacion' => $v->fecha_creacion,
                        'comentarios' => $v->comentarios,
                        'enviado' => $v->enviado,
                        'importe_total' => $v->importe_total,
                        'detalles' => $v->detalles->map(function($detalle) {
                            return [
                                'id_producto' => $detalle->id_producto,
                                'codbar' => $detalle->codbar,
                                'descripcion' => $detalle->descripcion,
                                'cantidad' => $detalle->cantidad,
                                'precio_unitario' => $detalle->precio_unitario,
                                'descuento' => $detalle->descuento,
                                'importe' => $detalle->importe,
                                'nombre_sucursal_surtido' => $detalle->sucursalSurtido->nombre ?? 'No asignada',
                                'nombre_convenio' => $detalle->convenio->nombre ?? 'No aplica'
                            ];
                        })
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener versiones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el historial de versiones'
            ], 500);
        }
    }
}