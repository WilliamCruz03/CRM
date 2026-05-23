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
use Illuminate\Http\JsonResponse;

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

        return view('reportes.ventas.index', compact(
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
            
            $top = $request->get('top', 50);
            $sortBy = $request->get('sort_by', 'monto_total');
            $searchCliente = $request->get('search_cliente');
            
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
            
            // Aplicar límite
            if ($top !== 'todos') {
                $query->limit((int)$top);
            }
            
            $clientes = $query->get();
            
            // Log para depuración
            \Log::info('Filtros aplicados:', [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'top' => $top,
                'sort_by' => $sortBy,
                'search_cliente' => $searchCliente,
                'total_clientes' => $clientes->count()
            ]);
            
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
            
            return view('reportes.ventas.clientes', compact(
                'clientes', 'fechaInicio', 'fechaFin', 'top', 'sortBy', 'searchCliente'
            ) + ['sortFields' => $this->validSortFields]);
        }
        
        // Aplicar filtros de fecha
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];
        
        // Obtener parámetros
        $top = $request->get('top', 50);
        $sortBy = $request->get('sort_by', 'monto_total');
        $searchCliente = $request->get('search_cliente');
        
        // Construir la consulta base
        $query = HistorialVenta::entreFechas($fechaInicio, $fechaFin)
            ->join('fp_central_matriz.dbo.catalogo_cliente_maestro as c', 'historial_ventas_matriz.IDCLIENTE', '=', 'c.idtarjetaclientefrecuente')
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
            return view('reportes.ventas.clientes', compact(
                'clientes', 'fechaInicio', 'fechaFin', 'top', 'sortBy', 'searchCliente'
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
        
        // Aplicar límite TOP
        if ($top !== 'todos') {
            $query->limit((int)$top);
        }
        
        $clientes = $query->get();
        
        return view('reportes.ventas.clientes', compact(
            'clientes', 'fechaInicio', 'fechaFin', 'top', 'sortBy', 'searchCliente'
        ) + ['sortFields' => $this->validSortFields]);
    }
    
    /**
     * Detalle de compras por cliente
     */
    public function detalleCliente(Request $request, $clienteId)
    {
        // Obtener filtros de la URL (mantener consistencia)
        $top = $request->input('top', 'todos');
        $sortBy = $request->input('sort_by', 'monto_total');
        $filtroFecha = $request->input('filtro_fecha', 'este_mes');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        
        // Si no hay fechas, calcular según el filtro rápido
        if ((!$fechaInicio || !$fechaFin) && $filtroFecha && $filtroFecha !== 'personalizado') {
            $fechas = $this->getFechasFiltro($request);
            $fechaInicio = $fechas['inicio'];
            $fechaFin = $fechas['fin'];
        }
        
        \Log::info('=== DETALLE CLIENTE ===');
        \Log::info('Cliente ID: ' . $clienteId);
        \Log::info('Top: ' . $top);
        \Log::info('Ordenar por: ' . $sortBy);
        \Log::info('Filtro fecha: ' . $filtroFecha);
        \Log::info('Fecha Inicio: ' . $fechaInicio);
        \Log::info('Fecha Fin: ' . $fechaFin);

        // Obtener datos del cliente
        $cliente = Cliente::findOrFail($clienteId);
        \Log::info('ID Tarjeta Cliente Frecuente: ' . $cliente->idtarjetaclientefrecuente);

        // Verificar si hay ventas en el rango de fechas
        $existeVenta = DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz')
            ->where('IDCLIENTE', $cliente->idtarjetaclientefrecuente)
            ->whereBetween('FECHA_DT', [$fechaInicio, $fechaFin])
            ->exists();
        
        \Log::info('Existen ventas en el rango: ' . ($existeVenta ? 'SÍ' : 'NO'));

        if (!$existeVenta) {
            // Buscar la última fecha de venta del cliente
            $ultimaVenta = DB::connection('sqlsrvV')
                ->table('historial_ventas_matriz')
                ->where('IDCLIENTE', $cliente->idtarjetaclientefrecuente)
                ->max('FECHA_DT');
            
            \Log::info('Última venta del cliente: ' . $ultimaVenta);
            
            $productos = collect(); // Colección vacía
            $totalGeneral = 0;
            
            return view('reportes.ventas.detalle_cliente', compact(
                'cliente', 'productos', 'totalGeneral', 'fechaInicio', 'fechaFin'
            ));
        }

        // Obtener productos
        $productos = DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz as h')
            ->join('fp_central_matriz.dbo.catalogo_general as cg', 'cg.ean', '=', 'h.F_CODBAR')
            ->select(
                'cg.ean',
                'cg.descripcion',
                DB::raw('COUNT(DISTINCT h.F_NUMTICKE) as transacciones'),
                DB::raw('COUNT(*) as cantidad_vendida'),
                DB::raw('SUM(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_total'),
                DB::raw('AVG(CAST(h.F_MONTO AS DECIMAL(18,2))) as precio_promedio'),
                DB::raw('MAX(h.FECHA_DT) as ultima_venta')
            )
            ->where('h.IDCLIENTE', $cliente->idtarjetaclientefrecuente)
            ->whereBetween('h.FECHA_DT', [$fechaInicio, $fechaFin])
            ->whereNotNull('h.F_CODBAR')
            ->where('h.F_CODBAR', '!=', '')
            ->groupBy('cg.ean', 'cg.descripcion')
            ->orderBy('monto_total', 'DESC')
            ->get();

        \Log::info('Total productos encontrados: ' . $productos->count());

        $totalGeneral = $productos->sum('monto_total');

        return view('reportes.ventas.detalle_cliente', compact(
            'cliente', 'productos', 'totalGeneral', 'fechaInicio', 'fechaFin'
        ));
    }

    /**
     * Listado de productos por cliente y familia
     */
    public function detalleFamilia(Request $request, $clienteId, $familiaId)
    {
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];

        $cliente = Cliente::findOrFail($clienteId);

        $productos = DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz as h')
            ->join('fp_central_matriz.dbo.catalogo_general as cg', 'cg.ean', '=', 'h.F_CODBAR')
            ->select(
                'cg.ean',
                'cg.descripcion',
                DB::raw('COUNT(*) as cantidad_vendida'),
                DB::raw('SUM(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_total'),
                DB::raw('MAX(h.FECHA_DT) as ultima_venta'),
                DB::raw('AVG(CAST(h.F_MONTO AS DECIMAL(18,2))) as precio_promedio')
            )
            ->where('h.IDCLIENTE', $cliente->idtarjetaclientefrecuente)
            ->where('cg.num_familia', $familiaId)
            ->whereBetween('h.FECHA_DT', [$fechaInicio, $fechaFin])
            ->whereNotNull('h.F_CODBAR')
            ->where('h.F_CODBAR', '!=', '')
            ->groupBy('cg.ean', 'cg.descripcion')
            ->orderBy('monto_total', 'DESC')
            ->get();

        // Obtener nombre de la familia
        $familia = DB::connection('fp_central_matriz')
            ->table('cat_familias')
            ->where('num_familia', $familiaId)
            ->first();

        return view('reportes.ventas.detalle_familia', compact(
            'cliente', 'productos', 'familia', 'fechaInicio', 'fechaFin'
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

        $top = $request->get('top', 10);

        $clientes = HistorialVenta::getResumenClientes($fechaInicio, $fechaFin, $top);

        return view('reportes.ventas.top_clientes', compact(
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

        $top = $request->get('top', 10);
        $orden = $request->get('orden', 'monto'); // monto o cantidad

        $productos = $this->getTopProductos($fechaInicio, $fechaFin, $top, $orden);

        return view('reportes.ventas.top_productos', compact(
            'productos', 'fechaInicio', 'fechaFin', 'top', 'orden'
        ));
    }

    /**
     * Top sucursales
     */
    public function topSucursales(Request $request)
    {
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];

        $top = $request->get('top', 10);

        $sucursales = DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz as h')
            ->join('fp_central_matriz.dbo.sucursales as s', 's.id_sucursal', '=', 'h.id_sucursal')
            ->select(
                's.id_sucursal',
                's.nombre',
                DB::raw('COUNT(DISTINCT h.F_NUMTICKE) as total_transacciones'),
                DB::raw('SUM(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_total'),
                DB::raw('COUNT(DISTINCT h.IDCLIENTE) as clientes_atendidos'),
                DB::raw('AVG(CAST(h.F_MONTO AS DECIMAL(18,2))) as ticket_promedio')
            )
            ->whereBetween('h.FECHA_DT', [$fechaInicio, $fechaFin])
            ->groupBy('s.id_sucursal', 's.nombre')
            ->orderBy('monto_total', 'DESC')
            ->limit($top)
            ->get();

        return view('reportes.ventas.top_sucursales', compact(
            'sucursales', 'fechaInicio', 'fechaFin', 'top'
        ));
    }

    /**
     * Cotizaciones por cliente
     */
    public function cotizacionesCliente(Request $request)
    {
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];

        $clienteId = $request->get('cliente_id');
        $searchCliente = $request->get('search_cliente');

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

        return view('reportes.ventas.cotizaciones_cliente', compact(
            'cotizaciones', 'fechaInicio', 'fechaFin', 'clienteId', 'searchCliente'
        ));
    }

    /**
     * Cotizaciones concretadas
     */
    public function cotizacionesConcretadas(Request $request)
    {
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];

        $top = $request->get('top', 10);

        $resumen = Cotizacion::with('cliente')
            ->where('activo', 1)
            ->where('enviado', 1)
            ->where('es_pedido', 1)
            ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin])
            ->select(
                'id_cliente',
                DB::raw('COUNT(*) as total_cotizaciones'),
                DB::raw('SUM(importe_total) as monto_total'),
                DB::raw('AVG(importe_total) as monto_promedio')
            )
            ->groupBy('id_cliente')
            ->orderBy('monto_total', 'DESC')
            ->limit($top)
            ->get();

        return view('reportes.ventas.cotizaciones_concretadas', compact(
            'resumen', 'fechaInicio', 'fechaFin', 'top'
        ));
    }

    /**
     * Frecuencia de compra por cliente
     */
    public function frecuenciaCompra(Request $request)
    {
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];
        
        $top = $request->get('top', 50);
        $searchCliente = $request->get('search_cliente');
        
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
            ->having('total_compras', '>', 1);
        
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
        
        return view('reportes.ventas.frecuencia_compra', compact(
            'clientes', 'fechaInicio', 'fechaFin', 'top', 'searchCliente'
        ));
    }

    /**
     * Montos promedio de compra
     */
    public function montosPromedio(Request $request)
    {
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];
        
        $rango = $request->get('rango', 'todos');
        $top = $request->get('top', 50);
        
        $clientes = DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz as h')
            ->join('fp_central_matriz.dbo.catalogo_cliente_maestro as c', 'h.IDCLIENTE', '=', 'c.idtarjetaclientefrecuente')
            ->select(
                'c.id_Cliente',
                'c.Nombre',
                'c.apPaterno',
                'c.apMaterno',
                DB::raw('COUNT(DISTINCT h.F_NUMTICKE) as total_compras'),
                DB::raw('SUM(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_total'),
                DB::raw('AVG(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_promedio'),
                DB::raw('MIN(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_minimo'),
                DB::raw('MAX(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_maximo')
            )
            ->whereBetween('h.FECHA_DT', [$fechaInicio, $fechaFin])
            ->groupBy('c.id_Cliente', 'c.Nombre', 'c.apPaterno', 'c.apMaterno');
        
        if ($rango !== 'todos') {
            $rangos = [
                '0-500' => ['min' => 0, 'max' => 500],
                '500-1000' => ['min' => 500, 'max' => 1000],
                '1000-5000' => ['min' => 1000, 'max' => 5000],
                '5000+' => ['min' => 5000, 'max' => PHP_FLOAT_MAX]
            ];
            
            if (isset($rangos[$rango])) {
                $clientes->havingBetween('monto_promedio', [$rangos[$rango]['min'], $rangos[$rango]['max']]);
            }
        }
        
        if ($top !== 'todos') {
            $clientes->limit((int)$top);
        }
        
        $clientes = $clientes->orderBy('monto_promedio', 'DESC')->get();
        
        // Estadísticas globales
        $estadisticas = DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz')
            ->whereBetween('FECHA_DT', [$fechaInicio, $fechaFin])
            ->select(
                DB::raw('AVG(CAST(F_MONTO AS DECIMAL(18,2))) as promedio_general'),
                DB::raw('STDEV(CAST(F_MONTO AS DECIMAL(18,2))) as desviacion_std'),
                DB::raw('MIN(CAST(F_MONTO AS DECIMAL(18,2))) as monto_minimo'),
                DB::raw('MAX(CAST(F_MONTO AS DECIMAL(18,2))) as monto_maximo')
            )
            ->first();
        
        return view('reportes.ventas.montos_promedio', compact(
            'clientes', 'fechaInicio', 'fechaFin', 'top', 'rango', 'estadisticas'
        ));
    }

    /**
     * Exportar a Excel
     */
    public function exportarExcel(Request $request)
    {
        $tipo = $request->get('tipo', 'clientes');
        $fechas = $this->getFechasFiltro($request);
        
        switch ($tipo) {
            case 'clientes':
                $clientes = HistorialVenta::getResumenClientes($fechas['inicio'], $fechas['fin']);
                
                // Aplicar TOP si existe
                $top = $request->get('top', 'todos');
                if ($top !== 'todos') {
                    $clientes = $clientes->take((int)$top);
                }
                
                $sortBy = $request->get('sort_by', 'monto_total');
                $fechaActual = now()->format('Ymd_His');
                
                return Excel::download(
                    new VentasClienteExport($fechas['inicio'], $fechas['fin'], $top, $sortBy), 
                    "reporte_clientes_{$fechaActual}.xlsx"
                );
                
            case 'top-clientes':
                $top = $request->get('top', 10);
                $fechaActual = now()->format('Ymd_His');
                return Excel::download(
                    new TopClientesExport($fechas['inicio'], $fechas['fin'], $top), 
                    "top_clientes_{$fechaActual}.xlsx"
                );
                
            case 'top-productos':
                $top = $request->get('top', 10);
                $orden = $request->get('orden', 'monto');
                $fechaActual = now()->format('Ymd_His');
                return Excel::download(
                    new TopProductosExport($fechas['inicio'], $fechas['fin'], $top, $orden), 
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
        $tipo = $request->get('tipo', 'clientes');
        $fechas = $this->getFechasFiltro($request);
        
        switch ($tipo) {
            case 'clientes':
                $clientes = HistorialVenta::getResumenClientes($fechas['inicio'], $fechas['fin']);
                
                // Aplicar TOP si existe
                $top = $request->get('top', 'todos');
                if ($top !== 'todos') {
                    $clientes = $clientes->take((int)$top);
                }
                
                $sortBy = $request->get('sort_by', 'monto_total');
                $pdf = Pdf::loadView('reportes.ventas.pdf.clientes', compact('clientes', 'fechas', 'top', 'sortBy'));
                return $pdf->download('reporte_clientes_' . now()->format('Ymd_His') . '.pdf');
                
            case 'top-clientes':
                $top = $request->get('top', 10);
                $clientes = HistorialVenta::getResumenClientes($fechas['inicio'], $fechas['fin'], $top);
                $pdf = Pdf::loadView('reportes.ventas.pdf.top_clientes', compact('clientes', 'fechas', 'top'));
                return $pdf->download('top_clientes_' . now()->format('Ymd_His') . '.pdf');
                
            case 'top-productos':
                $top = $request->get('top', 10);
                $orden = $request->get('orden', 'monto');
                $productos = $this->getTopProductos($fechas['inicio'], $fechas['fin'], $top, $orden);
                $pdf = Pdf::loadView('reportes.ventas.pdf.top_productos', compact('productos', 'fechas', 'top', 'orden'));
                return $pdf->download('top_productos_' . now()->format('Ymd_His') . '.pdf');
                
            default:
                return back()->with('error', 'Tipo de exportación no válido');
        }
    }

    // Métodos privados auxiliares
    private function getFechasFiltro(Request $request)
    {
        $filtro = $request->get('filtro_fecha', 'hoy'); // Por defecto 'hoy'
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');
        
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
     * Obtener el top de los porductos mas vendidos
     */
    private function getTopProductos($fechaInicio, $fechaFin, $limit = 10, $orden = 'monto')
    {
        $orderBy = $orden === 'cantidad' ? 'cantidad_vendida' : 'monto_total';
        
        return DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz as h')
            ->join('fp_central_matriz.dbo.catalogo_general as cg', 'cg.ean', '=', 'h.F_CODBAR')
            ->select(
                'cg.ean',
                'cg.descripcion',
                DB::raw('COUNT(*) as cantidad_vendida'),
                DB::raw('SUM(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_total'),
                DB::raw('COUNT(DISTINCT h.IDCLIENTE) as clientes_distintos')
            )
            ->whereBetween('h.FECHA_DT', [$fechaInicio, $fechaFin])
            ->whereNotNull('h.F_CODBAR')
            ->where('h.F_CODBAR', '!=', '')
            ->groupBy('cg.ean', 'cg.descripcion')
            ->orderBy($orderBy, 'DESC')
            ->limit($limit)
            ->get();
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
}