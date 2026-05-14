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
use App\Models\Configuracion;
use App\Models\Pedidos\OrdenPedido;
use App\Models\Pedidos\OrdenPedidoDetalle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\TmpCatalogo;
use Carbon\Carbon;

class CotizacionController extends Controller
{
    public function index(): View
    {
        $puedeVer = auth()->user()->puede('ventas', 'cotizaciones', 'ver');
        $puedeCrear = auth()->user()->puede('ventas', 'cotizaciones', 'crear');
        
        if (!$puedeVer && !$puedeCrear) {
            abort(403, 'No tienes permiso para acceder a este módulo');
        }
        
        // Inicializar $cotizaciones como un paginador vacío
        $cotizaciones = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);
        
        if ($puedeVer) {
            $cotizaciones = Cotizacion::with(['cliente' => function($query) {
                    $query->select('id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'telefono1', 'telefono2', 'email1');
                }, 'fase', 'clasificacion', 'sucursalAsignada'])
                ->activas()
                ->where('es_pedido', '!=', 1)
                ->orderBy('id_cotizacion', 'desc')
                ->paginate(15);
        }
        
        $permisos = [
            'ver' => $puedeVer,
            'crear' => $puedeCrear,
            'editar' => auth()->user()->puede('ventas', 'cotizaciones', 'editar'),
            'eliminar' => auth()->user()->puede('ventas', 'cotizaciones', 'eliminar'),
        ];
        
        $sucursalAsignadaUsuario = auth()->user()->sucursal_asignada ?? 0;
        
        return view('ventas.cotizaciones.index', compact('cotizaciones', 'permisos', 'sucursalAsignadaUsuario'));
    }
    
    public function buscarClientes(Request $request): JsonResponse
    {
        $termino = $request->input('q', '');
        
        // Excluir clientes BLOQUEADOS e INACTIVOS (no pueden tener cotizaciones)
        $clientes = Cliente::whereIn('status', ['CLIENTE', 'PROSPECTO'])
            ->where(function($query) use ($termino) {
                $query->where('id_Cliente', 'LIKE', "%{$termino}%")
                    ->orWhere('Nombre', 'LIKE', "%{$termino}%")
                    ->orWhere('apPaterno', 'LIKE', "%{$termino}%")
                    ->orWhere('apMaterno', 'LIKE', "%{$termino}%")
                    ->orWhere('telefono1', 'LIKE', "%{$termino}%")
                    ->orWhere('telefono2', 'LIKE', "%{$termino}%")
                    ->orWhere('email1', 'LIKE', "%{$termino}%")
                    ->orWhereRaw("CONCAT(Nombre, ' ', apPaterno, ' ', COALESCE(apMaterno, '')) LIKE ?", ["%{$termino}%"]);
            })
            ->limit(10)
            ->get(['id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'email1', 'telefono1', 'telefono2', 'titulo', 'Domicilio']);
        
        return response()->json([
            'success' => true,
            'data' => $clientes->map(function($cliente) {
                // Nombre completo con título como small
                $nombreCompleto = $cliente->nombre_completo;
                $tituloHtml = '';
                if ($cliente->titulo) {
                    $tituloHtml = "<br><small class='text-muted'>{$cliente->titulo}</small>";
                }
                
                // Contacto: orden prioridad: telefono1, telefono2, email1
                $contactoHtml = '';
                if ($cliente->telefono1) {
                    $contactoHtml .= "<i class='bi bi-telephone'></i> {$cliente->telefono1}<br>";
                }
                if ($cliente->telefono2) {
                    $contactoHtml .= "<i class='bi bi-telephone'></i> {$cliente->telefono2} (secundario)<br>";
                }
                if ($cliente->email1) {
                    $contactoHtml .= "<i class='bi bi-envelope'></i> {$cliente->email1}";
                }
                
                // Dirección como small
                $direccionHtml = '';
                if ($cliente->Domicilio) {
                    $direccionHtml = "<br><small class='text-muted'><i class='bi bi-geo-alt'></i> {$cliente->Domicilio}</small>";
                }
                
                return [
                    'id' => $cliente->id_Cliente,
                    'nombre_completo' => $nombreCompleto,
                    'Nombre' => $cliente->Nombre,
                    'apPaterno' => $cliente->apPaterno,
                    'apMaterno' => $cliente->apMaterno,
                    'titulo_html' => $tituloHtml,
                    'contacto_html' => $contactoHtml ?: '<span class="text-muted">Sin contacto</span>',
                    'direccion_html' => $direccionHtml,
                    'email' => $cliente->email1,
                    'email1' => $cliente->email1, // Consistencia en modelo para editar cliente
                    'telefono1' => $cliente->telefono1,
                    'telefono2' => $cliente->telefono2,
                    'titulo' => $cliente->titulo,
                    'domicilio' => $cliente->Domicilio
                ];
            })
        ]);
    }

    public function buscarProductos(Request $request): JsonResponse
    {
        $termino = $request->input('q', '');
        $cotizacionId = $request->input('cotizacion_id', null);

        // Si el término tiene menos de 3 caracteres, no buscar
        if (strlen($termino) < 3) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        if ($cotizacionId && is_numeric($cotizacionId)) {
            $cotizacionId = (int) $cotizacionId;
        }

        // ============================================
        // 1. OBTENER PRODUCTOS APARTADOS (desde CRM)
        // ============================================
        $productosApartadosQuery = DB::table('crm_cotizaciones_detalle as cd')
            ->join('crm_cotizaciones as c', 'cd.id_cotizacion', '=', 'c.id_cotizacion')
            ->where('cd.apartado', 1)
            ->where('c.activo', 1)
            ->where('c.es_pedido', '!=', 1)
            ->where('c.certeza', 3);

        if ($cotizacionId && $cotizacionId > 0) {
            $productosApartadosQuery->where('c.id_cotizacion', '!=', $cotizacionId);
        }

        $productosApartados = $productosApartadosQuery
            ->select('cd.codbar', 'cd.cantidad')
            ->get();

        // Sumar cantidades apartadas por producto (sin sucursal por ahora)
        $apartadosPorProducto = [];
        foreach ($productosApartados as $apartado) {
            $apartadosPorProducto[$apartado->id_producto] = ($apartadosPorProducto[$apartado->id_producto] ?? 0) + $apartado->cantidad;
        }

        $todosLosProductos = collect();

        // ============================================
        // 2. BUSCAR EN CATALOGO GENERAL (desde Matriz)
        // ============================================
        $queryProductos = CatalogoGeneral::with(['sucursal'])
            ->where('activo', 1)
            ->where('inventario', '>', 0);

        if (!empty($termino)) {
            $queryProductos->where(function($query) use ($termino) {
                // Usar COLLATE para ignorar acentos en la búsqueda
                $query->whereRaw("descripcion COLLATE SQL_Latin1_General_CP1_CI_AI LIKE ?", ["%{$termino}%"])
                    ->orWhere('ean', 'LIKE', "%{$termino}%")
                    ->orWhereExists(function($subquery) use ($termino) {
                        $subquery->select(DB::raw(1))
                            ->from('catalogo_maestro')
                            ->join('cat_sales_presentacion', 'catalogo_maestro.sales_presentacion', '=', 'cat_sales_presentacion.id')
                            ->whereColumn('catalogo_maestro.EAN', 'catalogo_general.ean')
                            ->whereRaw("cat_sales_presentacion.sustancia COLLATE SQL_Latin1_General_CP1_CI_AI LIKE ?", ["%{$termino}%"]);
                    });
            });
        }

        $productosNormales = $queryProductos
            ->limit(10)
            ->get([
                'id_catalogo_general',
                'id_sucursal',
                'ean',
                'descripcion',
                'precio',
                'inventario',
                'num_familia'
            ]);

        $productosNormalesProcesados = $productosNormales->map(function($producto) use ($apartadosPorProducto, $termino) {
            // Obtener cantidad apartada para este producto
            $stockApartado = $apartadosPorProducto[$producto->id_catalogo_general] ?? 0;
            $stockDisponible = $producto->inventario - $stockApartado;

            $sustancias = '';
            $esMedicamento = false;
            
            $sustanciasEncontradas = DB::connection('sqlsrvM')
                ->table('catalogo_maestro')
                ->join('cat_sales_presentacion', 'catalogo_maestro.sales_presentacion', '=', 'cat_sales_presentacion.id')
                ->where('catalogo_maestro.EAN', $producto->ean)
                ->whereNotNull('catalogo_maestro.sales_presentacion')
                ->where('catalogo_maestro.sales_presentacion', '>', 0)
                ->pluck('cat_sales_presentacion.sustancia')
                ->toArray();

            if (!empty($sustanciasEncontradas)) {
                $esMedicamento = true;
                $sustancias = implode(' / ', $sustanciasEncontradas);
                
                if (!empty($termino)) {
                    $sustanciaCoincidente = '';
                    foreach ($sustanciasEncontradas as $sustancia) {
                        if (stripos($sustancia, $termino) !== false) {
                            $componentes = explode('/', $sustancia);
                            foreach ($componentes as $componente) {
                                if (stripos(trim($componente), $termino) !== false) {
                                    $sustanciaCoincidente = strtoupper(trim($componente));
                                    break;
                                }
                            }
                            if ($sustanciaCoincidente) break;
                        }
                    }
                    if ($sustanciaCoincidente) {
                        $sustancias = $sustanciaCoincidente;
                    }
                }
            } else {
                $sustancias = 'No es medicamento';
            }

            return [
                'id' => $producto->id_catalogo_general,  // Se mantiene pero no se usará al guardar
                'id_sucursal' => $producto->id_sucursal,
                'nombre_sucursal' => $producto->sucursal->nombre ?? 'N/A',
                'codbar' => $producto->ean,
                'nombre' => $producto->descripcion,
                'precio' => floatval($producto->precio),
                'inventario' => max(0, $stockDisponible),
                'inventario_original' => $producto->inventario,
                'apartado' => $stockApartado,
                'num_familia' => $producto->num_familia,
                'sustancias_activas' => $sustancias,
                'es_medicamento' => $esMedicamento,
                'es_externo' => 0,
            ];
        })->filter(function($producto) {
            return $producto['inventario'] > 0 || $producto['apartado'] > 0;
        })->values();

        $todosLosProductos = $productosNormalesProcesados;

        // ============================================
        // 3. BUSCAR EN TMP_CATALOGO (productos externos)
        // ============================================
        $queryExternos = TmpCatalogo::where('activo', 1);
        
        if (!empty($termino)) {
            $queryExternos->where(function($query) use ($termino) {
                $query->whereRaw("descripcion COLLATE SQL_Latin1_General_CP1_CI_AI LIKE ?", ["%{$termino}%"])
                    ->orWhere('ean', 'LIKE', "%{$termino}%");
            });
        }
        
        $productosExternos = $queryExternos
            ->limit(5)
            ->get()
            ->map(function($producto) {
                return [
                    'id' => $producto->id_tmp,  // Se mantiene pero no se usará al guardar
                    'id_sucursal' => null,
                    'nombre_sucursal' => 'Pedido a Proveedor',
                    'codbar' => $producto->ean,
                    'nombre' => $producto->descripcion,
                    'precio' => floatval($producto->precio),
                    'inventario' => 0,
                    'inventario_original' => 0,
                    'apartado' => 0,
                    'num_familia' => 'EXT',
                    'sustancias_activas' => '',
                    'es_medicamento' => false,
                    'es_externo' => 1,
                ];
            });
        
        $todosLosProductos = $todosLosProductos->concat($productosExternos);

        // Ordenar por relevancia
        $terminoLower = strtolower($termino);
        $terminoNormalizado = $this->normalizarTexto($terminoLower);

        $productosOrdenados = $todosLosProductos->sortByDesc(function($producto) use ($terminoLower, $terminoNormalizado) {
            $score = 0;
            $nombreLower = strtolower($producto['nombre']);
            $nombreNormalizado = $this->normalizarTexto($nombreLower);
            $sustanciasLower = strtolower($producto['sustancias_activas']);
            $sustanciasNormalizado = $this->normalizarTexto($sustanciasLower);
            
            if ($nombreLower === $terminoLower) {
                $score = 100;
            } elseif ($nombreNormalizado === $terminoNormalizado) {
                $score = 95;
            } elseif (str_starts_with($nombreNormalizado, $terminoNormalizado)) {
                $score = 80;
            } elseif (str_contains($sustanciasNormalizado, $terminoNormalizado)) {
                $score = 70;
            } elseif (str_contains($nombreNormalizado, $terminoNormalizado)) {
                $score = 50;
            }
            
            if ($producto['es_externo'] == 1) {
                $score = $score - 10;
            }
            
            return $score;
        })->values();

        return response()->json([
            'success' => true,
            'data' => $productosOrdenados
        ]);
    }

    /**
     * Normalizar texto quitando acentos
     */
    private function normalizarTexto(string $texto): string
    {
        $texto = mb_strtolower($texto, 'UTF-8');
        
        $tabla = array(
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
            'ñ' => 'n', 'ç' => 'c'
        );
        
        return strtr($texto, $tabla);
    }
    
    public function catalogos(): JsonResponse
    {
        try {
            $fases = CatFase::where('activo', 1)->get(['id_fase', 'fase']);
            $clasificaciones = CatClasificacion::where('activo', 1)->get(['id_clasificacion', 'clasificacion']);
            $sucursales = Sucursal::where('activo', 1)->get(['id_sucursal', 'nombre']);
            
            // Buscar el ID de la fase "En proceso"
            $faseEnProceso = $fases->firstWhere('fase', 'En proceso');
            $faseEnProcesoId = $faseEnProceso ? $faseEnProceso->id_fase : null;
            
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
                    'fase_en_proceso_id' => $faseEnProcesoId, // Enviamos el ID
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
            // Log de todos los datos recibidos
            \Log::info('=== INICIO STORE COTIZACIÓN ===');
            \Log::info('Datos completos recibidos:', $request->all());

            $validated = $request->validate([
                'id_cliente' => 'required|exists:sqlsrvM.catalogo_cliente_maestro,id_Cliente',
                'id_fase' => 'required|exists:cat_fases,id_fase',
                'id_clasificacion' => 'nullable|exists:cat_clasificaciones,id_clasificacion',
                'id_sucursal_asignada' => 'nullable|exists:sqlsrvM.sucursales,id_sucursal',
                'certeza' => 'nullable|integer|in:1,2,3',
                'comentarios' => 'nullable|string|max:500',
                'articulos' => 'required|array|min:1',
                'articulos.*.codbar' => 'required|string|max:20',  // Usar codbar en lugar de id_producto
                'articulos.*.cantidad' => 'required|integer|min:1',
                'articulos.*.precio_unitario' => 'required|numeric|min:0',
                'articulos.*.descuento' => 'nullable|numeric|min:0|max:100',
                'articulos.*.id_convenio' => 'nullable|exists:sqlsrvM.cat_convenios,id_convenio',
                'articulos.*.es_externo' => 'nullable|in:0,1',
            ]);

            // Log de artículos validados
            \Log::info('Artículos validados:', $validated['articulos']);

            DB::beginTransaction();

            $importeTotal = 0;
            $articulosData = [];
            $stockDisponible = true;
            $sucursalAsignadaId = $validated['id_sucursal_asignada'] ?? null;

            foreach ($validated['articulos'] as $index => $articulo) {
                $descuento = $articulo['descuento'] ?? 0;
                $importe = $articulo['cantidad'] * $articulo['precio_unitario'] * (1 - $descuento / 100);
                $importeTotal += $importe;
                
                // Determinar tipo de producto
                $es_externo = $articulo['es_externo'] ?? 0;
                
                \Log::info("Procesando artículo {$index}:", [
                    'codbar' => $articulo['codbar'],
                    'es_externo' => $es_externo,
                    'cantidad' => $articulo['cantidad'],
                    'precio_unitario' => $articulo['precio_unitario']
                ]);
                
                if ($es_externo == 1) {
                    // ============================================
                    // PRODUCTO EXTERNO - Buscar en tmp_catalogo
                    // ============================================
                    $productoExterno = TmpCatalogo::where('ean', $articulo['codbar'])->first();
                    
                    if (!$productoExterno) {
                        \Log::error("Producto externo NO encontrado con codbar: " . $articulo['codbar']);
                        throw new \Exception('Producto externo no encontrado: ' . $articulo['codbar']);
                    }
                    
                    $articulosData[] = [
                        'codbar' => $productoExterno->ean,
                        'cantidad' => $articulo['cantidad'],
                        'precio_unitario' => $articulo['precio_unitario'],
                        'descuento' => $descuento,
                        'importe' => $importe,
                        'id_convenio' => $articulo['id_convenio'] ?? null,
                        'es_externo' => 1,
                    ];
                } else {
                    // ============================================
                    // PRODUCTO NORMAL - Buscar en catalogo_general
                    // ============================================
                    $producto = CatalogoGeneral::where('ean', $articulo['codbar'])->first();
                    
                    if (!$producto) {
                        \Log::error("Producto normal NO encontrado con codbar: " . $articulo['codbar']);
                        throw new \Exception('Producto no encontrado: ' . $articulo['codbar']);
                    }
                    
                    if ($sucursalAsignadaId && isset($articulo['id_sucursal_surtido']) && $articulo['id_sucursal_surtido'] == $sucursalAsignadaId) {
                        if ($producto->inventario < $articulo['cantidad']) {
                            $stockDisponible = false;
                            \Log::info("Stock insuficiente para producto {$index}: disponible {$producto->inventario}, solicitado {$articulo['cantidad']}");
                        }
                    }
                    
                    $articulosData[] = [
                        'codbar' => $producto->ean,
                        'cantidad' => $articulo['cantidad'],
                        'precio_unitario' => $articulo['precio_unitario'],
                        'descuento' => $descuento,
                        'importe' => $importe,
                        'id_convenio' => $articulo['id_convenio'] ?? null,
                        'es_externo' => 0,
                    ];
                }
            }

            // Log de artículosData antes de guardar
            \Log::info('Artículos a guardar en crm_cotizaciones_detalle:', $articulosData);

            $certeza = $validated['certeza'] ?? 0;
            $apartado = ($certeza == 3) ? 1 : 0;

            $cotizacion = Cotizacion::create([
                'id_cliente' => $validated['id_cliente'],
                'id_fase' => $validated['id_fase'],
                'id_clasificacion' => $validated['id_clasificacion'] ?? null,
                'id_sucursal_asignada' => $validated['id_sucursal_asignada'] ?? null,
                'certeza' => $certeza,
                'importe_total' => $importeTotal,
                'comentarios' => $validated['comentarios'],
                'fecha_entrega_sugerida' => null,
                'activo' => 1,
                'enviado' => 0,
                'version' => 1,
                'creado_por' => auth()->id(),
                'modificado_por' => auth()->id(),
            ]);

            \Log::info('Cotización creada con ID: ' . $cotizacion->id_cotizacion);

            foreach ($articulosData as $detalle) {
                try {
                    CotizacionDetalle::create(array_merge($detalle, [
                        'id_cotizacion' => $cotizacion->id_cotizacion,
                        'apartado' => $apartado,
                        'fecha_actualizacion' => now(),
                        'activo' => 1
                    ]));
                } catch (\Exception $e) {
                    \Log::error('ERROR AL GUARDAR DETALLE: ' . $e->getMessage());
                    throw $e;
                }
            }

            DB::commit();

            \Log::info('=== FIN STORE COTIZACIÓN EXITOSA ===');

            return response()->json([
                'success' => true,
                'message' => 'Cotización creada correctamente',
                'data' => $cotizacion->load('detalles', 'cliente', 'fase')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('=== ERROR EN STORE COTIZACIÓN ===');
            \Log::error('Error: ' . $e->getMessage());
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
            'detalles.convenio', 'detalles.sucursalSurtido', 'creador', 'modificador'
        ])->findOrFail($id);
        
        foreach ($cotizacion->detalles as $detalle) {
            // Asegurar que es_externo esté presente
            if (!isset($detalle->es_externo) || empty($detalle->es_externo)) {
                // Detectar por código de barras (EAN que empieza con 'T')
                if ($detalle->codbar && str_starts_with($detalle->codbar, 'T')) {
                    $detalle->es_externo = 1;
                } else {
                    $detalle->es_externo = 0;
                }
            }
            
            // ============================================
            // CARGAR PRODUCTO USANDO CODBAR (EAN)
            // ============================================
            if ($detalle->es_externo == 1) {
                // Producto externo - buscar en tmp_catalogo por codbar
                $detalle->producto = TmpCatalogo::where('ean', $detalle->codbar)->first();
                $detalle->es_externo = true;
                
                if (!$detalle->producto) {
                    \Log::warning("Producto externo no encontrado con codbar: {$detalle->codbar}");
                }
            } else {
                // Producto normal - buscar en catalogo_general por codbar (EAN)
                $detalle->producto = CatalogoGeneral::where('ean', $detalle->codbar)
                    ->where('activo', 1)
                    ->first();
                $detalle->es_externo = false;
                
                if (!$detalle->producto) {
                    \Log::warning("Producto normal no encontrado con codbar: {$detalle->codbar}");
                }
            }
            
            // Opcional: asignar nombre de producto para la vista
            if ($detalle->producto) {
                $detalle->nombre_producto = $detalle->producto->descripcion;
            } else {
                $detalle->nombre_producto = 'Producto no disponible';
            }
        }
        
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
            'id_sucursal_asignada' => 'nullable|exists:sqlsrvM.sucursales,id_sucursal',
            'certeza' => 'nullable|integer|in:1,2,3',
            'comentarios' => 'nullable|string|max:500',
            'articulos' => 'required|array|min:1',
            'articulos.*.codbar' => 'required|string|max:20',  // CAMBIO: usar codbar
            'articulos.*.cantidad' => 'required|integer|min:1',
            'articulos.*.precio_unitario' => 'required|numeric|min:0',
            'articulos.*.descuento' => 'nullable|numeric|min:0|max:100',
            'articulos.*.id_convenio' => 'nullable|exists:sqlsrvM.cat_convenios,id_convenio',
            'articulos.*.es_externo' => 'nullable|in:0,1',
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
        
        // Cargar la cotización con los detalles necesarios
        $cotizacionOriginal = Cotizacion::with([
            'detalles.sucursalSurtido', 
            'cliente'
        ])->findOrFail($id);
        
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
            'id_convenio_general' => $cotizacionOriginal->id_convenio_general,
            'articulos' => $cotizacionOriginal->detalles->map(function($detalle) {
                // Determinar el tipo de producto
                $esExterno = isset($detalle->es_externo) ? (int)$detalle->es_externo : 0;

                // Si no tiene es_externo pero el código empieza con T, es externo
                if ($esExterno == 0 && $detalle->codbar && str_starts_with($detalle->codbar, 'T')) {
                    $esExterno = 1;
                }

                if ($esExterno == 1) {
                    // ============================================
                    // PRODUCTO EXTERNO - Buscar en tmp_catalogo por codbar
                    // ============================================
                    $productoExterno = TmpCatalogo::where('ean', $detalle->codbar)->first();
                    
                    if ($productoExterno) {
                        $nombre = $productoExterno->descripcion;
                        $codbar = $productoExterno->ean;
                        $precio = $productoExterno->precio;
                    } else {
                        // Fallback a los datos del detalle
                        $nombre = 'Producto externo';
                        $codbar = $detalle->codbar;
                        $precio = $detalle->precio_unitario;
                    }
                    
                    return [
                        'codbar' => $codbar,
                        'nombre' => $nombre,
                        'precio' => $precio,
                        'cantidad' => $detalle->cantidad,
                        'descuento' => $detalle->descuento,
                        'id_convenio' => $detalle->id_convenio,
                        'num_familia' => 'EXT',
                        'inventario_disponible' => 999,
                        'nombre_sucursal_surtido' => $detalle->sucursalSurtido->nombre ?? 'Pedido a Proveedor',
                        'es_externo' => 1,
                    ];
                } else {
                    // ============================================
                    // PRODUCTO NORMAL - Buscar en catalogo_general por codbar
                    // ============================================
                    $producto = CatalogoGeneral::where('ean', $detalle->codbar)
                        ->where('activo', 1)
                        ->first();
                    
                    if (!$producto) {
                        $producto = CatalogoGeneral::find($detalle->id_producto);
                    }
                    
                    return [
                        'codbar' => $detalle->codbar,
                        'nombre' => $producto->descripcion ?? $detalle->descripcion,
                        'precio' => $detalle->precio_unitario,
                        'cantidad' => $detalle->cantidad,
                        'descuento' => $detalle->descuento,
                        'id_convenio' => $detalle->id_convenio,
                        'num_familia' => $producto->num_familia ?? '',
                        'inventario_disponible' => $producto->inventario ?? 0,
                        'nombre_sucursal_surtido' => $detalle->sucursalSurtido->nombre ?? ($producto->sucursal->nombre ?? 'No asignada'),
                        'es_externo' => 0,
                    ];
                }
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
                'id_cliente' => 'required|exists:sqlsrvM.catalogo_cliente_maestro,id_Cliente',
                'id_fase' => 'required|exists:cat_fases,id_fase',
                'id_clasificacion' => 'nullable|exists:cat_clasificaciones,id_clasificacion',
                'id_sucursal_asignada' => 'nullable|exists:sqlsrvM.sucursales,id_sucursal',
                'certeza' => 'nullable|integer|in:1,2,3',
                'comentarios' => 'nullable|string|max:500',
                'articulos' => 'required|array|min:1',
                'articulos.*.codbar' => 'required|string|max:20',  // Usar codbar
                'articulos.*.cantidad' => 'required|integer|min:1',
                'articulos.*.precio_unitario' => 'required|numeric|min:0',
                'articulos.*.descuento' => 'nullable|numeric|min:0|max:100',
                'articulos.*.id_convenio' => 'nullable|exists:sqlsrvM.cat_convenios,id_convenio',
                'articulos.*.es_externo' => 'nullable|in:0,1',
            ]);
            
            // Desactivar la cotización original
            $cotizacionOriginal->activo = 0;
            $cotizacionOriginal->save();
            
            $importeTotal = 0;
            $articulosData = [];
            $stockDisponible = true;
            $sucursalAsignadaId = $validated['id_sucursal_asignada'] ?? null;
            
            foreach ($validated['articulos'] as $index => $articulo) {
                $descuento = $articulo['descuento'] ?? 0;
                $importe = $articulo['cantidad'] * $articulo['precio_unitario'] * (1 - $descuento / 100);
                $importeTotal += $importe;
                
                // Determinar tipo de producto
                $es_externo = $articulo['es_externo'] ?? 0;
                $codbar = $articulo['codbar'];
                
                \Log::info("Procesando artículo {$index} en nueva versión:", [
                    'codbar' => $codbar,
                    'es_externo' => $es_externo,
                    'cantidad' => $articulo['cantidad'],
                    'precio_unitario' => $articulo['precio_unitario']
                ]);
                
                if ($es_externo == 1) {
                    // ============================================
                    // PRODUCTO EXTERNO - Buscar en tmp_catalogo por codbar
                    // ============================================
                    $productoExterno = TmpCatalogo::where('ean', $codbar)->first();
                    
                    if (!$productoExterno) {
                        \Log::error("Producto externo NO encontrado con codbar: {$codbar}");
                        throw new \Exception('Producto externo no encontrado: ' . $codbar);
                    }
                    
                    $articulosData[] = [
                        'codbar' => $productoExterno->ean,
                        'cantidad' => $articulo['cantidad'],
                        'precio_unitario' => $articulo['precio_unitario'],
                        'descuento' => $descuento,
                        'importe' => $importe,
                        'id_convenio' => $articulo['id_convenio'] ?? null,
                        'es_externo' => 1,
                    ];
                } else {
                    // ============================================
                    // PRODUCTO NORMAL - Buscar en catalogo_general por codbar
                    // ============================================
                    $producto = CatalogoGeneral::where('ean', $codbar)
                        ->where('activo', 1)
                        ->first();
                        
                    if (!$producto) {
                        if (!$producto) {
                            \Log::error("Producto normal NO encontrado con codbar: {$codbar}");
                            throw new \Exception('Producto no encontrado: ' . $codbar);
                        }
                    }
                    
                    if ($sucursalAsignadaId && isset($articulo['id_sucursal_surtido']) && $articulo['id_sucursal_surtido'] == $sucursalAsignadaId) {
                        if ($producto->inventario < $articulo['cantidad']) {
                            $stockDisponible = false;
                        }
                    }
                    
                    $articulosData[] = [
                        'codbar' => $producto->ean,
                        'cantidad' => $articulo['cantidad'],
                        'precio_unitario' => $articulo['precio_unitario'],
                        'descuento' => $descuento,
                        'importe' => $importe,
                        'id_convenio' => $articulo['id_convenio'] ?? null,
                        'es_externo' => 0,
                    ];
                }
            }
            
            // Log de artículos a guardar
            \Log::info('=== ARTICULOS A GUARDAR EN NUEVA VERSION ===');
            foreach ($articulosData as $idx => $data) {
                \Log::info("Artículo {$idx}:", [
                    'codbar' => $data['codbar'],
                    'es_externo' => $data['es_externo']
                ]);
            }
            
            $certeza = $validated['certeza'] ?? 0;
            $apartado = ($certeza == 3) ? 1 : 0;
            
            // Obtener ID de fase "En proceso" para la nueva versión
            $faseEnProceso = CatFase::where('fase', 'En proceso')->first();
            $faseEnProcesoId = $faseEnProceso ? $faseEnProceso->id_fase : 1;
            
            $nuevaCotizacion = Cotizacion::create([
                'folio' => Cotizacion::generarFolio(),
                'id_cliente' => $validated['id_cliente'],
                'id_fase' => $faseEnProcesoId,
                'id_clasificacion' => $validated['id_clasificacion'] ?? null,
                'id_sucursal_asignada' => $validated['id_sucursal_asignada'] ?? null,
                'certeza' => $certeza,
                'importe_total' => $importeTotal,
                'comentarios' => $validated['comentarios'],
                'fecha_entrega_sugerida' => null,
                'activo' => 1,
                'enviado' => 0,
                'version' => $cotizacionOriginal->version + 1,
                'cotizacion_origen_id' => $cotizacionOriginal->id_cotizacion,
                'creado_por' => auth()->id(),
                'modificado_por' => auth()->id(),
            ]);
            
            foreach ($articulosData as $detalle) {
                CotizacionDetalle::create(array_merge($detalle, [
                    'id_cotizacion' => $nuevaCotizacion->id_cotizacion,
                    'apartado' => $apartado,
                    'fecha_actualizacion' => now(),
                    'activo' => 1
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
                'id_cliente' => 'required|exists:sqlsrvM.catalogo_cliente_maestro,id_Cliente',
                'id_fase' => 'required|exists:cat_fases,id_fase',
                'id_clasificacion' => 'nullable|exists:cat_clasificaciones,id_clasificacion',
                'id_sucursal_asignada' => 'nullable|exists:sqlsrvM.sucursales,id_sucursal',
                'certeza' => 'nullable|integer|in:1,2,3',
                'comentarios' => 'nullable|string|max:500',
                'articulos' => 'required|array|min:1',
                'articulos.*.codbar' => 'required|string|max:20',
                'articulos.*.cantidad' => 'required|integer|min:1',
                'articulos.*.precio_unitario' => 'required|numeric|min:0',
                'articulos.*.descuento' => 'nullable|numeric|min:0|max:100',
                'articulos.*.id_convenio' => 'nullable|exists:sqlsrvM.cat_convenios,id_convenio',
                'articulos.*.es_externo' => 'nullable|in:0,1',
            ]);

            DB::beginTransaction();

            $importeTotal = 0;
            $articulosData = [];
            $stockDisponible = true;
            $sucursalAsignadaId = $validated['id_sucursal_asignada'] ?? null;

            foreach ($validated['articulos'] as $index => $articulo) {
                $descuento = $articulo['descuento'] ?? 0;
                $importe = $articulo['cantidad'] * $articulo['precio_unitario'] * (1 - $descuento / 100);
                $importeTotal += $importe;
                
                // Determinar tipo de producto
                $es_externo = $articulo['es_externo'] ?? 0;
                
                \Log::info("Procesando artículo {$index} en nueva cotización sin versión:", [
                    'codbar' => $articulo['codbar'],
                    'es_externo' => $es_externo,
                    'cantidad' => $articulo['cantidad'],
                    'precio_unitario' => $articulo['precio_unitario']
                ]);
                
                if ($es_externo == 1) {
                    // ============================================
                    // PRODUCTO EXTERNO - Buscar en tmp_catalogo
                    // ============================================
                    $productoExterno = TmpCatalogo::where('ean', $articulo['codbar'])->first();    
                    if (!$productoExterno) {
                        throw new \Exception('Producto externo no encontrado: ' . $articulo['id_producto']);
                    }
                    
                    $articulosData[] = [
                        'codbar' => $productoExterno->ean,
                        'cantidad' => $articulo['cantidad'],
                        'precio_unitario' => $articulo['precio_unitario'],
                        'descuento' => $descuento,
                        'importe' => $importe,
                        'id_convenio' => $articulo['id_convenio'] ?? null,
                        'es_externo' => 1,
                    ];
                } else {
                    // ============================================
                    // PRODUCTO NORMAL - Buscar en catalogo_general
                    // ============================================
                     $codbar = $articulo['codbar'] ?? null;
                    if (!$codbar) {
                        throw new \Exception('El producto normal debe tener código de barras');
                    }
                    
                    $producto = CatalogoGeneral::where('ean', $codbar)
                        ->where('activo', 1)
                        ->first();
                    
                    if (!$producto) {
                        throw new \Exception('Producto no encontrado con código: ' . $codbar);
                    }
                    
                    $articulosData[] = [
                        'codbar' => $producto->ean,
                        'cantidad' => $articulo['cantidad'],
                        'precio_unitario' => $articulo['precio_unitario'],
                        'descuento' => $descuento,
                        'importe' => $importe,
                        'id_convenio' => $articulo['id_convenio'] ?? null,
                        'es_externo' => 0,
                    ];
                }
            }

            // Log de artículosData antes de guardar
            \Log::info('Artículos a guardar en nueva cotización sin versión:', $articulosData);

            $certeza = $validated['certeza'] ?? 0;
            $apartado = ($certeza == 3) ? 1 : 0;

            $nuevaCotizacion = Cotizacion::create([
                'folio' => Cotizacion::generarFolio(),
                'id_cliente' => $validated['id_cliente'],
                'id_fase' => $validated['id_fase'],
                'id_clasificacion' => $validated['id_clasificacion'] ?? null,
                'id_sucursal_asignada' => $validated['id_sucursal_asignada'] ?? null,
                'certeza' => $certeza,
                'importe_total' => $importeTotal,
                'comentarios' => $validated['comentarios'],
                'fecha_entrega_sugerida' => null,
                'activo' => 1,
                'enviado' => 0,
                'version' => 1,
                'cotizacion_origen_id' => null,
                'creado_por' => auth()->id(),
                'modificado_por' => auth()->id(),
            ]);

            foreach ($articulosData as $detalle) {
                try {
                    CotizacionDetalle::create(array_merge($detalle, [
                        'id_cotizacion' => $nuevaCotizacion->id_cotizacion,
                        'apartado' => $apartado,
                        'fecha_actualizacion' => now(),
                        'activo' => 1
                    ]));
                } catch (\Exception $e) {
                    \Log::error('Error al guardar detalle en nueva cotización: ' . $e->getMessage());
                    \Log::error('Detalle: ' . json_encode($detalle));
                    throw $e;
                }
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
            Log::error('Stack trace: ' . $e->getTraceAsString());
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
            $sucursalAsignadaId = $validated['id_sucursal_asignada'] ?? null;

            \Log::info('=== ACTUALIZAR COTIZACION - INICIO ===');
            \Log::info('Artículos recibidos:', $validated['articulos']);

            foreach ($validated['articulos'] as $idx => $articulo) {
                $descuento = $articulo['descuento'] ?? 0;
                $importe = $articulo['cantidad'] * $articulo['precio_unitario'] * (1 - $descuento / 100);
                $importeTotal += $importe;
                
                // DETECTAR TIPO DE PRODUCTO
                $esExterno = isset($articulo['es_externo']) ? (int)$articulo['es_externo'] : 0;
                $codbar = $articulo['codbar'] ?? '';

                if ($esExterno == 1) {
                    // ============================================
                    // PRODUCTO EXTERNO - Buscar en tmp_catalogo por codbar
                    // ============================================
                    $productoExterno = TmpCatalogo::where('activo', 1)
                        ->where('ean', $codbar)
                        ->first();

                    if (!$productoExterno) {
                        \Log::error("Producto externo NO encontrado con codbar: {$codbar}");
                        throw new \Exception('Producto externo no encontrado. Código: ' . $codbar);
                    }
                    
                    $articulosData[] = [
                        'codbar' => $productoExterno->ean,
                        'cantidad' => $articulo['cantidad'],
                        'precio_unitario' => $articulo['precio_unitario'],
                        'descuento' => $descuento,
                        'importe' => $importe,
                        'id_convenio' => $articulo['id_convenio'] ?? null,
                        'es_externo' => 1,
                    ];
                    
                } else {
                    // ============================================
                    // PRODUCTO NORMAL - Buscar en catalogo_general por codbar
                    // ============================================
                    $producto = CatalogoGeneral::where('activo', 1)
                        ->where('ean', $codbar)
                        ->first();
                        
                    if (!$producto) {
                        \Log::error("Producto normal NO encontrado con codbar: {$codbar}");
                        throw new \Exception('Producto no encontrado. Código: ' . $codbar);
                    }

                    $articulosData[] = [
                        'codbar' => $producto->ean,
                        'cantidad' => $articulo['cantidad'],
                        'precio_unitario' => $articulo['precio_unitario'],
                        'descuento' => $descuento,
                        'importe' => $importe,
                        'id_convenio' => $articulo['id_convenio'] ?? null,
                        'es_externo' => 0,
                    ];
                }
            }

            // Log de artículos a guardar
            \Log::info('=== ARTICULOS A GUARDAR EN BD ===');
            foreach ($articulosData as $idx => $data) {
                \Log::info("Artículo {$idx}:", [
                    'codbar' => $data['codbar'],
                    'es_externo' => $data['es_externo']
                ]);
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
                'enviado' => 0,
                'modificado_por' => auth()->id(),
            ]);

            // Eliminar detalles antiguos
            $cotizacion->detalles()->delete();
            
            // Crear nuevos detalles (sin id_producto, sin descripcion)
            foreach ($articulosData as $detalle) {
                CotizacionDetalle::create(array_merge($detalle, [
                    'id_cotizacion' => $cotizacion->id_cotizacion,
                    'apartado' => $apartado,
                    'fecha_actualizacion' => now(),
                    'activo' => 1,
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
            \Log::error('Error en actualizarCotizacion: ' . $e->getMessage());
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
        // Obtener códigos de barras (codbar) de los productos actuales
        $productosActuales = $cotizacion->detalles->pluck('codbar')->toArray();
        
        // Obtener códigos de barras de los nuevos artículos
        $productosNuevos = collect($nuevosArticulos)->pluck('codbar')->toArray();

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
        $ean = $request->input('ean');
        $cotizacionId = $request->input('cotizacion_id', null);
        
        // Si no viene EAN, intentar buscar por producto_id (para compatibilidad)
        if (empty($ean)) {
            $productoId = $request->input('producto_id');
            if ($productoId) {
                $productoOriginal = CatalogoGeneral::find($productoId);
                if ($productoOriginal) {
                    $ean = $productoOriginal->ean;
                }
            }
        }
        
        if (empty($ean)) {
            return response()->json(['success' => true, 'data' => []]);
        }
        
        // Buscar el producto en la sucursal específica por EAN
        $producto = CatalogoGeneral::with('sucursal')
            ->where('id_sucursal', $sucursalId)
            ->where('ean', $ean)
            ->where('activo', 1)
            ->first();
        
        if (!$producto) {
            return response()->json(['success' => true, 'data' => []]);
        }
        
        // Obtener cantidad apartada para este producto específico
        $stockApartado = DB::table('crm_cotizaciones_detalle as cd')
            ->join('crm_cotizaciones as c', 'cd.id_cotizacion', '=', 'c.id_cotizacion')
            ->where('cd.apartado', 1)
            ->where('c.activo', 1)
            ->where('c.es_pedido', '!=', 1)
            ->where('c.certeza', 3)
            ->where('cd.codbar', $ean);  // Usar codbar en lugar de id_producto
        
        if ($cotizacionId) {
            $stockApartado->where('c.id_cotizacion', '!=', $cotizacionId);
        }
        
        $cantidadApartada = $stockApartado->sum('cd.cantidad');
        $stockDisponible = max(0, $producto->inventario - $cantidadApartada);
        
        $resultado = [
            'id' => $producto->id_catalogo_general,
            'codbar' => $producto->ean,
            'nombre' => $producto->descripcion,
            'precio' => floatval($producto->precio),
            'inventario' => $stockDisponible,
            'inventario_original' => $producto->inventario,
            'apartado' => $cantidadApartada,
            'num_familia' => $producto->num_familia,
            'nombre_sucursal' => $producto->sucursal->nombre ?? 'Sin sucursal'
        ];
        
        return response()->json([
            'success' => true,
            'data' => [$resultado]
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
                $anterior = Cotizacion::with(['detalles', 'detalles.convenio', 'detalles.sucursalSurtido'])
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
                            // Obtener descripción actual del producto por codbar
                            $descripcionActual = $this->obtenerDescripcionProductoPorCodbar($detalle->codbar, $detalle->es_externo);
                            
                            return [
                                'codbar' => $detalle->codbar,
                                'descripcion' => $descripcionActual,  // Descripción actual desde catálogo
                                'cantidad' => $detalle->cantidad,
                                'precio_unitario' => $detalle->precio_unitario,
                                'descuento' => $detalle->descuento,
                                'importe' => $detalle->importe,
                                'es_externo' => $detalle->es_externo,
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

    /**
     * Obtener descripción actual de un producto por su código de barras (EAN)
     */
    private function obtenerDescripcionProductoPorCodbar(string $codbar, int $esExterno): string
    {
        if ($esExterno == 1) {
            $producto = TmpCatalogo::where('ean', $codbar)->first();
            return $producto->descripcion ?? 'Producto externo no disponible';
        } else {
            $producto = CatalogoGeneral::where('ean', $codbar)->where('activo', 1)->first();
            return $producto->descripcion ?? 'Producto no disponible';
        }
    }

    public function guardarProductoExterno(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'descripcion' => 'required|string|max:255',
                'precio' => 'required|numeric|min:0',
            ]);

            $producto = TmpCatalogo::create([
                'ean' => TmpCatalogo::generarEan(),
                'descripcion' => $validated['descripcion'],
                'precio' => $validated['precio'],
                'creado_por' => auth()->id(),
                'fecha_creacion' => now(),
                'activo' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Producto externo guardado correctamente',
                'data' => [
                    'id' => $producto->id_tmp,
                    'ean' => $producto->ean,
                    'descripcion' => $producto->descripcion,
                    'precio' => $producto->precio,
                    'es_externo' => 1,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar producto externo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convertir cotización a pedido.
     */
    public function generarPedido(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $cotizacion = Cotizacion::with(['detalles', 'cliente'])->findOrFail($id);
            
            // Validar que se pueda generar pedido
            if (!$cotizacion->enviado) {
                return response()->json(['success' => false, 'message' => 'La cotización no ha sido enviada al cliente'], 400);
            }
            
            if ($cotizacion->fase_nombre !== 'Completada') {
                return response()->json(['success' => false, 'message' => 'La cotización no está completada'], 400);
            }
            
            if ($cotizacion->es_pedido) {
                return response()->json(['success' => false, 'message' => 'Esta cotización ya es un pedido'], 400);
            }
            
            // ============================================
            // CALCULAR FECHA Y HORA DE ENTREGA SUGERIDA
            // ============================================
            $fechaCreacion = now();
            $horaCreacion = (int) $fechaCreacion->format('H');
            $esAntesDe12 = $horaCreacion < 12;
            
            $hayProductosExternos = false;
            $hayStockInsuficiente = false;
            $sucursalAsignadaId = $cotizacion->id_sucursal_asignada;
            
            foreach ($cotizacion->detalles as $detalle) {
                if ($detalle->es_externo == 1) {
                    $hayProductosExternos = true;
                } else {
                    // Verificar stock por CODIGO DE BARRAS (EAN) en sucursal asignada (si existe)
                    if ($sucursalAsignadaId && $detalle->codbar) {
                        $producto = CatalogoGeneral::where('ean', $detalle->codbar)
                            ->where('id_sucursal', $sucursalAsignadaId)
                            ->where('activo', 1)
                            ->first();
                        if ($producto && $producto->inventario < $detalle->cantidad) {
                            $hayStockInsuficiente = true;
                        }
                    } else {
                        // Si no hay sucursal asignada, buscar en cualquier sucursal (pero se sumará inventario)
                        $producto = CatalogoGeneral::where('ean', $detalle->codbar)
                            ->where('activo', 1)
                            ->first();
                        if ($producto && $producto->inventario < $detalle->cantidad) {
                            $hayStockInsuficiente = true;
                        }
                    }
                }
            }
            
            $fechaEntrega = $fechaCreacion->copy();
            $horaEntrega = '12:00:00';
            
            if ($hayProductosExternos) {
                // Productos sobre pedido: 2 días hábiles
                $fechaEntrega = self::sumarDiasHabiles($fechaCreacion, 2);
                $horaEntrega = '14:00:00';
            } elseif ($hayStockInsuficiente) {
                // Sin stock en sucursal asignada: día siguiente
                $fechaEntrega = self::sumarDiasHabiles($fechaCreacion, 1);
                $horaEntrega = $esAntesDe12 ? '12:00:00' : '16:00:00';
            } else {
                // Hay stock: mismo día solo si es antes de las 12, si es después, día siguiente
                if ($esAntesDe12) {
                    $fechaEntrega = $fechaCreacion->copy();
                    $horaEntrega = '14:00:00';
                } else {
                    // Después de las 12, entregar al día siguiente
                    $fechaEntrega = self::sumarDiasHabiles($fechaCreacion, 1);
                    $horaEntrega = '12:00:00';
                }
            }
            
            // Generar folio del pedido
            $folioPedido = $this->generarFolioPedido();
            
            // Crear registro en orden_pedido
            $pedido = OrdenPedido::create([
                'id_cotizacion' => $cotizacion->id_cotizacion,
                'folio_pedido' => $folioPedido,
                'status' => 2, // En proceso
                'fecha_pedido' => now(),
                'fecha_entrega_sugerida' => $fechaEntrega->toDateString(),
                'hora_entrega_sugerida' => $horaEntrega,
                'creado_por' => auth()->id(),
                'created_at' => now(),
            ]);
            
            // ============================================
            // CREAR DETALLES DEL PEDIDO (solo copiar codbar)
            // ============================================
            foreach ($cotizacion->detalles as $detalle) {
                OrdenPedidoDetalle::create([
                    'id_pedido' => $pedido->id_pedido,
                    'id_cotizacion_detalle' => $detalle->id_cotizacion_detalle,
                    'ean' => $detalle->codbar,  // Solo el código de barras
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'descuento' => $detalle->descuento,
                    'importe' => $detalle->importe,
                    'es_externo' => $detalle->es_externo,
                    'id_sucursal_surtido' => null,  // Se asignará después en la gestión del pedido
                    //'id_producto' => null,  // Ya no se usa
                ]);
            }
            
            // Marcar cotización como pedido
            $cotizacion->update([
                'es_pedido' => true,
                'modificado_por' => auth()->id(),
            ]);
            
            // ============================================
            // ACTUALIZAR CLIENTE DE PROSPECTO A CLIENTE
            // ============================================
            $cliente = $cotizacion->cliente;
            if ($cliente && $cliente->status === 'PROSPECTO') {
                $cliente->update(['status' => 'CLIENTE']);
                
                \Log::info('Cliente convertido de PROSPECTO a CLIENTE', [
                    'cliente_id' => $cliente->id_Cliente,
                    'cotizacion_id' => $cotizacion->id_cotizacion
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pedido generado correctamente. ' . ($cliente && $cliente->status === 'CLIENTE' ? 'El cliente ha sido convertido a CLIENTE.' : ''),
                'data' => [
                    'pedido_id' => $pedido->id_pedido,
                    'folio_pedido' => $folioPedido
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al generar pedido: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sumar días hábiles a una fecha (lunes a viernes)
     */
    private static function sumarDiasHabiles(Carbon $fecha, int $dias): Carbon
    {
        $nuevaFecha = $fecha->copy();
        $diasAgregados = 0;
        
        while ($diasAgregados < $dias) {
            $nuevaFecha->addDay();
            // Días hábiles: lunes a viernes (1-5)
            if ($nuevaFecha->dayOfWeek >= 1 && $nuevaFecha->dayOfWeek <= 5) {
                $diasAgregados++;
            }
        }
        
        return $nuevaFecha;
    }

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

    // Método para verificar y cancelar cotizaciones vencidas al acceder al listado (opcional, para verificar al cargar)
    protected function verificarYCancelarVencidas()
    {
        $diasCancelacion = Configuracion::getValor('dias_cancelacion_cotizacion');
        
        if ($diasCancelacion === null) {
            return;
        }
        
        $fechaLimite = Carbon::now()->subDays($diasCancelacion);
        $faseCancelada = CatFase::where('fase', 'Cancelada')->first();
        
        if (!$faseCancelada) {
            return;
        }
        
        Cotizacion::where('id_fase', function($query) {
                $query->select('id_fase')->from('cat_fases')->where('fase', 'En proceso');
            })
            ->where('activo', 1)
            ->where('enviado', 0)
            ->where('es_pedido', '!=', 1)
            ->where('fecha_creacion', '<', $fechaLimite)
            ->update([
                'id_fase' => $faseCancelada->id_fase,
                'comentarios' => DB::raw("CONCAT(COALESCE(comentarios, ''), '\n[AUTOMÁTICO] Cancelada por superar los {$diasCancelacion} días en estado \"En proceso\"')"),
                'modificado_por' => null,
            ]);
    }
}