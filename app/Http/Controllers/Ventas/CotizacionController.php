<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Models\Cotizaciones\Cotizacion;
use App\Models\Cotizaciones\CotizacionDetalle;
use App\Models\Cotizaciones\CatFase;
use App\Models\Cotizaciones\CatClasificacion;
use App\Models\Cotizaciones\CatConvenio;
use App\Models\Cliente;
use App\Models\Clientes\CatLocalidad;
use App\Models\Sucursal;
use App\Models\CatalogoGeneral;
use App\Models\Configuracion;
use App\Models\Pedidos\OrdenPedidoSucursal;
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
            $cotizaciones = Cotizacion::with([
                'cliente' => function($query) {
                    $query->select('id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'telefono1', 'telefono2', 'email1');
                }, 
                'fase', 
                'clasificacion', 
                'sucursalAsignada',
                'seguimientos'
            ])
            ->activas()
            ->where('es_pedido', '!=', 1)
            ->where('id_fase', '!=', 3)
            ->orderByRaw("
                CASE 
                    WHEN id_fase = 1 THEN 0  -- En proceso primero (prioridad 1)
                    WHEN id_fase = 2 THEN 1  -- Completada después (prioridad 2)
                    ELSE 2
                END, 
                fecha_creacion DESC
            ")
            ->paginate(15);
            
            // Agregar flag de notificación a cada cotización
            foreach ($cotizaciones as $cotizacion) {
                $diasSinContacto = $cotizacion->fecha_creacion ? $cotizacion->fecha_creacion->diffInDays(now()) : 0;
                $diasAlerta = Configuracion::getValor('dias_sin_contacto_alerta', 7);
                
                // Verificar si tiene seguimiento reciente
                $tieneSeguimientoReciente = $cotizacion->seguimientos()
                    ->where('hora_inicio', '>=', now()->subDays($diasAlerta))
                    ->exists();
                
                // Mostrar notificación solo si: está en proceso, ha pasado más de N días, y NO tiene seguimiento reciente
                $cotizacion->mostrarNotificacion = (
                    $cotizacion->fase_nombre === 'En proceso' && 
                    $diasSinContacto >= $diasAlerta && 
                    !$tieneSeguimientoReciente
                );
            }
        }
        
        $permisos = [
            'ver' => $puedeVer,
            'crear' => $puedeCrear,
            'editar' => auth()->user()->puede('ventas', 'cotizaciones', 'editar'),
            'eliminar' => auth()->user()->puede('ventas', 'cotizaciones', 'eliminar'),
        ];
        
        $sucursalAsignadaUsuario = auth()->user()->sucursal_asignada ?? 0;
        
        $ultimoId = Cotizacion::max('id_cotizacion') ?? 0;
        return view('ventas.cotizaciones.index', compact('cotizaciones', 'permisos', 'sucursalAsignadaUsuario', 'ultimoId'));
    }
    
    public function buscarClientes(Request $request): JsonResponse
    {
        $termino = $request->input('q', '');
        
        // Excluir clientes BLOQUEADOS e INACTIVOS (no pueden tener cotizaciones)
        $clientes = Cliente::whereIn('status', ['CLIENTE', 'PROSPECTO'])
            ->with(['patologiasAsociadas'])
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
            ->limit(5)
            ->get(['id_Cliente', 'Nombre', 'apPaterno', 'apMaterno', 'email1', 'telefono1', 'telefono2', 'titulo', 'Domicilio', 'localidad_id']);
        
        return response()->json([
            'success' => true,
            'data' => $clientes->map(function($cliente) {
                // Obtener el nombre de la localidad (si existe)
                $localidadNombre = '';
                if ($cliente->localidad_id) {
                    $localidad = CatLocalidad::find($cliente->localidad_id);
                    $localidadNombre = $localidad ? $localidad->nombre : '';
                }
                
                // ============================================
                // OBTENER INTERESES DEL CLIENTE
                // ============================================
                $interesesIds = DB::connection('sqlsrv')
                    ->table('crm_cliente_intereses')
                    ->where('id_cliente', $cliente->id_Cliente)
                    ->where('activo', 1)
                    ->pluck('id_interes')
                    ->toArray();
                
                $interesesNombres = [];
                if (!empty($interesesIds)) {
                    $interesesNombres = DB::connection('sqlsrvM')
                        ->table('crm_cat_intereses')
                        ->whereIn('id_interes', $interesesIds)
                        ->pluck('Descripcion')
                        ->toArray();
                }
                
                // ============================================
                // OBTENER PATOLOGÍAS (ENFERMEDADES) DEL CLIENTE
                // ============================================
                $patologias = DB::connection('sqlsrv')
                    ->table('crm_patologia_asociada')
                    ->where('id_cliente_maestro', $cliente->id_Cliente)
                    ->where('status', 1)
                    ->pluck('patologia')
                    ->toArray();
                
                // Construir HTML de intereses (solo si hay)
                $interesesHtml = '';
                if (!empty($interesesNombres)) {
                    $interesesHtml = implode(', ', array_slice($interesesNombres, 0, 3));
                    if (count($interesesNombres) > 3) {
                        $interesesHtml .= ' +' . (count($interesesNombres) - 3) . ' más';
                    }
                }
                
                // Construir HTML de patologías (solo si hay)
                $patologiasHtml = '';
                if (!empty($patologias)) {
                    $patologiasHtml = implode(', ', array_slice($patologias, 0, 3));
                    if (count($patologias) > 3) {
                        $patologiasHtml .= ' +' . (count($patologias) - 3) . ' más';
                    }
                }
                
                // Construir dirección completa
                $direccionCompleta = '';
                if ($cliente->Domicilio && $localidadNombre) {
                    $direccionCompleta = $cliente->Domicilio . ', ' . $localidadNombre;
                } elseif ($cliente->Domicilio) {
                    $direccionCompleta = $cliente->Domicilio;
                } elseif ($localidadNombre) {
                    $direccionCompleta = $localidadNombre;
                }
                
                // Nombre completo
                $nombreCompleto = $cliente->nombre_completo;
                
                // Contacto HTML
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
                
                return [
                    'id' => $cliente->id_Cliente,
                    'nombre_completo' => $nombreCompleto,
                    'Nombre' => $cliente->Nombre,
                    'apPaterno' => $cliente->apPaterno,
                    'apMaterno' => $cliente->apMaterno,
                    'email' => $cliente->email1,
                    'email1' => $cliente->email1,
                    'telefono1' => $cliente->telefono1,
                    'telefono2' => $cliente->telefono2,
                    'titulo' => $cliente->titulo,
                    'domicilio' => $cliente->Domicilio,
                    'localidad_id' => $cliente->localidad_id,
                    'localidad_nombre' => $localidadNombre,
                    'direccion_completa' => $direccionCompleta,
                    'intereses' => $interesesNombres,
                    'intereses_html' => $interesesHtml,
                    'patologias' => $patologias,
                    'patologias_html' => $patologiasHtml,
                ];
            })
        ]);
    }

    public function buscarProductos(Request $request): JsonResponse
    {
        $termino = $request->input('q', '');
        $cotizacionId = $request->input('cotizacion_id', null);
        
        // 1. Mínimo 3 caracteres
        if (strlen($termino) < 3) {
            return response()->json(['success' => true, 'data' => []]);
        }
        
        if ($cotizacionId && is_numeric($cotizacionId)) {
            $cotizacionId = (int) $cotizacionId;
        }
        
        // 2. Obtener productos apartados globalmente
        $apartadosPorProducto = $this->getProductosApartadosGlobal($cotizacionId);
        
        // 3. Buscar productos agrupando por EAN y limitando
        $productosAgrupados = $this->buscarProductosAgrupados($termino);
        
        // 4. Calcular stock disponible y obtener sustancias (solo para los encontrados)
        $resultados = $this->procesarResultadosConSustancias($productosAgrupados, $apartadosPorProducto, $termino);
        
        // 5. Buscar productos externos
        $productosExternos = $this->buscarProductosExternos($termino);
        
        $todosLosProductos = $resultados->concat($productosExternos);
        
        // Ordenar por relevancia (los externos van al final)
        $todosLosProductos = $this->ordenarPorRelevancia($todosLosProductos, $termino);
        
        return response()->json([
            'success' => true,
            'data' => $todosLosProductos->values()
        ]);
    }

    /**
     * Obtener productos apartados globalmente (por codbar, sin sucursal)
     */
    private function getProductosApartadosGlobal($cotizacionId): array
    {
        $query = DB::table('crm_cotizaciones_detalle as cd')
            ->join('crm_cotizaciones as c', 'cd.id_cotizacion', '=', 'c.id_cotizacion')
            ->where('cd.apartado', 1)
            ->where('c.activo', 1)
            ->where('c.es_pedido', '!=', 1)
            ->where('c.id_fase', '!=', 3)
            ->where('c.certeza', 3);
        
        if ($cotizacionId && $cotizacionId > 0) {
            $query->where('c.id_cotizacion', '!=', $cotizacionId);
        }
        
        $apartados = $query->select('cd.codbar', 'cd.cantidad')->get();
        
        $apartadosPorProducto = [];
        foreach ($apartados as $apartado) {
            $apartadosPorProducto[$apartado->codbar] = ($apartadosPorProducto[$apartado->codbar] ?? 0) + $apartado->cantidad;
        }
        
        return $apartadosPorProducto;
    }

    /**
     * Buscar productos agrupando por EAN, incluyendo sustancia en la consulta principal
     * Busca por descripción, EAN y sustancia en una sola consulta
     */
    private function buscarProductosAgrupados(string $termino): array
    {
        // Búsqueda por descripción y EAN
        $eans = DB::connection('sqlsrvM')
            ->table('catalogo_general as cg')
            ->select(DB::raw("TRIM(CAST(cg.ean as VARCHAR(50))) as ean"))
            ->where('cg.inventario', '>', 0)
            ->where(function($q) use ($termino) {
                $q->where('cg.descripcion', 'LIKE', "%{$termino}%")
                ->orWhere('cg.ean', 'LIKE', "%{$termino}%");
            })
            ->groupBy(DB::raw("TRIM(CAST(cg.ean as VARCHAR(50)))"))
            ->orderByRaw('MAX(cg.precio) DESC')
            ->limit(10)
            ->pluck('ean')
            ->toArray();
        
        // Si hay menos de 5 resultados, buscar por sustancia
        if (count($eans) < 5) {
            $eansPorSustancia = DB::connection('sqlsrvM')
                ->table('catalogo_maestro as cm')
                ->join('cat_sales_presentacion as csp', 'cm.sales_presentacion', '=', 'csp.id')
                ->where('csp.sustancia', 'LIKE', "%{$termino}%")
                ->whereNotNull('cm.sales_presentacion')
                ->where('cm.sales_presentacion', '>', 0)
                ->distinct()
                ->pluck('cm.EAN')
                ->toArray();
            
            // Agregar EANs de sustancia que no estén ya en la lista
            $eans = array_merge($eans, array_diff($eansPorSustancia, $eans));
            $eans = array_slice($eans, 0, 10);
        }
        
        if (empty($eans)) {
            return [];
        }
        
        // Obtener todos los datos de los productos encontrados
        $productos = DB::connection('sqlsrvM')
            ->table('catalogo_general as cg')
            ->select(
                DB::raw("TRIM(CAST(cg.ean as VARCHAR(50))) as ean"),
                DB::raw('MAX(cg.descripcion) as descripcion'),
                DB::raw('MAX(cg.precio) as precio'),
                DB::raw('MAX(gf.descripcionfamilia) as nombre_familia'),
                DB::raw('MAX(cg.num_familia) as num_familia'),
                DB::raw('SUM(CAST(cg.inventario as INT)) as inventario_global')
            )
            ->join('grupos_familias as gf', 'cg.num_familia', '=', 'gf.numfamilia')
            ->whereIn(DB::raw("TRIM(CAST(cg.ean as VARCHAR(50)))"), $eans)
            ->groupBy(DB::raw("TRIM(CAST(cg.ean as VARCHAR(50)))"))
            ->get()
            ->map(function($item) {
                return (array) $item;
            })
            ->keyBy('ean')
            ->toArray();
        
        return $productos;
    }

    /**
     * Procesar resultados: calcular stock y obtener sustancias (solo para estos productos)
     * También obtiene el desglose de inventario por sucursal
     */
    private function procesarResultadosConSustancias(array $productosAgrupados, array $apartadosPorProducto, string $termino): \Illuminate\Support\Collection
    {
        if (empty($productosAgrupados)) {
            return collect();
        }
        
        // Obtener EANs de los productos encontrados
        $eans = array_keys($productosAgrupados);
        
        // Obtener sustancias SOLO para estos EANs
        $sustanciasPorEan = $this->obtenerSustanciasPorEan($eans, $termino);
        
        // Obtener desglose de inventario por sucursal para estos EANs
        $detalleSucursales = $this->obtenerDetalleInventarioPorSucursal($eans);
        
        $resultados = [];
        foreach ($productosAgrupados as $ean => $producto) {
            // $producto es un array
            $stockApartadoGlobal = $apartadosPorProducto[$ean] ?? 0;
            $inventarioGlobal = $producto['inventario_global'] ?? 0;
            
            // Validación: calcular la suma del detalle
            $sumaDetalle = 0;
            if (isset($detalleSucursales[$ean])) {
                foreach ($detalleSucursales[$ean] as $sucursal) {
                    $sumaDetalle += $sucursal['inventario'];
                }
            }
            
            // Si el inventario global no coincide con la suma, usar la suma del detalle
            if ($inventarioGlobal != $sumaDetalle && $sumaDetalle > 0) {
                $inventarioGlobal = $sumaDetalle;
            }
            
            $stockDisponibleGlobal = max(0, $inventarioGlobal - $stockApartadoGlobal);
            
            // Solo mostrar si hay stock disponible (excepto si está apartado)
            if ($stockDisponibleGlobal <= 0 && $stockApartadoGlobal <= 0) {
                continue;
            }
            
            $sustanciasInfo = $sustanciasPorEan[$ean] ?? ['sustancias' => 'No es medicamento', 'es_medicamento' => false];
            
            // Formatear el desglose por sucursal
            $detalleSucursalStr = '';
            if (isset($detalleSucursales[$ean]) && !empty($detalleSucursales[$ean])) {
                $partes = [];
                foreach ($detalleSucursales[$ean] as $sucursal) {
                    $partes[] = "{$sucursal['nombre']}: {$sucursal['inventario']}";
                }
                $detalleSucursalStr = implode(' | ', $partes);
            }
            
            $resultados[] = [
                'id' => null,
                'id_sucursal' => null,
                'nombre_sucursal' => 'Inventario Global',
                'codbar' => (string) $ean,
                'nombre' => $producto['descripcion'] ?? '',
                'precio' => floatval($producto['precio'] ?? 0),
                'inventario' => $stockDisponibleGlobal,
                'inventario_original' => $inventarioGlobal,
                'apartado' => $stockApartadoGlobal,
                'num_familia' => $producto['num_familia'] ?? '',
                'nombre_familia' => $producto['nombre_familia'] ?? '',
                'sustancias_activas' => $sustanciasInfo['sustancias'],
                'es_medicamento' => $sustanciasInfo['es_medicamento'],
                'es_externo' => 0,
                'detalle_sucursales' => $detalleSucursalStr,
            ];
        }
        
        return collect($resultados);
    }

    /**
     * Obtener el desglose de inventario por sucursal para una lista de EANs
     */
    private function obtenerDetalleInventarioPorSucursal(array $eans): array
    {
        if (empty($eans)) {
            return [];
        }
        
        // Limpiar EANs
        $eansString = array_map(function($ean) {
            return trim((string) $ean);
        }, $eans);
        
        // Obtener inventario por sucursal para cada EAN
        $detalleRaw = DB::connection('sqlsrvM')
            ->table('catalogo_general')
            ->select(
                DB::raw("TRIM(CAST(ean as VARCHAR(50))) as ean"),
                'id_sucursal',
                'inventario'
            )
            ->whereIn(DB::raw("TRIM(CAST(ean as VARCHAR(50)))"), $eansString)
            ->where('inventario', '>', 0)
            ->orderBy('id_sucursal')
            ->get();
        
        // Obtener nombres de sucursales
        $sucursalesNombres = DB::connection('sqlsrvM')
            ->table('sucursales')
            ->whereIn('id_sucursal', $detalleRaw->pluck('id_sucursal')->unique()->toArray())
            ->pluck('nombre', 'id_sucursal')
            ->toArray();
        
        // Agrupar por EAN
        $resultados = [];
        foreach ($detalleRaw as $row) {
            $ean = trim((string) $row->ean);
            if (!isset($resultados[$ean])) {
                $resultados[$ean] = [];
            }
            
            $nombreSucursal = $sucursalesNombres[$row->id_sucursal] ?? "Sucursal {$row->id_sucursal}";
            $resultados[$ean][] = [
                'id_sucursal' => $row->id_sucursal,
                'nombre' => $nombreSucursal,
                'inventario' => $row->inventario
            ];
        }
        
        return $resultados;
    }

    /**
     * Obtener sustancias para una lista específica de EANs
     * Devuelve texto plano (sin HTML para resaltar)
     */
    private function obtenerSustanciasPorEan(array $eans, string $termino): array
    {
        if (empty($eans)) {
            return [];
        }
        
        // Convertir cada EAN a string
        $eansString = array_map(function($ean) {
            return (string) $ean;
        }, $eans);
        
        // Consulta única para todos los EANs
        $sustanciasRaw = DB::connection('sqlsrvM')
            ->table('catalogo_maestro')
            ->join('cat_sales_presentacion', 'catalogo_maestro.sales_presentacion', '=', 'cat_sales_presentacion.id')
            ->whereIn(DB::raw('CAST(catalogo_maestro.EAN as VARCHAR(50))'), $eansString)
            ->whereNotNull('catalogo_maestro.sales_presentacion')
            ->where('catalogo_maestro.sales_presentacion', '>', 0)
            ->select('catalogo_maestro.EAN', 'cat_sales_presentacion.sustancia')
            ->get();
        
        // Agrupar sustancias por EAN
        $sustanciasPorEan = [];
        foreach ($sustanciasRaw as $row) {
            $ean = trim((string) $row->EAN);
            if (!isset($sustanciasPorEan[$ean])) {
                $sustanciasPorEan[$ean] = [];
            }
            $sustanciasPorEan[$ean][] = $row->sustancia;
        }
        
        // Procesar resultados - sin HTML, solo texto plano
        $resultados = [];
        foreach ($eans as $ean) {
            $eanString = (string) $ean;
            if (isset($sustanciasPorEan[$eanString])) {
                $sustanciasLista = $sustanciasPorEan[$eanString];
                $sustanciasStr = implode(' / ', $sustanciasLista);
                $esMedicamento = true;
                
                $resultados[$eanString] = [
                    'sustancias' => $sustanciasStr,
                    'es_medicamento' => $esMedicamento
                ];
            } else {
                $resultados[$eanString] = [
                    'sustancias' => 'No es medicamento',
                    'es_medicamento' => false
                ];
            }
        }
        
        return $resultados;
    }

    /**
     * Buscar productos externos (tmp_catalogo)
     */
    private function buscarProductosExternos(string $termino): \Illuminate\Support\Collection
    {
        $queryExternos = TmpCatalogo::where('activo', 1);
        
        if (!empty($termino) && strlen($termino) >= 3) {
            $queryExternos->where(function($query) use ($termino) {
                $query->where('descripcion', 'LIKE', "%{$termino}%")
                    ->orWhere('ean', 'LIKE', "%{$termino}%");
            });
        }
        
        return $queryExternos
            ->limit(5)
            ->get()
            ->map(function($producto) {
                return [
                    'id' => $producto->id_tmp,
                    'id_sucursal' => null,
                    'nombre_sucursal' => 'Pedido a Proveedor',
                    'codbar' => (string) $producto->ean,
                    'nombre' => $producto->descripcion,
                    'precio' => floatval($producto->precio),
                    'inventario' => 0,
                    'inventario_original' => 0,
                    'apartado' => 0,
                    'num_familia' => 'EXT',
                    'sustancias_activas' => '',
                    'es_medicamento' => false,
                    'es_externo' => 1,
                    'detalle_sucursales' => 'No aplica (pedido a proveedor)',
                ];
            });
    }

    /**
     * Ordenar resultados por relevancia
     */
    private function ordenarPorRelevancia(\Illuminate\Support\Collection $productos, string $termino): \Illuminate\Support\Collection
    {
        $terminoLower = strtolower($termino);
        $terminoNormalizado = $this->normalizarTexto($terminoLower);
        
        return $productos->sortByDesc(function($producto) use ($terminoLower, $terminoNormalizado) {
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
        });
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
            
            $faseEnProceso = $fases->firstWhere('fase', 'En proceso');
            $faseEnProcesoId = $faseEnProceso ? $faseEnProceso->id_fase : null;
            
            $convenios = CatConvenio::where('status', 1)
                ->where('tipo', 'C')
                ->get(['id', 'convenio']);
            
            // Try-catch dentro del map para cada convenio
            $conveniosFormateados = $convenios->map(function($convenio) {
                try {
                    $familias = $convenio->getFamiliasConDescuento();
                    
                    return [
                        'id' => $convenio->id,
                        'nombre' => $convenio->convenio,
                        'familias' => $familias->map(function($familia) {
                            return [
                                'num_familia' => $familia->numfamilia,
                                'descuento' => $familia->descuento ?? 0
                            ];
                        })
                    ];
                } catch (\Exception $e) {
                    // Log del error pero continuar con los demás convenios
                    \Log::error('Error en convenio ' . $convenio->id . ': ' . $e->getMessage());
                    return [
                        'id' => $convenio->id,
                        'nombre' => $convenio->convenio,
                        'familias' => [] // Devolver array vacío para este convenio
                    ];
                }
            });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'fases' => $fases,
                    'fase_en_proceso_id' => $faseEnProcesoId,
                    'clasificaciones' => $clasificaciones,
                    'sucursales' => $sucursales,
                    'convenios' => $conveniosFormateados
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en catalogos: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
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
                'id_cliente' => 'required|exists:sqlsrvM.catalogo_cliente_maestro,id_Cliente',
                'id_fase' => 'required|exists:cat_fases,id_fase',
                'id_clasificacion' => 'nullable|exists:cat_clasificaciones,id_clasificacion',
                'id_sucursal_asignada' => 'nullable|exists:sqlsrvM.sucursales,id_sucursal',
                'certeza' => 'nullable|integer|in:1,2,3',
                'comentarios' => 'nullable|string|max:500',
                'fecha_entrega_sugerida' => 'nullable|date',
                'hora_entrega_sugerida' => 'nullable|date_format:H:i',
                'articulos' => 'required|array|min:1',
                'articulos.*.codbar' => 'required|string|max:20',
                'articulos.*.cantidad' => 'required|integer|min:1',
                'articulos.*.precio_unitario' => 'required|numeric|min:0',
                'articulos.*.descuento' => 'nullable|numeric|min:0|max:100',
                'articulos.*.id_convenio' => 'nullable|exists:sqlsrvM.cat_convenios,id',
                'articulos.*.es_externo' => 'nullable|in:0,1',
            ]);

            DB::beginTransaction();

            $importeTotal = 0;
            $articulosData = [];
            $sucursalAsignadaId = $validated['id_sucursal_asignada'] ?? null;
            $hayExternos = false;
            $stockDisponible = true;

            foreach ($validated['articulos'] as $articulo) {
                $descuento = $articulo['descuento'] ?? 0;
                $importe = $articulo['cantidad'] * $articulo['precio_unitario'] * (1 - $descuento / 100);
                $importeTotal += $importe;
                
                // Determinar tipo de producto
                $es_externo = $articulo['es_externo'] ?? 0;
                
                if ($es_externo == 1) {
                    // ============================================
                    // PRODUCTO EXTERNO - Buscar en tmp_catalogo
                    // ============================================
                    $hayExternos = true;
                    $productoExterno = TmpCatalogo::where('ean', $articulo['codbar'])->first();
                    
                    if (!$productoExterno) {
                        throw new \Exception('Producto sobre pedido no encontrado: ' . $articulo['codbar']);
                    }
                    
                    $articulosData[] = [
                        'codbar' => $productoExterno->ean,
                        'cantidad' => $articulo['cantidad'],
                        'precio_unitario' => $articulo['precio_unitario'],
                        'descuento' => $descuento,
                        'importe' => $importe,
                        'id_convenio' => $articulo['id_convenio'] ?? null,
                        'id_sucursal' => null,
                        'es_externo' => 1,
                    ];
                } else {
                    // ============================================
                    // PRODUCTO NORMAL - Buscar en catalogo_general
                    // ============================================
                    $producto = CatalogoGeneral::where('ean', $articulo['codbar'])->first();
                    
                    if (!$producto) {
                        throw new \Exception('Producto no encontrado: ' . $articulo['codbar']);
                    }
                    
                    // Verificar stock en sucursal asignada o global
                    if ($sucursalAsignadaId) {
                        $productoSucursal = CatalogoGeneral::where('ean', $articulo['codbar'])
                            ->where('id_sucursal', $sucursalAsignadaId)
                            ->first();
                        if ($productoSucursal && $productoSucursal->inventario < $articulo['cantidad']) {
                            $stockDisponible = false;
                        }
                    } else {
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
                        'id_sucursal' => $producto->id_sucursal,
                        'es_externo' => 0,
                    ];
                }
            }

            // Calcular fecha de entrega sugerida
            $fechaEntrega = Cotizacion::calcularFechaEntregaSugerida(now(), $stockDisponible, $hayExternos);

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
                'fecha_entrega_sugerida' => $validated['fecha_entrega_sugerida'] ?? $fechaEntrega['fecha'],
                'hora_entrega_sugerida' => $validated['hora_entrega_sugerida'] ?? $fechaEntrega['hora'],
                'activo' => 1,
                'enviado' => 0,
                'version' => 1,
                'creado_por' => auth()->id(),
                'modificado_por' => auth()->id(),
            ]);

            foreach ($articulosData as $detalle) {
                CotizacionDetalle::create(array_merge($detalle, [
                    'id_cotizacion' => $cotizacion->id_cotizacion,
                    'apartado' => $apartado,
                    'fecha_actualizacion' => now(),
                    'activo' => 1
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
            \Log::error('Error en store cotización: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la cotización: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id): JsonResponse
    {
        // Si $id es un objeto, intentar extraer el ID
        if (is_object($id)) {
            // Si es un modelo de Eloquent
            if (method_exists($id, 'getKey')) {
                $id = $id->getKey();
            } 
            // Si tiene propiedad id
            elseif (property_exists($id, 'id')) {
                $id = $id->id;
            }
            // Si tiene propiedad id_cotizacion
            elseif (property_exists($id, 'id_cotizacion')) {
                $id = $id->id_cotizacion;
            }
            // Si no se puede extraer, error
            else {
                return response()->json([
                    'success' => false, 
                    'message' => 'ID inválido: no se pudo extraer el identificador'
                ], 400);
            }
        }
        
        // Convertir a int y validar
        $id = (int) $id;
        if ($id <= 0) {
            return response()->json(['success' => false, 'message' => 'ID inválido'], 400);
        }
        
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'ver')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        $cotizacion = Cotizacion::with([
            'cliente', 'fase', 'clasificacion', 'sucursalAsignada',
            'detalles.convenio', 
            'detalles.sucursalSurtido', 
            'detalles.producto',
            'creador', 'modificador'
        ])->findOrFail($id);
        
        // ============================================
        // CARGAR INTERESES Y PATOLOGÍAS DEL CLIENTE
        // ============================================
        if ($cotizacion->cliente) {
            $clienteId = $cotizacion->cliente->id_Cliente;
            
            // Obtener intereses del cliente
            $interesesIds = DB::connection('sqlsrv')
                ->table('crm_cliente_intereses')
                ->where('id_cliente', $clienteId)
                ->where('activo', 1)
                ->pluck('id_interes')
                ->toArray();
            
            $intereses = [];
            if (!empty($interesesIds)) {
                $intereses = DB::connection('sqlsrvM')
                    ->table('crm_cat_intereses')
                    ->whereIn('id_interes', $interesesIds)
                    ->pluck('Descripcion')
                    ->toArray();
            }
            
            // Obtener patologías del cliente
            $patologias = DB::connection('sqlsrv')
                ->table('crm_patologia_asociada')
                ->where('id_cliente_maestro', $clienteId)
                ->where('status', 1)
                ->pluck('patologia')
                ->toArray();
            
            // Agregar al objeto cliente
            $cotizacion->cliente->intereses = $intereses;
            $cotizacion->cliente->patologias = $patologias;
        }
        
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
            // CARGAR PRODUCTO Y EXTRAER INVENTARIO
            // ============================================
            if ($detalle->es_externo == 1) {
                $producto = TmpCatalogo::where('ean', $detalle->codbar)->first();
                $detalle->producto = $producto;
                $detalle->es_externo = true;
                // ASIGNAR descripcion
                $detalle->descripcion = $producto->descripcion ?? 'Sobre Pedido';
                
                // Para externos, inventario = 999 y desglose no aplica
                $detalle->inventario_disponible = 999;
                $detalle->detalle_sucursales = 'No aplica (pedido a proveedor)';
                
                if (!$producto) {
                    \Log::warning("Producto sobre pedido no encontrado con codbar: {$detalle->codbar}");
                }
            } else {
                $producto = CatalogoGeneral::where('ean', $detalle->codbar)->first();
                $detalle->producto = $producto;
                $detalle->es_externo = false;
                $detalle->descripcion = $producto->descripcion ?? 'Producto no disponible';
                
                if ($producto) {
                    // Obtener detalle de sucursales con total
                    $detalleSucursales = $this->obtenerDetalleSucursales($detalle->codbar);
                    
                    // ASIGNAR INVENTARIO GLOBAL REAL (suma de todas las sucursales)
                    $detalle->inventario_global = $detalleSucursales['total'] ?? intval($producto->inventario ?? 0);
                    $detalle->detalle_sucursales = $detalleSucursales['desglose'] ?? '';
                    $detalle->num_familia = $producto->num_familia ?? '';
                    $detalle->inventario_disponible = $detalleSucursales['total'] ?? intval($producto->inventario ?? 0);
                if (!empty($detalleSucursales['desglose'])) {
                    $primerSucursal = explode(':', $detalleSucursales['desglose']);
                    $nombreSucursal = trim($primerSucursal[0] ?? 'No asignada');
                    $detalle->nombre_sucursal_surtido = $nombreSucursal;
                } else 
                    $detalle->nombre_sucursal_surtido = 'No asignada';
                }
            }
            
            // Opcional: mantener nombre_producto por compatibilidad
            if ($detalle->producto) {
                $detalle->nombre_producto = $detalle->producto->descripcion;
            } else {
                $detalle->nombre_producto = 'Producto no disponible';
            }
        }
        
        if ($cotizacion->fecha_entrega_sugerida) {
            $cotizacion->fecha_entrega_sugerida = Carbon::parse($cotizacion->fecha_entrega_sugerida)->format('Y-m-d');
        }

        return response()->json([
            'success' => true,
            'data' => $cotizacion
        ]);
    }

    /**
     * Obtiene el desglose de inventario por sucursal para un EAN
     */
    private function obtenerDetalleSucursales(string $ean): array
    {
        try {
            $detalle = DB::connection('sqlsrvM')
                ->table('catalogo_general')
                ->where(DB::raw('TRIM(CAST(ean AS VARCHAR(50)))'), trim($ean))
                ->where('inventario', '>', 0)
                ->select('id_sucursal', 'inventario')
                ->get();
            
            if ($detalle->isEmpty()) {
                return ['total' => 0, 'desglose' => ''];
            }
            
            $sucursalesIds = $detalle->pluck('id_sucursal')->unique()->toArray();
            $sucursalesNombres = DB::connection('sqlsrvM')
                ->table('sucursales')
                ->whereIn('id_sucursal', $sucursalesIds)
                ->pluck('nombre', 'id_sucursal')
                ->toArray();
            
            $partes = [];
            $total = 0;
            foreach ($detalle as $row) {
                $nombre = $sucursalesNombres[$row->id_sucursal] ?? "Sucursal {$row->id_sucursal}";
                $partes[] = "{$nombre}: {$row->inventario}";
                $total += $row->inventario;
            }
            
            return [
                'total' => $total,
                'desglose' => implode(' | ', $partes)
            ];
        } catch (\Exception $e) {
            \Log::error('Error al obtener detalle de sucursales: ' . $e->getMessage());
            return ['total' => 0, 'desglose' => ''];
        }
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
            'articulos.*.codbar' => 'required|string|max:20',
            'articulos.*.cantidad' => 'required|integer|min:1',
            'articulos.*.precio_unitario' => 'required|numeric|min:0',
            'articulos.*.descuento' => 'nullable|numeric|min:0|max:100',
            'articulos.*.id_convenio' => 'nullable|exists:sqlsrvM.cat_convenios,id',
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
            'id_clasificacion' => $cotizacionOriginal->id_clasificacion,
            'id_sucursal_asignada' => $cotizacionOriginal->id_sucursal_asignada,
            'certeza' => $cotizacionOriginal->certeza,
            'comentarios' => $cotizacionOriginal->comentarios,
            'id_convenio_general' => $cotizacionOriginal->id_convenio_general,
            'fecha_entrega_sugerida' => $cotizacionOriginal->fecha_entrega_sugerida,
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
                        $nombre = 'Sobre Pedido';
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
                    $producto = CatalogoGeneral::where('ean', $detalle->codbar)->first();
                    
                    if (!$producto) {
                        $producto = CatalogoGeneral::find($detalle->id_producto);
                    }
                    
                    return [
                        'codbar' => $detalle->codbar,
                        'descripcion' => $producto->descripcion ?? $detalle->descripcion,
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
                'articulos.*.id_convenio' => 'nullable|exists:sqlsrvM.cat_convenios,id',
                'articulos.*.es_externo' => 'nullable|in:0,1',
            ]);
            
            // Desactivar la cotización original
            $cotizacionOriginal->activo = 0;
            $cotizacionOriginal->save();
            
            $importeTotal = 0;
            $articulosData = [];
            
            foreach ($validated['articulos'] as $articulo) {
                $descuento = $articulo['descuento'] ?? 0;
                $importe = $articulo['cantidad'] * $articulo['precio_unitario'] * (1 - $descuento / 100);
                $importeTotal += $importe;
                
                // Determinar tipo de producto
                $es_externo = $articulo['es_externo'] ?? 0;
                
                if ($es_externo == 1) {
                    // ============================================
                    // PRODUCTO EXTERNO - Buscar en tmp_catalogo por codbar
                    // ============================================
                    $productoExterno = TmpCatalogo::where('ean', $articulo['codbar'])->first();
                    if (!$productoExterno) {
                        throw new \Exception('Producto sobre pedido no encontrado: ' . $articulo['codbar']);
                    }
                    
                    $articulosData[] = [
                        'codbar' => $productoExterno->ean,
                        'cantidad' => $articulo['cantidad'],
                        'precio_unitario' => $articulo['precio_unitario'],
                        'descuento' => $descuento,
                        'importe' => $importe,
                        'id_convenio' => $articulo['id_convenio'] ?? null,
                        'id_sucursal' => null,
                        'es_externo' => 1,
                    ];
                } else {
                    // ============================================
                    // PRODUCTO NORMAL - Buscar en catalogo_general por codbar
                    // ============================================
                    $producto = CatalogoGeneral::where('ean', $articulo['codbar'])->first();
                    if (!$producto) {
                        throw new \Exception('Producto no encontrado: ' . $articulo['codbar']);
                    }
                    
                    $articulosData[] = [
                        'codbar' => $producto->ean,
                        'cantidad' => $articulo['cantidad'],
                        'precio_unitario' => $articulo['precio_unitario'],
                        'descuento' => $descuento,
                        'importe' => $importe,
                        'id_convenio' => $articulo['id_convenio'] ?? null,
                        'id_sucursal' => $producto->id_sucursal,
                        'es_externo' => 0,
                    ];
                }
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
            \Log::error('Error al guardar nueva versión: ' . $e->getMessage());
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
                'fecha_entrega_sugerida' => 'nullable|date',
                'articulos' => 'required|array|min:1',
                'articulos.*.codbar' => 'required|string|max:20',
                'articulos.*.cantidad' => 'required|integer|min:1',
                'articulos.*.precio_unitario' => 'required|numeric|min:0',
                'articulos.*.descuento' => 'nullable|numeric|min:0|max:100',
                'articulos.*.id_convenio' => 'nullable|exists:sqlsrvM.cat_convenios,id',
                'articulos.*.es_externo' => 'nullable|in:0,1',
            ]);

            DB::beginTransaction();

            $importeTotal = 0;
            $articulosData = [];
            $sucursalAsignadaId = $validated['id_sucursal_asignada'] ?? null;
            $hayExternos = false;
            $stockDisponible = true;

            foreach ($validated['articulos'] as $articulo) {
                $descuento = $articulo['descuento'] ?? 0;
                $importe = $articulo['cantidad'] * $articulo['precio_unitario'] * (1 - $descuento / 100);
                $importeTotal += $importe;
                
                // Determinar tipo de producto
                $es_externo = $articulo['es_externo'] ?? 0;
                
                if ($es_externo == 1) {
                    // ============================================
                    // PRODUCTO EXTERNO - Buscar en tmp_catalogo
                    // ============================================
                    $hayExternos = true;
                    $productoExterno = TmpCatalogo::where('ean', $articulo['codbar'])->first();
                    if (!$productoExterno) {
                        throw new \Exception('Producto sobre pedido no encontrado: ' . $articulo['codbar']);
                    }
                    
                    $articulosData[] = [
                        'codbar' => $productoExterno->ean,
                        'cantidad' => $articulo['cantidad'],
                        'precio_unitario' => $articulo['precio_unitario'],
                        'descuento' => $descuento,
                        'importe' => $importe,
                        'id_convenio' => $articulo['id_convenio'] ?? null,
                        'id_sucursal' => null,
                        'es_externo' => 1,
                        // Para externos
                        'inventario_global' => 999,
                        'detalle_sucursales' => 'No aplica (pedido a proveedor)',
                    ];
                } else {
                    $producto = CatalogoGeneral::where('ean', $articulo['codbar'])->first();
                    if (!$producto) {
                        throw new \Exception('Producto no encontrado: ' . $articulo['codbar']);
                    }
                    // VERIFICAR STOCK
                    if ($producto->inventario < $articulo['cantidad']) {
                        $stockDisponible = false;
                    }
                    
                    // Obtener desglose de sucursales y total
                    $detalleSucursales = $this->obtenerDetalleSucursales($producto->ean);
                    
                    $articulosData[] = [
                        'codbar' => $producto->ean,
                        'cantidad' => $articulo['cantidad'],
                        'precio_unitario' => $articulo['precio_unitario'],
                        'descuento' => $descuento,
                        'importe' => $importe,
                        'id_convenio' => $articulo['id_convenio'] ?? null,
                        'id_sucursal' => $producto->id_sucursal,
                        'es_externo' => 0,
                        'inventario_global' => $detalleSucursales['total'] ?? intval($producto->inventario ?? 0),
                        'detalle_sucursales' => $detalleSucursales['desglose'] ?? '',
                        'nombre_sucursal_surtido' => $this->obtenerNombreSucursalSurtido($detalleSucursales['desglose'] ?? ''),
                    ];
                }
            }

            // CALCULAR FECHA DE ENTREGA SUGERIDA
            $fechaEntrega = Cotizacion::calcularFechaEntregaSugerida(now(), $stockDisponible, $hayExternos);

            $certeza = $validated['certeza'] ?? 0;
            $apartado = ($certeza == 3) ? 1 : 0;

            // ============================================
            // FORZAR FASE "EN PROCESO" PARA NUEVA COTIZACIÓN
            // ============================================
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
                'fecha_entrega_sugerida' => $validated['fecha_entrega_sugerida'] ?? $fechaEntrega['fecha'],
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
     * Obtiene el nombre de la primera sucursal del desglose
     */
    private function obtenerNombreSucursalSurtido(string $desglose): string
    {
        if (empty($desglose)) {
            return 'No asignada';
        }
        
        $primerSucursal = explode(':', $desglose);
        return trim($primerSucursal[0] ?? 'No asignada');
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
            $sucursalAsignadaId = $validated['id_sucursal_asignada'] ?? null;
            $hayExternos = false;
            $stockDisponible = true;

            foreach ($validated['articulos'] as $articulo) {
                $descuento = $articulo['descuento'] ?? 0;
                $importe = $articulo['cantidad'] * $articulo['precio_unitario'] * (1 - $descuento / 100);
                $importeTotal += $importe;
                
                $es_externo = $articulo['es_externo'] ?? 0;
                
                if ($es_externo == 1) {
                    $hayExternos = true;
                    $productoExterno = TmpCatalogo::where('ean', $articulo['codbar'])->first();
                    if (!$productoExterno) {
                        throw new \Exception('Producto sobre pedido no encontrado: ' . $articulo['codbar']);
                    }
                    
                    $articulosData[] = [
                        'codbar' => $productoExterno->ean,
                        'cantidad' => $articulo['cantidad'],
                        'precio_unitario' => $articulo['precio_unitario'],
                        'descuento' => $descuento,
                        'importe' => $importe,
                        'id_convenio' => $articulo['id_convenio'] ?? null,
                        'id_sucursal' => null,
                        'es_externo' => 1,
                    ];
                } else {
                    // ============================================
                    // PRODUCTO NORMAL - Buscar en catalogo_general por codbar
                    // ============================================
                    $producto = CatalogoGeneral::where('ean', $articulo['codbar'])->first();
                    if (!$producto) {
                        throw new \Exception('Producto no encontrado: ' . $articulo['codbar']);
                    }
                    
                    // Verificar stock en sucursal asignada o global
                    if ($sucursalAsignadaId) {
                        $productoSucursal = CatalogoGeneral::where('ean', $articulo['codbar'])
                            ->where('id_sucursal', $sucursalAsignadaId)
                            ->first();
                        if ($productoSucursal && $productoSucursal->inventario < $articulo['cantidad']) {
                            $stockDisponible = false;
                        }
                    } else {
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
                        'id_sucursal' => $producto->id_sucursal,
                        'es_externo' => 0,
                    ];
                }
            }

            // Calcular fecha de entrega sugerida si no viene en el request
            if (!isset($validated['fecha_entrega_sugerida']) || empty($validated['fecha_entrega_sugerida'])) {
                $fechaEntrega = Cotizacion::calcularFechaEntregaSugerida(now(), $stockDisponible, $hayExternos);
                $fechaEntregaSugerida = $fechaEntrega['fecha'];
                $horaEntregaSugerida = $fechaEntrega['hora'];
            } else {
                $fechaEntregaSugerida = $validated['fecha_entrega_sugerida'];
                $horaEntregaSugerida = $validated['hora_entrega_sugerida'] ?? '12:00:00';
            }

            $certeza = $validated['certeza'] ?? 0;
            $apartado = ($certeza == 3) ? 1 : 0;
            
            $cotizacion->update([
                'id_fase' => $validated['id_fase'],
                'id_clasificacion' => $validated['id_clasificacion'] ?? null,
                'id_sucursal_asignada' => $validated['id_sucursal_asignada'] ?? null,
                'certeza' => $certeza,
                'importe_total' => $importeTotal,
                'comentarios' => $validated['comentarios'],
                'fecha_entrega_sugerida' => $fechaEntregaSugerida,
                'hora_entrega_sugerida' => $horaEntregaSugerida,
                'enviado' => 0,
                'modificado_por' => auth()->id(),
            ]);

            // Eliminar detalles antiguos
            $cotizacion->detalles()->delete();
            
            // Crear nuevos detalles
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
    protected function verificarStockSucursal($detalles, $sucursalId, $cotizacionId = null): bool
    {
        if (!$sucursalId) return true;

        foreach ($detalles as $detalle) {
            // Saltar productos externos
            if (isset($detalle['es_externo']) && $detalle['es_externo'] == 1) {
                continue;
            }
            
            // Obtener codbar (puede venir como array o como objeto)
            $codbar = $detalle['codbar'] ?? $detalle->codbar ?? null;
            $cantidad = $detalle['cantidad'] ?? $detalle->cantidad ?? 0;
            
            if (!$codbar) continue;
            
            // Obtener stock apartado para este producto
            $stockApartado = DB::table('crm_cotizaciones_detalle as cd')
                ->join('crm_cotizaciones as c', 'cd.id_cotizacion', '=', 'c.id_cotizacion')
                ->where('cd.apartado', 1)
                ->where('c.activo', 1)
                ->where('c.es_pedido', '!=', 1)
                ->where('c.certeza', 3)
                ->where('cd.codbar', $codbar)
                ->where('cd.id_sucursal', $sucursalId);
            
            if ($cotizacionId) {
                $stockApartado->where('c.id_cotizacion', '!=', $cotizacionId);
            }
            
            $cantidadApartada = $stockApartado->sum('cd.cantidad');
            
            // Obtener inventario actual del producto
            $producto = CatalogoGeneral::where('ean', $codbar)
                ->where('id_sucursal', $sucursalId)
                ->first();
            
            if ($producto) {
                $stockDisponible = $producto->inventario - $cantidadApartada;
                if ($stockDisponible < $cantidad) {
                    return false;
                }
            }
        }
        return true;
    }

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
     * Soft delete quotation (cambiar a fase 3 - Cancelada)
     */
    public function destroy(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'eliminar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            $cotizacion = Cotizacion::findOrFail($id);
            
            // Soft delete: cambiar a fase 3 (Cancelada)
            $cotizacion->id_fase = 3;  // Cancelada
            $cotizacion->save();
            
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
            ->first();
        
        if (!$producto) {
            return response()->json(['success' => true, 'data' => []]);
        }
        
        // Obtener cantidad apartada GLOBALMENTE para este EAN
        $stockApartadoGlobal = DB::table('crm_cotizaciones_detalle as cd')
            ->join('crm_cotizaciones as c', 'cd.id_cotizacion', '=', 'c.id_cotizacion')
            ->where('cd.apartado', 1)
            ->where('c.activo', 1)
            ->where('c.es_pedido', '!=', 1)
            ->where('c.certeza', 3)
            ->where('cd.codbar', $ean);
        
        if ($cotizacionId) {
            $stockApartadoGlobal->where('c.id_cotizacion', '!=', $cotizacionId);
        }
        
        $cantidadApartadaGlobal = $stockApartadoGlobal->sum('cd.cantidad');
        
        // Obtener inventario global de este EAN
        $inventarioGlobal = CatalogoGeneral::where('ean', $ean)->sum('inventario');
        $stockDisponibleGlobal = max(0, $inventarioGlobal - $cantidadApartadaGlobal);
        
        $resultado = [
            'id' => $producto->id_catalogo_general,
            'codbar' => $producto->ean,
            'nombre' => $producto->descripcion,
            'precio' => floatval($producto->precio),
            'inventario' => $stockDisponibleGlobal,
            'inventario_original' => $inventarioGlobal,
            'apartado' => $cantidadApartadaGlobal,
            'num_familia' => $producto->num_familia,
            'nombre_sucursal' => $producto->sucursal->nombre ?? 'Sin sucursal',
            'id_sucursal_asignada' => $sucursalId
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
            return $producto->descripcion ?? 'Producto sobre pedido no disponible';
        } else {
            $producto = CatalogoGeneral::where('ean', $codbar)->first();
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
                'message' => 'Producto sobre pedido guardado correctamente',
                'data' => [
                    'id' => $producto->id_tmp,
                    'ean' => $producto->ean,
                    'descripcion' => $producto->descripcion,
                    'precio' => $producto->precio,
                    'es_externo' => 1,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . json_encode($e->errors())
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar producto sobre pedido: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convertir cotización a pedido.
     */
    public function generarPedido(int $id): JsonResponse
    {
        // Redirigir al método con asignación
        return $this->generarPedidoConAsignacion(new Request(), $id);
    }

    public function disponibilidadInventario(int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'ver')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        $cotizacion = Cotizacion::with('detalles')->findOrFail($id);
        
        // Obtener todas las sucursales activas
        $todasLasSucursales = DB::connection('sqlsrvM')
            ->table('sucursales')
            ->where('activo', 1)
            ->select('id_sucursal', 'nombre')
            ->get()
            ->map(function($item) {
                return [
                    'id_sucursal' => $item->id_sucursal,
                    'nombre' => $item->nombre,
                    'inventario' => 0 // Sin stock para "Sobre Pedido"
                ];
            })
            ->toArray();
        
        $resultado = [];
        
        foreach ($cotizacion->detalles as $detalle) {
            $esExterno = $detalle->es_externo == 1;
            
            if ($esExterno) {
                // Productos externos: mostramos todas las sucursales con inventario 0
                $stockPorSucursal = array_map(function($sucursal) {
                    return [
                        'id_sucursal' => $sucursal['id_sucursal'],
                        'nombre' => $sucursal['nombre'],
                        'inventario' => 0
                    ];
                }, $todasLasSucursales);
                
                $sucursalesCompletas = $stockPorSucursal;
                $inventarioGlobal = 0;
                
            } else {
                // Productos normales: obtener stock por sucursal
                $stockPorSucursal = DB::connection('sqlsrvM')
                    ->table('catalogo_general')
                    ->where('ean', $detalle->codbar)
                    ->where('inventario', '>', 0)
                    ->join('sucursales', 'catalogo_general.id_sucursal', '=', 'sucursales.id_sucursal')
                    ->select(
                        'sucursales.id_sucursal', 
                        'sucursales.nombre', 
                        DB::raw('CAST(catalogo_general.inventario AS INT) as inventario')
                    )
                    ->orderBy('catalogo_general.inventario', 'desc')
                    ->get()
                    ->map(function($item) {
                        return [
                            'id_sucursal' => $item->id_sucursal,
                            'nombre' => $item->nombre,
                            'inventario' => (int) $item->inventario
                        ];
                    })
                    ->toArray();
                
                // Combinar: sucursales con stock + todas las sucursales (para "Sobre Pedido")
                $sucursalesCompletas = array_merge(
                    $stockPorSucursal,
                    array_filter($todasLasSucursales, function($sucursal) use ($stockPorSucursal) {
                        return !in_array($sucursal['id_sucursal'], array_column($stockPorSucursal, 'id_sucursal'));
                    })
                );
                
                $inventarioGlobal = collect($stockPorSucursal)->sum('inventario');
            }
            
            $nombreProducto = $detalle->descripcion;
            if (empty($nombreProducto) && $detalle->codbar) {
                $producto = CatalogoGeneral::where('ean', $detalle->codbar)->first();
                $nombreProducto = $producto->descripcion ?? $detalle->codbar;
            }
            
            $resultado[] = [
                'codbar' => $detalle->codbar,
                'nombre' => $nombreProducto,
                'cantidad' => $detalle->cantidad,
                'inventario_global' => $inventarioGlobal,
                'es_externo' => $esExterno,
                'stock_por_sucursal' => $sucursalesCompletas, // Todas las sucursales
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $resultado
        ]);
    }

    public function generarPedidoConAsignacion(Request $request, int $id): JsonResponse
    {
        if (!auth()->user()->puede('ventas', 'cotizaciones', 'editar')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $cotizacion = Cotizacion::with(['detalles', 'cliente'])->findOrFail($id);
            $asignaciones = $request->input('asignaciones');
            
            // Validaciones existentes...
            if (!$cotizacion->enviado) {
                return response()->json(['success' => false, 'message' => 'La cotización no ha sido enviada al cliente'], 400);
            }
            
            if ($cotizacion->fase_nombre !== 'Completada') {
                return response()->json(['success' => false, 'message' => 'La cotización no está completada'], 400);
            }
            
            if ($cotizacion->es_pedido) {
                return response()->json(['success' => false, 'message' => 'Esta cotización ya es un pedido'], 400);
            }
            
            // Crear pedido
            $folioPedido = $this->generarFolioPedido();
            $pedido = OrdenPedido::create([
                'id_cotizacion' => $cotizacion->id_cotizacion,
                'folio_pedido' => $folioPedido,
                'status' => 2,
                'fecha_pedido' => now(),
                'fecha_entrega_sugerida' => $cotizacion->fecha_entrega_sugerida ?? now()->addDay(),
                'hora_entrega_sugerida' => '12:00:00',
                'creado_por' => auth()->id(),
                'created_at' => now(),
            ]);
            
            // Crear detalles del pedido con asignaciones por sucursal
            foreach ($asignaciones as $asignacion) {
                $detalleCotizacion = $cotizacion->detalles[$asignacion['articulo_index']];
                
                foreach ($asignacion['detalles'] as $detalleAsignacion) {
                    // Obtener la sucursal
                    $sucursalId = $detalleAsignacion['sucursal'] ?? null;
                    
                    // Si la sucursal es 'especial' (string), se debe usar la sucursal seleccionada en el select
                    // que ya debería venir en el campo 'sucursal' como número
                    if ($sucursalId === 'especial') {
                        $sucursalId = null;
                    }
                    
                    // Si el producto es externo y no tiene sucursal, NO asignar fallback
                    // Usar null para que luego en el proceso de "marcar como listo" se pueda asignar
                    if ($detalleCotizacion->es_externo == 1 && $sucursalId === null) {
                        // Intentar obtener la sucursal del detalle de asignación (sucursal_nombre o similar)
                        // O dejar null para que el usuario lo asigne al marcar como listo
                        $sucursalId = null;
                    }
                    
                    OrdenPedidoDetalle::create([
                        'id_pedido' => $pedido->id_pedido,
                        'id_cotizacion_detalle' => $detalleCotizacion->id_cotizacion_detalle,
                        'ean' => $detalleCotizacion->codbar,
                        'cantidad' => $detalleAsignacion['cantidad'],
                        'precio_unitario' => $detalleCotizacion->precio_unitario,
                        'descuento' => $detalleCotizacion->descuento,
                        'importe' => $detalleAsignacion['cantidad'] * $detalleCotizacion->precio_unitario * (1 - $detalleCotizacion->descuento / 100),
                        'es_externo' => $detalleCotizacion->es_externo,
                        'id_sucursal_surtido' => $sucursalId,
                    ]);
                }
            }
            
            // CREAR REGISTROS EN orden_pedido_sucursal
            // Obtener las sucursales únicas que tienen productos asignados
            $sucursalesAsignadas = collect($asignaciones)
                ->flatMap(function($asignacion) {
                    return collect($asignacion['detalles'])
                        ->pluck('sucursal')
                        ->filter(function($sucursal) {
                            // Filtrar valores nulos y 'especial'
                            return $sucursal !== null && $sucursal !== 'especial' && $sucursal !== '';
                        })
                        ->map(function($sucursal) {
                            return (int) $sucursal;
                        });
                })
                ->unique()
                ->values()
                ->toArray();
            
            // Crear registros en orden_pedido_sucursal para cada sucursal asignada
            foreach ($sucursalesAsignadas as $sucursalId) {
                $existe = OrdenPedidoSucursal::where('id_pedido', $pedido->id_pedido)
                    ->where('id_sucursal', $sucursalId)
                    ->exists();
                
                if (!$existe) {
                    OrdenPedidoSucursal::create([
                        'id_pedido' => $pedido->id_pedido,
                        'id_sucursal' => $sucursalId,
                        'status' => 0,
                        'fecha_asignacion' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            // Marcar cotización como pedido
            $cotizacion->update([
                'es_pedido' => true,
                'modificado_por' => auth()->id(),
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Pedido generado correctamente',
                'data' => [
                    'pedido_id' => $pedido->id_pedido,
                    'folio_pedido' => $folioPedido
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al generar pedido con asignación: ' . $e->getMessage());
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

    /**
     * Refrescar la tabla de cotizaciones vía AJAX (para polling)
     */
    public function refrescarTabla(Request $request)
    {
        try {
            $puedeVer = auth()->user()->puede('ventas', 'cotizaciones', 'ver');
            
            if (!$puedeVer) {
                return response()->json(['success' => false, 'message' => 'Sin permiso'], 403);
            }
            
            $puedeEditar = auth()->user()->puede('ventas', 'cotizaciones', 'editar');
            $puedeEliminar = auth()->user()->puede('ventas', 'cotizaciones', 'eliminar');
            
            // Misma consulta que en index()
            $cotizaciones = Cotizacion::with(['cliente', 'fase', 'clasificacion'])
                ->where('activo', 1)
                ->where('es_pedido', '!=', 1) // NO mostrar cotizaciones que ya son pedidos
                ->where('id_fase', '!=', 3)
                ->orderBy('fecha_creacion', 'desc')
                ->paginate(15);
            
            $permisos = [
                'ver' => $puedeVer,
                'crear' => auth()->user()->puede('ventas', 'cotizaciones', 'crear'),
                'editar' => $puedeEditar,
                'eliminar' => $puedeEliminar,
            ];
            
            $ultimoId = $cotizaciones->isNotEmpty() ? $cotizaciones->first()->id_cotizacion : 0;
            
            $html = view('ventas.cotizaciones.partials.tabla-cotizaciones', compact('cotizaciones', 'permisos'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html,
                'ultimo_id' => $ultimoId,
                'total' => $cotizaciones->total()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en refrescarTabla: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}