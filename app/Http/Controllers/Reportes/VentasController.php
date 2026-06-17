<?php
// app/Http/Controllers/Reportes/VentasController.php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Reportes\HistorialVenta;
use App\Models\Cliente;
use App\Models\Cotizaciones\Cotizacion;
use App\Models\CatalogoGeneral;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\VentasClienteExport;
use App\Exports\TopClientesExport;
use App\Exports\TopProductosExport;
use App\Exports\MontosPromedioExport;
use App\Exports\SucursalesPreferidasExport;
use Illuminate\Http\JsonResponse;
use App\Models\Reportes\IndicacionTerapeutica;
use App\Models\TmpCatalogo;
use App\Models\Pedidos\OrdenPedido;
use App\Models\Pedidos\OrdenPedidoDetalle;

class VentasController extends Controller
{
    protected $validSortFields = [
        'monto_total' => 'Mayor monto',
        'monto_total_asc' => 'Menor monto',
        'total_transacciones' => 'Más compras',
        'total_transacciones_asc' => 'Menos compras'
    ];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->puede('reportes', 'compras_cliente', 'ver')) {
                abort(403, 'No tiene permisos para acceder a esta sección');
            }
            return $next($request);
        });
    }

    /**
     * Dashboard principal con KPIs
     */
    public function index(Request $request)
    {
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];

        // Obtener KPIs
        $kpis = HistorialVenta::getKPIs($fechaInicio, $fechaFin);

        // Datos para gráficos - Top 5 productos
        $topProductos = $this->getTopProductos($fechaInicio, $fechaFin, 5);

        // Datos para gráfico - Ventas por día (últimos 30 días)
        $ventasPorDia = $this->getVentasPorDia($fechaInicio, $fechaFin);

        // Datos para gráfico - Top 5 clientes
        $topClientes = HistorialVenta::getResumenClientes($fechaInicio, $fechaFin, 5);

        return view('reportes.compras_cliente.index', compact(
            'kpis', 'topProductos', 'ventasPorDia', 'topClientes', 'fechaInicio', 'fechaFin'
        ));
    }

    /**
     * Obtener datos de clientes vía AJAX
     */
    public function clientesData(Request $request)
    {
        try {
            $fechas = $this->getFechasFiltro($request);
            $fechaInicio = $fechas['inicio'];
            $fechaFin = $fechas['fin'];
            
            $top = $request->input('top', 50);
            $sortBy = $request->input('sort_by', 'monto_total');
            $searchCliente = $request->input('search_cliente');
            $indicacionId = $request->input('indicacion_id');
            
            // IDs a ignorar
            $idsExcluir = ['0000000007295', '0000000004489'];
            
            // Validar fechas
            if (!$fechaInicio || !$fechaFin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar un período de fechas',
                    'data' => []
                ]);
            }
            
            // Construir la consulta
            $query = HistorialVenta::entreFechas($fechaInicio, $fechaFin)
                ->join('fp_central_matriz.dbo.catalogo_cliente_maestro as c', 'historial_ventas_matriz.IDCLIENTE', '=', 'c.idtarjetaclientefrecuente')
                ->whereNotIn('historial_ventas_matriz.IDCLIENTE', $idsExcluir); // Excluir IDs especiales
            
            // Aplicar filtro de indicación terapéutica (si se seleccionó)
            if ($indicacionId) {
                $query->join('fp_central_matriz.dbo.catalogo_maestro as cm', 'cm.EAN', '=', 'historial_ventas_matriz.F_CODBAR')
                    ->where('cm.id_ITerapeutica', $indicacionId);
            }
            
            $query->select(
                    'c.id_Cliente',
                    'c.Nombre',
                    'c.apPaterno',
                    'c.apMaterno',
                    DB::raw('COUNT(DISTINCT F_NUMTICKE) as total_transacciones'),
                    DB::raw('SUM(CAST(F_MONTO AS DECIMAL(18,2))) as monto_total'),
                    DB::raw('AVG(CAST(F_MONTO AS DECIMAL(18,2))) as ticket_promedio'),
                    DB::raw('MAX(FECHA_DT) as ultima_compra')
                )
                ->groupBy('c.id_Cliente', 'c.Nombre', 'c.apPaterno', 'c.apMaterno');
            
            // Aplicar filtro de cliente específico
            if ($searchCliente) {
                $query->where('c.id_Cliente', $searchCliente);
            }
            
            // Aplicar ordenamiento
            switch ($sortBy) {
                case 'monto_total':
                    $query->orderBy('monto_total', 'DESC');
                    break;
                case 'monto_total_asc':
                    $query->orderBy('monto_total', 'ASC');
                    break;
                case 'total_transacciones':
                    $query->orderBy('total_transacciones', 'DESC');
                    break;
                case 'total_transacciones_asc':
                    $query->orderBy('total_transacciones', 'ASC');
                    break;
                default:
                    $query->orderBy('monto_total', 'DESC');
            }
            
            if ($top !== 'todos') {
                $query->limit((int)$top);
            }
            
            $clientes = $query->get();
            
            return response()->json([
                'success' => true,
                'data' => $clientes,
                'filtros' => [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'top' => $top,
                    'sort_by' => $sortBy,
                    'indicacion_id' => $indicacionId
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en clientesData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * Listado de clientes con compras
     */
    public function clientes(Request $request)
    {
        // Obtener indicaciones SIEMPRE (para el select)
        $indicaciones = IndicacionTerapeutica::all();
        
        // Solo aplicar filtros si se ha enviado el formulario o hay parámetros
        $hasFilters = $request->hasAny(['top', 'sort_by', 'search_cliente', 'filtro_fecha', 'fecha_inicio', 'fecha_fin']);
        
        // Si no hay filtros, no mostrar datos
        if (!$hasFilters) {
            $clientes = collect(); // Colección vacía
            $fechaInicio = now()->startOfMonth()->format('Y-m-d');
            $fechaFin = now()->endOfMonth()->format('Y-m-d');
            $top = 50;
            $sortBy = 'monto_total';
            $searchCliente = null;
            
            return view('reportes.compras_cliente.clientes', compact(
                'clientes', 'fechaInicio', 'fechaFin', 'top', 'sortBy', 'searchCliente', 'indicaciones'
            ) + ['sortFields' => $this->validSortFields]);
        }
        
        // Aplicar filtros de fecha
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];
        
        // Obtener parámetros
        $top = $request->input('top', 50);
        $sortBy = $request->input('sort_by', 'monto_total');
        $searchCliente = $request->input('search_cliente');
        
        // IDs a ignorar (publico en general)
        $idsExcluir = ['0000000007295', '0000000004489'];
        
        $query = HistorialVenta::entreFechas($fechaInicio, $fechaFin)
            ->join('fp_central_matriz.dbo.catalogo_cliente_maestro as c', 'historial_ventas_matriz.IDCLIENTE', '=', 'c.idtarjetaclientefrecuente')
            ->whereNotIn('historial_ventas_matriz.IDCLIENTE', $idsExcluir)
            ->select(
                'c.id_Cliente',
                'c.Nombre',
                'c.apPaterno',
                'c.apMaterno',
                DB::raw('COUNT(DISTINCT F_NUMTICKE) as total_transacciones'),
                DB::raw('SUM(CAST(F_MONTO AS DECIMAL(18,2))) as monto_total'),
                DB::raw('AVG(CAST(F_MONTO AS DECIMAL(18,2))) as ticket_promedio'),
                DB::raw('MAX(FECHA_DT) as ultima_compra')
            )
            ->groupBy('c.id_Cliente', 'c.Nombre', 'c.apPaterno', 'c.apMaterno');
        
        // Verificar si hay datos en el rango de fechas
        $hasData = $query->get()->count() > 0;
        
        if (!$hasData) {
            $clientes = collect();
            return view('reportes.compras_cliente.clientes', compact(
                'clientes', 'fechaInicio', 'fechaFin', 'top', 'sortBy', 'searchCliente', 'indicaciones'
            ) + ['sortFields' => $this->validSortFields]);
        }
        
        // Aplicar búsqueda de cliente
        if ($searchCliente) {
            $query->having(DB::raw("CONCAT(c.Nombre, ' ', c.apPaterno, ' ', c.apMaterno)"), 'LIKE', "%{$searchCliente}%");
        }
        
        // Aplicar ordenamiento
        switch ($sortBy) {
            case 'monto_total':
                $query->orderBy('monto_total', 'DESC');
                break;
            case 'monto_total_asc':
                $query->orderBy('monto_total', 'ASC');
                break;
            case 'total_transacciones':
                $query->orderBy('total_transacciones', 'DESC');
                break;
            case 'total_transacciones_asc':
                $query->orderBy('total_transacciones', 'ASC');
                break;
            default:
                $query->orderBy('monto_total', 'DESC');
        }
        
        // Aplicar límite
        if ($top !== 'todos') {
            $query->limit((int)$top);
        }
        
        $clientes = $query->get();
        
        return view('reportes.compras_cliente.clientes', compact(
            'clientes', 'fechaInicio', 'fechaFin', 'top', 'sortBy', 'searchCliente', 'indicaciones'
        ) + ['sortFields' => $this->validSortFields]);
    }

    // ========================================
    // REPORTE DE COMPRAS POR CLIENTE
    // =========================================

    /**
     * Detalle de compras por cliente Reporte de Clientes
     */
    public function detalleCliente(Request $request, $clienteId)
    {
        // Obtener filtros de la URL
        $top = $request->input('top', 'todos');
        $sortBy = $request->input('sort_by', 'monto_total');
        $filtroFecha = $request->input('filtro_fecha', 'este_mes');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $searchCliente = $request->input('search_cliente');
        
        // Si las fechas son objetos, convertirlas a strings
        if (is_object($fechaInicio) && method_exists($fechaInicio, 'format')) {
            $fechaInicio = $fechaInicio->format('Y-m-d');
        }
        if (is_object($fechaFin) && method_exists($fechaFin, 'format')) {
            $fechaFin = $fechaFin->format('Y-m-d');
        }
        
        // Si no hay fechas, calcular según el filtro rápido
        if ((!$fechaInicio || !$fechaFin) && $filtroFecha && $filtroFecha !== 'personalizado') {
            $fechas = $this->getFechasFiltro($request);
            $fechaInicio = $fechas['inicio'] instanceof \Carbon\Carbon ? $fechas['inicio']->format('Y-m-d') : $fechas['inicio'];
            $fechaFin = $fechas['fin'] instanceof \Carbon\Carbon ? $fechas['fin']->format('Y-m-d') : $fechas['fin'];
        }
        
        // Asegurar que siempre sean strings
        $fechaInicio = is_string($fechaInicio) ? $fechaInicio : '';
        $fechaFin = is_string($fechaFin) ? $fechaFin : '';


        // Obtener datos del cliente
        $cliente = Cliente::findOrFail($clienteId);

        // Verificar si hay ventas en el rango de fechas
        $existeVenta = DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz')
            ->where('IDCLIENTE', $cliente->idtarjetaclientefrecuente)
            ->whereBetween('FECHA_DT', [$fechaInicio, $fechaFin])
            ->exists();
        
        if (!$existeVenta) {
            $gruposMadre = collect();
            $familias = collect();
            $totalGeneral = 0;
            
            return view('reportes.compras_cliente.detalle_cliente', compact(
                'cliente', 'familias', 'gruposMadre', 'totalGeneral', 'fechaInicio', 'fechaFin',
                'frecuenciaTexto', 'frecuenciaBadgeColor', 'top', 'sortBy', 'searchCliente'
            ));
        }

        // ============================================
        // 1. CONSULTA PARA GRUPOS MADRE (agrupados por id_grupo_madre)
        // ============================================
        $gruposMadre = DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz as h')
            ->join('fp_central_matriz.dbo.catalogo_maestro as cm', 'cm.EAN', '=', 'h.F_CODBAR')
            ->join('fp_central_matriz.dbo.grupos_familias as gf', 'gf.numfamilia', '=', 'cm.numFam')
            ->select(
                'gf.id_grupo_madre',
                'gf.descripciongrupomadre',
                DB::raw('COUNT(DISTINCT h.F_NUMTICKE) as transacciones'),
                DB::raw('COUNT(*) as cantidad_productos'),
                DB::raw('SUM(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_total')
            )
            ->where('h.IDCLIENTE', $cliente->idtarjetaclientefrecuente)
            ->whereBetween('h.FECHA_DT', [$fechaInicio, $fechaFin])
            ->whereNotNull('h.F_CODBAR')
            ->where('h.F_CODBAR', '!=', '')
            ->groupBy('gf.id_grupo_madre', 'gf.descripciongrupomadre')
            ->orderBy('monto_total', 'DESC')
            ->get();

        // ============================================
        // 2. CONSULTA PARA FAMILIAS (agrupadas por numfamilia)
        //    Solo para la gráfica de barras
        // ============================================
        $familias = DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz as h')
            ->join('fp_central_matriz.dbo.catalogo_maestro as cm', 'cm.EAN', '=', 'h.F_CODBAR')
            ->join('fp_central_matriz.dbo.grupos_familias as gf', 'gf.numfamilia', '=', 'cm.numFam')
            ->select(
                'gf.numfamilia as num_familia',
                'gf.descripcionfamilia as nombre_familia',
                DB::raw('SUM(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_total')
            )
            ->where('h.IDCLIENTE', $cliente->idtarjetaclientefrecuente)
            ->whereBetween('h.FECHA_DT', [$fechaInicio, $fechaFin])
            ->whereNotNull('h.F_CODBAR')
            ->where('h.F_CODBAR', '!=', '')
            ->groupBy('gf.numfamilia', 'gf.descripcionfamilia')
            ->orderBy('monto_total', 'DESC')
            ->get();

        $totalGeneral = $gruposMadre->sum('monto_total');

        // Calcular porcentaje para cada grupo madre
        foreach ($gruposMadre as $grupo) {
            $grupo->porcentaje = $totalGeneral > 0 ? ($grupo->monto_total / $totalGeneral) * 100 : 0;
        }

        // Obtener fechas de compras del cliente
        $fechasCompras = DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz')
            ->where('IDCLIENTE', $cliente->idtarjetaclientefrecuente)
            ->whereBetween('FECHA_DT', [$fechaInicio, $fechaFin])
            ->select('FECHA_DT')
            ->distinct()
            ->orderBy('FECHA_DT', 'asc')
            ->pluck('FECHA_DT')
            ->toArray();

        // Calcular frecuencia promedio de compra
        $frecuenciaCompra = null;
        $frecuenciaTexto = 'N/A';
        $frecuenciaBadgeColor = 'secondary';

        if (count($fechasCompras) >= 2) {
            $totalDias = 0;
            for ($i = 1; $i < count($fechasCompras); $i++) {
                $fechaAnterior = new \Carbon\Carbon($fechasCompras[$i - 1]);
                $fechaActual = new \Carbon\Carbon($fechasCompras[$i]);
                $totalDias += $fechaAnterior->diffInDays($fechaActual);
            }
            $frecuenciaCompra = round($totalDias / (count($fechasCompras) - 1), 1);
            
            if ($frecuenciaCompra <= 7) {
                $frecuenciaTexto = "Cada {$frecuenciaCompra} días <span class='badge bg-success'>Frecuente</span>";
                $frecuenciaBadgeColor = 'success';
            } elseif ($frecuenciaCompra <= 15) {
                $frecuenciaTexto = "Cada {$frecuenciaCompra} días <span class='badge bg-warning text-dark'>Regular</span>";
                $frecuenciaBadgeColor = 'warning';
            } elseif ($frecuenciaCompra <= 30) {
                $frecuenciaTexto = "Cada {$frecuenciaCompra} días <span class='badge bg-info'>Ocasional</span>";
                $frecuenciaBadgeColor = 'info';
            } else {
                $frecuenciaTexto = "Cada {$frecuenciaCompra} días <span class='badge bg-secondary'>Poco Frecuente</span>";
                $frecuenciaBadgeColor = 'secondary';
            }
        } elseif (count($fechasCompras) == 1) {
            $frecuenciaTexto = 'Primera compra en el período';
            $frecuenciaBadgeColor = 'info';
        } else {
            $frecuenciaTexto = 'Sin compras en el período';
            $frecuenciaBadgeColor = 'secondary';
        }

        return view('reportes.compras_cliente.detalle_cliente', compact(
            'cliente', 'familias', 'gruposMadre', 'totalGeneral', 'fechaInicio', 'fechaFin',
            'frecuenciaTexto', 'frecuenciaBadgeColor', 'top', 'sortBy', 'searchCliente'
        ));
    }

    /**
     * Listado de productos por cliente y familia Reporte de Clientes
     */
    public function detalleGrupoMadre(Request $request, $clienteId, $grupoMadreId)
    {
        // Obtener filtros de la URL
        $top = $request->input('top', 'todos');
        $sortBy = $request->input('sort_by', 'monto_total');
        $filtroFecha = $request->input('filtro_fecha', 'este_mes');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $searchCliente = $request->input('search_cliente');
        $indicacionId = $request->input('indicacion_id');
        
        // Si no hay fechas, calcular según el filtro rápido
        if ((!$fechaInicio || !$fechaFin) && $filtroFecha && $filtroFecha !== 'personalizado') {
            $fechas = $this->getFechasFiltro($request);
            // Convertir a string Y-m-d si son objetos Carbon
            $fechaInicio = $fechas['inicio'] instanceof \Carbon\Carbon ? $fechas['inicio']->format('Y-m-d') : $fechas['inicio'];
            $fechaFin = $fechas['fin'] instanceof \Carbon\Carbon ? $fechas['fin']->format('Y-m-d') : $fechas['fin'];
        }
        
        // Asegurar que siempre sean strings
        $fechaInicio = is_string($fechaInicio) ? $fechaInicio : ($fechaInicio instanceof \Carbon\Carbon ? $fechaInicio->format('Y-m-d') : '');
        $fechaFin = is_string($fechaFin) ? $fechaFin : ($fechaFin instanceof \Carbon\Carbon ? $fechaFin->format('Y-m-d') : '');

        // Obtener datos del cliente
        $cliente = Cliente::findOrFail($clienteId);

        // Obtener información del grupo madre
        $grupoMadre = DB::connection('sqlsrvM')
            ->table('grupos_familias')
            ->where('id_grupo_madre', $grupoMadreId)
            ->select('id_grupo_madre', 'descripciongrupomadre')
            ->first();

        if (!$grupoMadre) {
            abort(404, 'Grupo madre no encontrado');
        }

        // Obtener productos de todas las familias del grupo madre
        $productos = DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz as h')
            ->join('fp_central_matriz.dbo.catalogo_maestro as cm', 'cm.EAN', '=', 'h.F_CODBAR')
            ->join('fp_central_matriz.dbo.grupos_familias as gf', 'gf.numfamilia', '=', 'cm.numFam')
            ->select(
                'cm.EAN as ean',
                'cm.DESCRIPCION as descripcion',
                'gf.descripcionfamilia as nombre_familia',
                DB::raw('COUNT(DISTINCT h.F_NUMTICKE) as transacciones'),
                DB::raw('COUNT(*) as cantidad_vendida'),
                DB::raw('SUM(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_total'),
                DB::raw('MAX(h.FECHA_DT) as ultima_venta')
            )
            ->where('h.IDCLIENTE', $cliente->idtarjetaclientefrecuente)
            ->where('gf.id_grupo_madre', $grupoMadreId)
            ->whereBetween('h.FECHA_DT', [$fechaInicio, $fechaFin])
            ->whereNotNull('h.F_CODBAR')
            ->where('h.F_CODBAR', '!=', '')
            ->groupBy('cm.EAN', 'cm.DESCRIPCION', 'gf.descripcionfamilia')
            ->orderBy('monto_total', 'DESC')
            ->get();

        $totalGeneral = $productos->sum('monto_total');

        return view('reportes.compras_cliente.detalle_grupo_madre', compact(
            'cliente', 'grupoMadre', 'productos', 'totalGeneral', 'fechaInicio',
            'fechaFin', 'top', 'sortBy', 'filtroFecha', 'indicacionId', 'searchCliente'
        ));
    }

    /**
     * Top clientes
     */
    public function topClientes(Request $request)
    {
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];

        $top = $request->input('top', 10);

        $clientes = HistorialVenta::getResumenClientes($fechaInicio, $fechaFin, $top);

        return view('reportes.compras_cliente.top_clientes', compact(
            'clientes', 'fechaInicio', 'fechaFin', 'top'
        ));
    }

    /**
     * Top productos
     */
    public function topProductos(Request $request)
    {
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];

        $top = $request->input('top', 10);
        $orden = $request->input('orden', 'monto'); // monto o cantidad

        $productos = $this->getTopProductos($fechaInicio, $fechaFin, $top, $orden);

        return view('reportes.compras_cliente.top_productos', compact(
            'productos', 'fechaInicio', 'fechaFin', 'top', 'orden'
        ));
    }

    // ========================================
    // REPORTE DE COTIZACIONES POR CLIENTE
    // =========================================

    /**
     * Cotizaciones por cliente
     */
    public function cotizacionesCliente(Request $request)
    {
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];

        $clienteId = $request->input('cliente_id');
        $searchCliente = $request->input('search_cliente');

        $query = Cotizacion::with('cliente', 'fase')
            ->where('activo', 1)
            ->where('enviado', 1)
            ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin]);

        if ($clienteId) {
            $query->where('id_cliente', $clienteId);
        }

        if ($searchCliente) {
            $query->whereHas('cliente', function($q) use ($searchCliente) {
                $q->whereRaw("CONCAT(Nombre, ' ', apPaterno, ' ', apMaterno) LIKE ?", ["%{$searchCliente}%"]);
            });
        }

        $cotizaciones = $query->orderBy('fecha_creacion', 'DESC')->paginate(20);

        return view('reportes.compras_cliente.cotizaciones_cliente', compact(
            'cotizaciones', 'fechaInicio', 'fechaFin', 'clienteId', 'searchCliente'
        ));
    }

    // ========================================
    // REPORTE DE PEDIDOS CLIENTE
    // =========================================

    /**
     * Pedidos por Cliente (reporte de cotizaciones convertidas a pedido)
     */
    public function pedidosCliente(Request $request)
    {
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];

        $top = $request->input('top', 10);

        $resumen = Cotizacion::with('cliente')
            ->where('activo', 1)
            ->where('enviado', 1)
            ->where('es_pedido', 1)
            ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin])
            ->select(
                'id_cliente',
                DB::raw('COUNT(*) as total_pedidos'),
                DB::raw('SUM(importe_total) as monto_total'),
                DB::raw('AVG(importe_total) as monto_promedio')
            )
            ->groupBy('id_cliente')
            ->orderBy('monto_total', 'DESC')
            ->limit($top)
            ->get();

        return view('reportes.pedidos_cliente.index', compact(
            'resumen', 'fechaInicio', 'fechaFin', 'top'
        ));
    }

    public function pedidosClienteData(Request $request)
    {
        try {
            $fechas = $this->getFechasFiltro($request);
            $fechaInicio = $fechas['inicio'];
            $fechaFin = $fechas['fin'];
            
            $top = $request->input('top', 10);
            $sortBy = $request->input('sort_by', 'monto_total');
            $clienteId = $request->input('cliente_id');
            
            $query = Cotizacion::with('cliente')
                ->where('activo', 1)
                ->where('enviado', 1)
                ->where('es_pedido', 1)
                ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin]);
            
            if ($clienteId && is_numeric($clienteId) && $clienteId > 0) {
                $query->where('id_cliente', (int) $clienteId);
            }
            
            $resumen = $query->select(
                    'id_cliente',
                    DB::raw('COUNT(*) as total_pedidos'),
                    DB::raw('SUM(importe_total) as monto_total'),
                    DB::raw('AVG(importe_total) as monto_promedio')
                )
                ->groupBy('id_cliente');
            
            // Ordenamiento
            switch ($sortBy) {
                case 'monto_promedio':
                    $resumen->orderBy('monto_promedio', 'DESC');
                    break;
                case 'monto_promedio_asc':
                    $resumen->orderBy('monto_promedio', 'ASC');
                    break;
                case 'total_pedidos':
                    $resumen->orderBy('total_pedidos', 'DESC');
                    break;
                case 'total_pedidos_asc':
                    $resumen->orderBy('total_pedidos', 'ASC');
                    break;
                default:
                    $resumen->orderBy('monto_total', 'DESC');
            }
            
            if ($top !== 'todos' && is_numeric($top)) {
                $resumen->limit((int) $top);
            }
            
            $resultados = $resumen->get()->map(function($item) {
                $cliente = Cliente::find($item->id_cliente);
                return [
                    'id_Cliente' => $item->id_cliente,
                    'Nombre' => $cliente->Nombre ?? 'N/A',
                    'apPaterno' => $cliente->apPaterno ?? '',
                    'apMaterno' => $cliente->apMaterno ?? '',
                    'total_pedidos' => $item->total_pedidos,
                    'monto_total' => floatval($item->monto_total),
                    'monto_promedio' => floatval($item->monto_promedio),
                ];
            });
            
            // Siempre devolver filtros, incluso si no hay resultados
            return response()->json([
                'success' => true,
                'data' => $resultados,
                'filtros' => [
                    'fecha_inicio' => $fechaInicio instanceof \Carbon\Carbon ? $fechaInicio->format('Y-m-d') : $fechaInicio,
                    'fecha_fin' => $fechaFin instanceof \Carbon\Carbon ? $fechaFin->format('Y-m-d') : $fechaFin,
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en pedidosClienteData: ' . $e->getMessage());
            
            // En caso de error, también devolver filtros
            $fechas = $this->getFechasFiltro($request);
            $fechaInicio = $fechas['inicio'] ?? date('Y-m-d');
            $fechaFin = $fechas['fin'] ?? date('Y-m-d');
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los datos: ' . $e->getMessage(),
                'data' => [],
                'filtros' => [
                    'fecha_inicio' => $fechaInicio instanceof \Carbon\Carbon ? $fechaInicio->format('Y-m-d') : $fechaInicio,
                    'fecha_fin' => $fechaFin instanceof \Carbon\Carbon ? $fechaFin->format('Y-m-d') : $fechaFin,
                ]
            ], 500);
        }
    }

    /**
     * Detalle de pedidos por cliente
     */
    public function detallePedidoCliente(Request $request, $clienteId)
    {
        if (!auth()->user()->puede('ventas', 'pedidos_anticipo', 'ver')) {
            abort(403);
        }
        
        $cliente = Cliente::findOrFail($clienteId);
        
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];
        $searchCliente = $request->input('search_cliente');
        
        return view('reportes.pedidos_cliente.detalle_pedido', compact(
            'cliente', 'fechaInicio', 'fechaFin', 'searchCliente'
        ));
    }

    /**
     * Obtener datos del cliente (pedidos y grupos madre)
     */
    public function detallePedidoClienteData(Request $request, $clienteId)
    {
        try {
            $fechas = $this->getFechasFiltro($request);
            $fechaInicio = $fechas['inicio'];
            $fechaFin = $fechas['fin'];
            
            // 1. Pedidos del cliente (usando orden_pedido)
            $pedidos = OrdenPedido::with('cotizacion')
                ->whereHas('cotizacion', function($q) use ($clienteId) {
                    $q->where('id_cliente', $clienteId);
                })
                ->whereBetween('fecha_pedido', [$fechaInicio, $fechaFin])
                ->where('activo', 1)
                ->orderBy('fecha_pedido', 'DESC')
                ->get()
                ->map(function($pedido) {
                    $estadoMap = [
                        1 => ['nombre' => 'Pendiente', 'color' => 'warning'],
                        2 => ['nombre' => 'En proceso', 'color' => 'info'],
                        3 => ['nombre' => 'Completado', 'color' => 'success'],
                        4 => ['nombre' => 'Cancelado', 'color' => 'danger']
                    ];
                    $estado = $estadoMap[$pedido->status] ?? ['nombre' => 'Desconocido', 'color' => 'secondary'];
                    
                    return [
                        'id_pedido' => $pedido->id_pedido,
                        'folio_pedido' => $pedido->folio_pedido,
                        'fecha_pedido' => $pedido->fecha_pedido,
                        'importe_total' => $pedido->importe_total,
                        'estado_nombre' => $estado['nombre'],
                        'estado_color' => $estado['color']
                    ];
                });
            
            // 2. Datos para gráfica de grupos madre
            try {
                $gruposMadre = DB::connection('sqlsrv')
                    ->table('orden_pedido_detalle as opd')
                    ->join('orden_pedido as op', 'opd.id_pedido', '=', 'op.id_pedido')
                    ->join('crm_cotizaciones as c', 'op.id_cotizacion', '=', 'c.id_cotizacion')
                    ->join('fp_central_matriz.dbo.catalogo_maestro as cm', 'cm.EAN', '=', 'opd.ean')
                    ->join('fp_central_matriz.dbo.grupos_familias as gf', 'gf.numfamilia', '=', 'cm.numFam')
                    ->where('c.id_cliente', $clienteId)
                    ->whereBetween('op.fecha_pedido', [$fechaInicio, $fechaFin])
                    ->where('op.activo', 1)
                    ->select(
                        'gf.id_grupo_madre',
                        'gf.descripciongrupomadre',
                        DB::raw('SUM(opd.importe) as monto_total')
                    )
                    ->groupBy('gf.id_grupo_madre', 'gf.descripciongrupomadre')
                    ->orderBy('monto_total', 'DESC')
                    ->get();
            } catch (\Exception $e) {
                \Log::error('Error en consulta de grupos madre: ' . $e->getMessage());
                $gruposMadre = collect();
            }
            
            $totalGeneral = $gruposMadre->sum('monto_total');
            
            return response()->json([
                'success' => true,
                'data' => [
                    'pedidos' => $pedidos,
                    'gruposMadre' => $gruposMadre,
                    'totalGeneral' => $totalGeneral,
                    'resumen' => [
                        'total_pedidos' => $pedidos->count(),
                        'importe_total' => $pedidos->sum('importe_total'),
                        'ticket_promedio' => $pedidos->avg('importe_total'),
                        'ultimo_pedido' => $pedidos->first()['fecha_pedido'] ?? null
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en detallePedidoClienteData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vista completa de productos de un pedido
     */
    public function vistaProductosPedido(Request $request, $clienteId, $pedidoId)
    {
        if (!auth()->user()->puede('ventas', 'pedidos_anticipo', 'ver')) {
            abort(403);
        }
        
        $cliente = Cliente::findOrFail($clienteId);
        
        $pedido = OrdenPedido::where('id_pedido', $pedidoId)
            ->where('activo', 1)
            ->firstOrFail();
        
        // Obtener productos del pedido
        $productos = OrdenPedidoDetalle::where('id_pedido', $pedidoId)
            ->where('se_elimino', '!=', 1)
            ->get();
        
        foreach ($productos as $producto) {
            $esExterno = str_starts_with($producto->ean, 'T');
            
            if ($esExterno) {
                $tmpProducto = TmpCatalogo::where('ean', $producto->ean)->first();
                $producto->descripcion = $tmpProducto?->descripcion ?? 'Producto externo';
            } else {
                $productoInfo = CatalogoGeneral::where('ean', $producto->ean)->first();
                $producto->descripcion = $productoInfo?->descripcion ?? 'Producto no encontrado';
            }
        }
        
        // Capturar filtros para regresar
        $filtroFecha = $request->input('filtro_fecha', 'este_mes');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $top = $request->input('top', 'todos');
        $sortBy = $request->input('sort_by', 'monto_total');
        $searchCliente = $request->input('search_cliente');
        
        return view('reportes.pedidos_cliente.productos', compact(
            'cliente', 'pedido', 'productos', 'filtroFecha', 'fechaInicio', 'fechaFin', 'top', 'sortBy', 'searchCliente'
        ));
    }

    /**
     * Exportar reporte de Pedidos por Cliente
     */
    public function exportarPedidosCliente(Request $request)
    {
        try {
            $tipo = $request->input('tipo', 'pdf');
            $fechas = $this->getFechasFiltro($request);
            $fechaInicio = $fechas['inicio'];
            $fechaFin = $fechas['fin'];
            
            $top = $request->input('top', 10);
            $sortBy = $request->input('sort_by', 'monto_total');
            $clienteId = $request->input('cliente_id');
            
            $query = Cotizacion::with('cliente')
                ->where('activo', 1)
                ->where('enviado', 1)
                ->where('es_pedido', 1)
                ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin]);
            
            if ($clienteId) {
                $cliente = Cliente::find($clienteId);
                $clienteNombre = $cliente ? $cliente->nombre_completo : null;
                $query->where('id_cliente', $clienteId);
            }
            
            $resultados = $query->select(
                    'id_cliente',
                    DB::raw('COUNT(*) as total_pedidos'),
                    DB::raw('SUM(importe_total) as monto_total'),
                    DB::raw('AVG(importe_total) as monto_promedio')
                )
                ->groupBy('id_cliente');
            
            // Ordenamiento
            switch ($sortBy) {
                case 'monto_promedio':
                    $resultados->orderBy('monto_promedio', 'DESC');
                    break;
                case 'monto_promedio_asc':
                    $resultados->orderBy('monto_promedio', 'ASC');
                    break;
                case 'total_pedidos':
                    $resultados->orderBy('total_pedidos', 'DESC');
                    break;
                case 'total_pedidos_asc':
                    $resultados->orderBy('total_pedidos', 'ASC');
                    break;
                default:
                    $resultados->orderBy('monto_total', 'DESC');
            }
            
            if ($top !== 'todos' && is_numeric($top)) {
                $resultados->limit((int) $top);
            }
            
            $data = $resultados->get()->map(function($item) {
                return [
                    'cliente_nombre' => $item->cliente->nombre_completo ?? 'N/A',
                    'total_pedidos' => $item->total_pedidos,
                    'monto_total' => floatval($item->monto_total),
                    'monto_promedio' => floatval($item->monto_promedio),
                ];
            })->toArray();
            
            // Preparar filtros
            $filtros = [
                'top' => $top,
                'sort_by' => $sortBy,
                'fecha_inicio' => $fechaInicio instanceof \Carbon\Carbon ? $fechaInicio->format('d/m/Y') : $fechaInicio,
                'fecha_fin' => $fechaFin instanceof \Carbon\Carbon ? $fechaFin->format('d/m/Y') : $fechaFin,
                'cliente' => $clienteNombre ?? null,
            ];
            
            if ($tipo === 'excel') {
                // Usar el método de exportación a Excel
                return $this->exportarPedidosClienteExcel($data, $filtros);
            }
            
            // Ruta corregida: incluye la subcarpeta "pdf"
            $pdf = Pdf::loadView('reportes.pedidos_cliente.pdf.pedidos_cliente', compact('data', 'filtros'));
            $pdf->setPaper('a4', 'portrait');
            
            return $pdf->download('pedidos_por_cliente_' . now()->format('Ymd_His') . '.pdf');
            
        } catch (\Exception $e) {
            \Log::error('Error en exportarPedidosCliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al exportar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar Pedidos por Cliente a Excel
     */
    private function exportarPedidosClienteExcel($data, $filtros)
    {
        if (!class_exists(Excel::class)) {
            throw new \Exception('La librería de Excel no está instalada. Ejecute: composer require maatwebsite/excel');
        }
        
        $export = new \App\Exports\PedidosClienteExport($data, $filtros);
        return Excel::download($export, 'pedidos_por_cliente_' . date('Y-m-d_His') . '.xlsx');
    }

    /**
     * Frecuencia de compra por cliente para Reporte de Clientes
     */
    public function frecuenciaCompra(Request $request)
    {
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];
        
        $top = $request->input('top', 50);
        $searchCliente = $request->input('search_cliente');
        
        $clientes = DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz as h')
            ->join('fp_central_matriz.dbo.catalogo_cliente_maestro as c', 'h.IDCLIENTE', '=', 'c.idtarjetaclientefrecuente')
            ->select(
                'c.id_Cliente',
                'c.Nombre',
                'c.apPaterno',
                'c.apMaterno',
                DB::raw('COUNT(DISTINCT h.F_NUMTICKE) as total_compras'),
                DB::raw('COUNT(DISTINCT CAST(h.FECHA_DT AS DATE)) as dias_con_compra'),
                DB::raw('DATEDIFF(day, MIN(h.FECHA_DT), MAX(h.FECHA_DT)) as dias_entre_compras'),
                DB::raw('MIN(h.FECHA_DT) as primera_compra'),
                DB::raw('MAX(h.FECHA_DT) as ultima_compra'),
                DB::raw('SUM(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_total')
            )
            ->whereBetween('h.FECHA_DT', [$fechaInicio, $fechaFin])
            ->groupBy('c.id_Cliente', 'c.Nombre', 'c.apPaterno', 'c.apMaterno')
            // ->having('total_compras', '>', 1);  // NO RENOMBRAR DESDE LARAVEL (Por eso no mostraba resultados en la consulta)
            ->havingRaw('COUNT(DISTINCT h.F_NUMTICKE) > 1');
        
        if ($searchCliente) {
            $clientes->having(DB::raw("CONCAT(c.Nombre, ' ', c.apPaterno, ' ', c.apMaterno)"), 'LIKE', "%{$searchCliente}%");
        }
        
        if ($top !== 'todos') {
            $clientes->limit((int)$top);
        }
        
        $clientes = $clientes->orderBy('total_compras', 'DESC')->get();
        
        // Calcular frecuencia promedio
        foreach ($clientes as $cliente) {
            if ($cliente->total_compras > 1 && $cliente->dias_entre_compras > 0) {
                $cliente->frecuencia_promedio = $cliente->dias_entre_compras / ($cliente->total_compras - 1);
            } else {
                $cliente->frecuencia_promedio = 0;
            }
        }
        
        return view('reportes.compras_cliente.frecuencia_compra', compact(
            'clientes', 'fechaInicio', 'fechaFin', 'top', 'searchCliente'
        ));
    }

    // ========================================
    // REPORTE DE MONTOS PROMEDIO DE COMPRA POR CLIENTE
    // =========================================

    /**
     * Muestra la vista de montos promedio
     */
    public function montosPromedio(Request $request)
    {
        // Obtener filtros de la URL
        $filtroFecha = $request->input('filtro_fecha', 'este_ano');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $top = $request->input('top', 'todos');
        $sortBy = $request->input('sort_by', 'monto_promedio');
        $searchCliente = $request->input('search_cliente');
        
        // Si no hay fechas, calcular según el filtro rápido
        if ((!$fechaInicio || !$fechaFin) && $filtroFecha && $filtroFecha !== 'personalizado') {
            $fechas = $this->getFechasFiltro($request);
            $fechaInicio = $fechas['inicio'];
            $fechaFin = $fechas['fin'];
        }
        
        return view('reportes.montos_promedio_compra.index', compact(
            'filtroFecha', 'fechaInicio', 'fechaFin', 'top', 'sortBy', 'searchCliente'
        ));
    }

    /**
     * Obtiene los datos vía AJAX para el reporte de montos promedio
     */
    public function montosPromedioData(Request $request)
    {
        try {
            $fechas = $this->getFechasFiltro($request);
            $fechaInicio = $fechas['inicio'];
            $fechaFin = $fechas['fin'];
            
            $top = $request->input('top', 50);
            $sortBy = $request->input('sort_by', 'monto_promedio');
            $searchCliente = $request->input('search_cliente');
            
            $idsExcluir = ['0000000007295', '0000000004489'];
            
            if (!$fechaInicio || !$fechaFin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar un período de fechas',
                    'data' => []
                ]);
            }
            
            $query = DB::connection('sqlsrvV')
                ->table('historial_ventas_matriz as hv')
                ->join('fp_central_matriz.dbo.catalogo_cliente_maestro as c', 'c.idtarjetaclientefrecuente', '=', 'hv.IDCLIENTE')
                ->whereNotIn('hv.IDCLIENTE', $idsExcluir)
                ->whereBetween('hv.FECHA_DT', [$fechaInicio, $fechaFin])
                ->select(
                    'c.id_Cliente',
                    'c.Nombre',
                    'c.apPaterno',
                    'c.apMaterno',
                    'c.idtarjetaclientefrecuente',
                    DB::raw('COUNT(DISTINCT hv.F_NUMTICKE) as total_compras'),
                    DB::raw('SUM(CAST(hv.F_MONTO AS DECIMAL(18,2))) as monto_total'),
                    DB::raw('(SUM(CAST(hv.F_MONTO AS DECIMAL(18,2))) / COUNT(DISTINCT hv.F_NUMTICKE)) as monto_promedio'),  // ← Corregido
                    DB::raw('MIN(hv.FECHA_DT) as fecha_primera_compra'),
                    DB::raw('MAX(hv.FECHA_DT) as fecha_ultima_compra')
                )
                ->groupBy('c.id_Cliente', 'c.Nombre', 'c.apPaterno', 'c.apMaterno', 'c.idtarjetaclientefrecuente');
            
            if ($searchCliente) {
                $query->where('c.id_Cliente', $searchCliente);
            }
            
            switch ($sortBy) {
                case 'monto_promedio':
                    $query->orderBy('monto_promedio', 'DESC');
                    break;
                case 'monto_promedio_asc':
                    $query->orderBy('monto_promedio', 'ASC');
                    break;
                case 'total_compras':
                    $query->orderBy('total_compras', 'DESC');
                    break;
                case 'total_compras_asc':
                    $query->orderBy('total_compras', 'ASC');
                    break;
                default:
                    $query->orderBy('monto_promedio', 'DESC');
            }
            
            if ($top !== 'todos') {
                $query->limit((int)$top);
            }
            
            $clientes = $query->get();
            
            // Calcular primera y última compra con montos
            foreach ($clientes as $cliente) {
                $tarjetaId = $cliente->idtarjetaclientefrecuente ?? $this->getTarjetaByClienteId($cliente->id_Cliente);
                
                if ($tarjetaId && $cliente->fecha_primera_compra) {
                    $primeraCompra = DB::connection('sqlsrvV')
                        ->table('historial_ventas_matriz')
                        ->where('IDCLIENTE', $tarjetaId)
                        ->where('FECHA_DT', $cliente->fecha_primera_compra)
                        ->select(DB::raw('SUM(CAST(F_MONTO AS DECIMAL(18,2))) as monto'))
                        ->first();
                    $cliente->monto_primera_compra = $primeraCompra->monto ?? 0;
                } else {
                    $cliente->monto_primera_compra = 0;
                }
                
                if ($tarjetaId && $cliente->fecha_ultima_compra) {
                    $ultimaCompra = DB::connection('sqlsrvV')
                        ->table('historial_ventas_matriz')
                        ->where('IDCLIENTE', $tarjetaId)
                        ->where('FECHA_DT', $cliente->fecha_ultima_compra)
                        ->select(DB::raw('SUM(CAST(F_MONTO AS DECIMAL(18,2))) as monto'))
                        ->first();
                    $cliente->monto_ultima_compra = $ultimaCompra->monto ?? 0;
                } else {
                    $cliente->monto_ultima_compra = 0;
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $clientes,
                'filtros' => [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'top' => $top,
                    'sort_by' => $sortBy
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en montosPromedioData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * Obtiene el detalle de compras de un cliente para el reporte de Montos Promedios de Compra
     */
    public function detalleComprasCliente(Request $request, $clienteId)
    {
        try {
            // Obtener filtros de la URL PRIMERO
            $top = $request->input('top', 'todos');
            $sortBy = $request->input('sort_by', 'monto_promedio');
            $filtroFecha = $request->input('filtro_fecha', 'este_ano');
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');
            $searchCliente = $request->input('search_cliente');
            
            // Si no hay fechas en la URL, calcular según el filtro rápido
            if ((!$fechaInicio || !$fechaFin) && $filtroFecha && $filtroFecha !== 'personalizado') {
                $fechas = $this->getFechasFiltro($request);
                $fechaInicio = $fechas['inicio'];
                $fechaFin = $fechas['fin'];
            }
            
            $cliente = Cliente::findOrFail($clienteId);
            
            // Si no hay fechas, mostrar mensaje
            if (!$fechaInicio || !$fechaFin) {
                $compras = collect();
                $totalCompras = 0;
                $montoTotal = 0;
                $montoPromedio = 0;
                
                return view('reportes.montos_promedio_compra.detalle_montos', compact(
                    'cliente', 'compras', 'fechaInicio', 'fechaFin', 'totalCompras', 'montoTotal', 'montoPromedio',
                    'top', 'sortBy', 'searchCliente', 'filtroFecha'
                ));
            }
            
            $compras = DB::connection('sqlsrvV')
                ->table('historial_ventas_matriz')
                ->where('IDCLIENTE', $cliente->idtarjetaclientefrecuente)
                ->whereBetween('FECHA_DT', [$fechaInicio, $fechaFin])
                ->select(
                    'FECHA_DT as fecha',
                    'F_NUMTICKE as ticket',
                    DB::raw('SUM(CAST(F_MONTO AS DECIMAL(18,2))) as monto')
                )
                ->groupBy('FECHA_DT', 'F_NUMTICKE')
                ->orderBy('FECHA_DT', 'DESC')
                ->get();
            
            $acumulado = 0;
            foreach ($compras as $compra) {
                $acumulado += $compra->monto;
                $compra->acumulado = $acumulado;
            }
            
            $totalCompras = $compras->count();
            $montoTotal = $compras->sum('monto');
            $montoPromedio = $totalCompras > 0 ? $montoTotal / $totalCompras : 0;
            
            return view('reportes.montos_promedio_compra.detalle_montos', compact(
                'cliente', 'compras', 'fechaInicio', 'fechaFin', 'totalCompras', 'montoTotal', 'montoPromedio',
                'top', 'sortBy', 'searchCliente', 'filtroFecha'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en detalleComprasCliente: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar el detalle de compras');
        }
    }

    private function getTarjetaByClienteId($clienteId)
    {
        $cliente = Cliente::find($clienteId);
        return $cliente ? $cliente->idtarjetaclientefrecuente : null;
    }

    /**
     * Exportar a Excel
     */
    public function exportarExcel(Request $request)
    {
        $tipo = $request->input('tipo', 'clientes');
        $fechas = $this->getFechasFiltro($request);
        
        // Obtener filtros adicionales
        $top = $request->input('top', 'todos');
        $sortBy = $request->input('sort_by', 'monto_total');
        $searchCliente = $request->input('search_cliente');
        $indicacionId = $request->input('indicacion_id');
        
        switch ($tipo) {
            case 'clientes':
                // Obtener clientes con todos los filtros aplicados
                $clientes = HistorialVenta::getResumenClientes(
                    $fechas['inicio'], 
                    $fechas['fin'], 
                    ($top !== 'todos') ? (int)$top : null,
                    $searchCliente,
                    $indicacionId
                );
                
                $fechaActual = now()->format('Ymd_His');
                
                return Excel::download(
                    new VentasClienteExport($clientes, $fechas, $top, $sortBy, $searchCliente, $indicacionId), 
                    "reporte_clientes_{$fechaActual}.xlsx"
                );
                
            case 'top-clientes':
                $top = $request->input('top', 10);
                $fechaActual = now()->format('Ymd_His');
                
                // Aplicar filtros también al top de clientes
                $clientes = HistorialVenta::getResumenClientes(
                    $fechas['inicio'], 
                    $fechas['fin'], 
                    $top,
                    $searchCliente,
                    $indicacionId
                );
                
                return Excel::download(
                    new TopClientesExport($clientes, $fechas, $top, $searchCliente, $indicacionId), 
                    "top_clientes_{$fechaActual}.xlsx"
                );
                
            case 'top-productos':
                $top = $request->input('top', 10);
                $orden = $request->input('orden', 'monto');
                $fechaActual = now()->format('Ymd_His');
                
                $productos = $this->getTopProductos($fechas['inicio'], $fechas['fin'], $top, $orden);
                
                return Excel::download(
                    new TopProductosExport($productos, $fechas, $top, $orden), 
                    "top_productos_{$fechaActual}.xlsx"
                );
                
            default:
                return back()->with('error', 'Tipo de exportación no válido');
        }
    }

    /**
     * Exportar a PDF
     */
    public function exportarPdf(Request $request)
    {
        $tipo = $request->input('tipo', 'clientes');
        $fechas = $this->getFechasFiltro($request);
        
        // Obtener filtros adicionales
        $top = $request->input('top', 'todos');
        $sortBy = $request->input('sort_by', 'monto_total');
        $searchCliente = $request->input('search_cliente');
        $indicacionId = $request->input('indicacion_id');
        
        switch ($tipo) {
            case 'clientes':
                // Obtener clientes con todos los filtros aplicados
                $clientes = HistorialVenta::getResumenClientes(
                    $fechas['inicio'], 
                    $fechas['fin'], 
                    ($top !== 'todos') ? (int)$top : null,
                    $searchCliente,
                    $indicacionId
                );
                
                $pdf = Pdf::loadView('reportes.compras_cliente.pdf.clientes', compact('clientes', 'fechas', 'top', 'sortBy', 'searchCliente', 'indicacionId'));
                return $pdf->download('reporte_clientes_' . now()->format('Ymd_His') . '.pdf');
                
            case 'top-clientes':
                $top = $request->input('top', 10);
                
                $clientes = HistorialVenta::getResumenClientes(
                    $fechas['inicio'], 
                    $fechas['fin'], 
                    $top,
                    $searchCliente,
                    $indicacionId
                );
                
                $pdf = Pdf::loadView('reportes.compras_cliente.pdf.top_clientes', compact('clientes', 'fechas', 'top', 'searchCliente', 'indicacionId'));
                return $pdf->download('top_clientes_' . now()->format('Ymd_His') . '.pdf');
                
            case 'top-productos':
                $top = $request->input('top', 10);
                $orden = $request->input('orden', 'monto');
                
                $productos = $this->getTopProductos($fechas['inicio'], $fechas['fin'], $top, $orden);
                
                $pdf = Pdf::loadView('reportes.compras_cliente.pdf.top_productos', compact('productos', 'fechas', 'top', 'orden'));
                return $pdf->download('top_productos_' . now()->format('Ymd_His') . '.pdf');
                
            default:
                return back()->with('error', 'Tipo de exportación no válido');
        }
    }

    public function exportarMontosPromedioExcel(Request $request)
    {
        $fechas = $this->getFechasFiltro($request);
        
        // Obtener los mismos datos que en montosPromedioData
        $response = $this->montosPromedioData($request);
        $data = json_decode($response->getContent(), true);
        
        // Asegurar que los datos sean objetos
        $clientes = collect($data['data'] ?? [])->map(function($item) {
            return (object) $item;
        });
        
        $fechaActual = now()->format('Ymd_His');
        
        return Excel::download(
            new MontosPromedioExport($clientes, $fechas),
            "montos_promedio_{$fechaActual}.xlsx"
        );
    }

    public function exportarMontosPromedioPdf(Request $request)
    {
        $fechas = $this->getFechasFiltro($request);
        
        // Obtener los mismos datos que en montosPromedioData
        $response = $this->montosPromedioData($request);
        $data = json_decode($response->getContent(), true);
        
        // Asegurar que los datos sean objetos
        $clientes = collect($data['data'] ?? [])->map(function($item) {
            return (object) $item;
        });
        
        $pdf = Pdf::loadView('reportes.montos_promedio_compra.pdf.montos_promedio', compact('clientes', 'fechas'));
        
        return $pdf->download("montos_promedio_" . now()->format('Ymd_His') . ".pdf");
    }

    /**
     * Obtiene los productos de un ticket específico para un cliente (Monto Promedio Compra)
     */
    public function getProductosPorTicket(Request $request, $clienteId, $ticket)
    {
        try {
            $fechas = $this->getFechasFiltro($request);
            $fechaInicio = $fechas['inicio'];
            $fechaFin = $fechas['fin'];
            
            $cliente = Cliente::findOrFail($clienteId);
            
            // Obtener la fecha del ticket
            $fechaTicket = DB::connection('sqlsrvV')
                ->table('historial_ventas_matriz')
                ->where('IDCLIENTE', $cliente->idtarjetaclientefrecuente)
                ->where('F_NUMTICKE', $ticket)
                ->select('FECHA_DT')
                ->first();
            
            $productos = DB::connection('sqlsrvV')
                ->table('historial_ventas_matriz as h')
                ->join('fp_central_matriz.dbo.catalogo_maestro as cm', 'cm.EAN', '=', 'h.F_CODBAR')
                ->select(
                    'h.F_CODBAR as ean',
                    'cm.descripcion',
                    DB::raw('COUNT(*) as cantidad'),
                    DB::raw('AVG(CAST(h.F_MONTO AS DECIMAL(18,2))) as precio_unitario'),
                    DB::raw('SUM(CAST(h.F_MONTO AS DECIMAL(18,2))) as subtotal')
                )
                ->where('h.IDCLIENTE', $cliente->idtarjetaclientefrecuente)
                ->where('h.F_NUMTICKE', $ticket)
                ->whereNotNull('h.F_CODBAR')
                ->where('h.F_CODBAR', '!=', '')
                ->groupBy('h.F_CODBAR', 'cm.descripcion')
                ->orderBy('subtotal', 'DESC')
                ->get();
            
            $totalMonto = $productos->sum('subtotal');
            $fecha = $fechaTicket ? $fechaTicket->FECHA_DT : null;
            
            return view('reportes.montos_promedio_compra.detalle_productos_ticket', compact(
                'cliente', 'productos', 'ticket', 'fecha', 'fechaInicio', 'fechaFin', 'totalMonto'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error en getProductosPorTicket: ' . $e->getMessage());
            return back()->with('error', 'Error al cargar los productos del ticket');
        }
    }

    // Métodos privados auxiliares
    private function getFechasFiltro(Request $request)
    {
        $filtro = $request->input('filtro_fecha', 'hoy'); // Por defecto 'hoy'
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        
        // Si hay fechas personalizadas, usarlas
        if ($fechaInicio && $fechaFin) {
            return [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin
            ];
        }
        
        // Aplicar filtros rápidos
        switch ($filtro) {
            case 'hoy':
                return [
                    'inicio' => now()->format('Y-m-d'),
                    'fin' => now()->format('Y-m-d')
                ];
            case 'esta_semana':
                // CORREGIDO: Lunes a Domingo
                return [
                    'inicio' => now()->startOfWeek()->format('Y-m-d'),
                    'fin' => now()->endOfWeek()->format('Y-m-d')
                ];
            case 'este_mes':
                return [
                    'inicio' => now()->startOfMonth()->format('Y-m-d'),
                    'fin' => now()->endOfMonth()->format('Y-m-d')
                ];
            case 'este_ano':
                return [
                    'inicio' => now()->startOfYear()->format('Y-m-d'),
                    'fin' => now()->endOfYear()->format('Y-m-d')
                ];
            default:
                return [
                    'inicio' => now()->startOfMonth()->format('Y-m-d'),
                    'fin' => now()->endOfMonth()->format('Y-m-d')
                ];
        }
    }

    /**
     * Obtener el total de ventas del día
     */
    private function getVentasPorDia($fechaInicio, $fechaFin)
    {
        return DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz')
            ->select(
                DB::raw('FECHA_DT as fecha'),
                DB::raw('SUM(CAST(F_MONTO AS DECIMAL(18,2))) as total_ventas'),
                DB::raw('COUNT(DISTINCT F_NUMTICKE) as transacciones')
            )
            ->whereBetween('FECHA_DT', [$fechaInicio, $fechaFin])
            ->groupBy('FECHA_DT')
            ->orderBy('FECHA_DT')
            ->get();
    }

    /**
     * Buscador de clientes para el filtro
     */
    public function buscarClientes(Request $request): JsonResponse
    {
        $termino = $request->input('q', '');
        
        // Buscar a partir de 3 caracteres
        if (strlen($termino) < 3) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }
        
        // Buscar clientes con status CLIENTE (activos)
        $clientes = Cliente::where('status', 'CLIENTE')
            ->where(function($query) use ($termino) {
                $query->where('Nombre', 'LIKE', "%{$termino}%")
                    ->orWhere('apPaterno', 'LIKE', "%{$termino}%")
                    ->orWhere('apMaterno', 'LIKE', "%{$termino}%")
                    ->orWhereRaw("CONCAT(Nombre, ' ', apPaterno, ' ', COALESCE(apMaterno, '')) LIKE ?", ["%{$termino}%"]);
            })
            ->limit(5)  // Limitar a 5 resultados
            ->get(['id_Cliente', 'Nombre', 'apPaterno', 'apMaterno']);
        
        return response()->json([
            'success' => true,
            'data' => $clientes->map(function($cliente) {
                return [
                    'id' => $cliente->id_Cliente,
                    'nombre_completo' => $cliente->nombre_completo,
                ];
            })
        ]);
    }

    // =======================================================
    // REPORTE SUCURSALES PREFERIDAS 
    // =======================================================

    /**
     * Muestra la vista de sucursales preferidas Reporte
     */
    public function sucursalesPreferidas(Request $request)
    {
        return view('reportes.sucursales_preferidas.index');
    }

    /**
     * Obtiene los datos vía AJAX para el reporte de sucursales preferidas
     */
    public function sucursalesPreferidasData(Request $request)
    {
        try {
            $fechas = $this->getFechasFiltro($request);
            $fechaInicio = $fechas['inicio'];
            $fechaFin = $fechas['fin'];
            
            $sortBy = $request->input('sort_by', 'ventas');
            
            // Validar fechas
            if (!$fechaInicio || !$fechaFin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Debe seleccionar un período de fechas',
                    'data' => []
                ]);
            }
            
            $idsExcluir = ['0000000007295', '0000000004489'];
            
            // Usar sqlsrvV para historial_ventas_matriz
            $query = DB::connection('sqlsrvV')
                ->table('historial_ventas_matriz as hv')
                ->join('fp_central_matriz.dbo.sucursales as s', 's.id_sucursal', '=', 'hv.id_sucursal')
                ->whereNotIn('hv.IDCLIENTE', $idsExcluir)
                ->whereBetween('hv.FECHA_DT', [$fechaInicio, $fechaFin])
                ->select(
                    's.id_sucursal',
                    's.nombre',
                    DB::raw('CAST(COUNT(DISTINCT hv.F_NUMTICKE) AS DECIMAL(18,2)) as total_ventas'),
                    DB::raw('CAST(SUM(CAST(hv.F_MONTO AS DECIMAL(18,2))) AS DECIMAL(18,2)) as monto_total'),
                    DB::raw('CAST(AVG(CAST(hv.F_MONTO AS DECIMAL(18,2))) AS DECIMAL(18,2)) as ticket_promedio'),
                    DB::raw('COUNT(DISTINCT hv.IDCLIENTE) as clientes_atendidos')
                )
                ->groupBy('s.id_sucursal', 's.nombre');
            
            // Aplicar ordenamiento
            switch ($sortBy) {
                case 'ventas':
                    $query->orderBy('total_ventas', 'DESC');
                    break;
                case 'ventas_asc':
                    $query->orderBy('total_ventas', 'ASC');
                    break;
                case 'monto':
                    $query->orderBy('monto_total', 'DESC');
                    break;
                case 'monto_asc':
                    $query->orderBy('monto_total', 'ASC');
                    break;
                case 'ticket':
                    $query->orderBy('ticket_promedio', 'DESC');
                    break;
                case 'ticket_asc':
                    $query->orderBy('ticket_promedio', 'ASC');
                    break;
                default:
                    $query->orderBy('total_ventas', 'DESC');
            }
            
            $sucursales = $query->get();
            
            // Convertir los valores a números para asegurar el formato correcto
            $sucursales = $sucursales->map(function($item) {
                return (object) [
                    'id_sucursal' => $item->id_sucursal,
                    'nombre' => $item->nombre,
                    'total_ventas' => floatval($item->total_ventas),
                    'monto_total' => floatval($item->monto_total),
                    'ticket_promedio' => floatval($item->ticket_promedio),
                    'clientes_atendidos' => intval($item->clientes_atendidos)
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $sucursales,
                'filtros' => [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'sort_by' => $sortBy
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en sucursalesPreferidasData: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * Exportar a Excel sucursales preferidas
     */
    public function exportarSucursalesExcel(Request $request)
    {
        $fechas = $this->getFechasFiltro($request);
        $top = $request->input('top', 10);
        $sortBy = $request->input('sort_by', 'ventas');
        
        $response = $this->sucursalesPreferidasData($request);
        $data = json_decode($response->getContent(), true);
        
        $sucursales = collect($data['data'] ?? [])->map(function($item) {
            return (object) $item;
        });
        
        $fechaActual = now()->format('Ymd_His');
        
        return Excel::download(
            new SucursalesPreferidasExport($sucursales, $fechas),
            "sucursales_preferidas_{$fechaActual}.xlsx"
        );
    }

    /**
     * Exportar a PDF sucursales preferidas
     */
    public function exportarSucursalesPdf(Request $request)
    {
        $fechas = $this->getFechasFiltro($request);
        
        $response = $this->sucursalesPreferidasData($request);
        $data = json_decode($response->getContent(), true);
        
        $sucursales = collect($data['data'] ?? [])->map(function($item) {
            return (object) $item;
        });
        
        $pdf = Pdf::loadView('reportes.sucursales_preferidas.pdf.sucursales_preferidas', compact('sucursales', 'fechas'));
        
        return $pdf->download("sucursales_preferidas_" . now()->format('Ymd_His') . ".pdf");
    }
}