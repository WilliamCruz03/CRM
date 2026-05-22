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
     * Listado de clientes con compras
     */
    public function clientes(Request $request)
    {
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];

        $top = $request->get('top', 50);
        $sortBy = $request->get('sort_by', 'monto_total');
        $searchCliente = $request->get('search_cliente');

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
                DB::raw('MAX(F_FECHA) as ultima_compra')
            )
            ->groupBy('c.id_Cliente', 'c.Nombre', 'c.apPaterno', 'c.apMaterno');

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

        if ($top !== 'todos') {
            $query->limit((int)$top);
        }

        $clientes = $query->get();

        return view('reportes.ventas.clientes', compact(
            'clientes', 'fechaInicio', 'fechaFin', 'top', 'sortBy', 'searchCliente'
        ) + ['sortFields' => $this->validSortFields]);
    }

    /**
     * Detalle de áreas/familias por cliente
     */
    public function detalleCliente(Request $request, $clienteId)
    {
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];

        // Obtener datos del cliente
        $cliente = Cliente::findOrFail($clienteId);

        // Obtener resumen por familias
        $familias = DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz as h')
            ->join('fp_central_matriz.dbo.catalogo_general as cg', 'cg.ean', '=', 'h.F_CODBAR')
            ->join('fp_central_matriz.dbo.cat_familias as f', 'f.num_familia', '=', 'cg.num_familia')
            ->select(
                'f.num_familia',
                'f.descripcion as area',
                DB::raw('COUNT(DISTINCT h.F_NUMTICKE) as transacciones'),
                DB::raw('COUNT(*) as cantidad_productos'),
                DB::raw('SUM(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_total'),
                DB::raw('AVG(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_promedio')
            )
            ->where('h.IDCLIENTE', $cliente->idtarjetaclientefrecuente)
            ->whereBetween('h.F_FECHA', [$fechaInicio, $fechaFin])
            ->whereNotNull('h.F_CODBAR')
            ->where('h.F_CODBAR', '!=', '')
            ->groupBy('f.num_familia', 'f.descripcion')
            ->orderBy('monto_total', 'DESC')
            ->get();

        // Calcular total general
        $totalGeneral = $familias->sum('monto_total');

        return view('reportes.ventas.detalle_cliente', compact(
            'cliente', 'familias', 'totalGeneral', 'fechaInicio', 'fechaFin'
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
                DB::raw('MAX(h.F_FECHA) as ultima_venta'),
                DB::raw('AVG(CAST(h.F_MONTO AS DECIMAL(18,2))) as precio_promedio')
            )
            ->where('h.IDCLIENTE', $cliente->idtarjetaclientefrecuente)
            ->where('cg.num_familia', $familiaId)
            ->whereBetween('h.F_FECHA', [$fechaInicio, $fechaFin])
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
            ->whereBetween('h.F_FECHA', [$fechaInicio, $fechaFin])
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
                DB::raw('COUNT(DISTINCT CAST(h.F_FECHA AS DATE)) as dias_con_compra'),
                DB::raw('DATEDIFF(day, MIN(h.F_FECHA), MAX(h.F_FECHA)) as dias_entre_compras'),
                DB::raw('MIN(h.F_FECHA) as primera_compra'),
                DB::raw('MAX(h.F_FECHA) as ultima_compra'),
                DB::raw('SUM(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_total')
            )
            ->whereBetween('h.F_FECHA', [$fechaInicio, $fechaFin])
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
            ->whereBetween('h.F_FECHA', [$fechaInicio, $fechaFin])
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
            ->whereBetween('F_FECHA', [$fechaInicio, $fechaFin])
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
                return Excel::download(new VentasClienteExport($fechas['inicio'], $fechas['fin']), 'reporte_clientes.xlsx');
            case 'top-clientes':
                return Excel::download(new TopClientesExport($fechas['inicio'], $fechas['fin'], $request->get('top', 10)), 'top_clientes.xlsx');
            case 'top-productos':
                return Excel::download(new TopProductosExport($fechas['inicio'], $fechas['fin'], $request->get('top', 10)), 'top_productos.xlsx');
            case 'frecuencia_compra':
                // Implementar exportación de frecuencia de compra
                return back()->with('error', 'Exportación en desarrollo');
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
                $pdf = Pdf::loadView('reportes.ventas.pdf.clientes', compact('clientes', 'fechas'));
                return $pdf->download('reporte_clientes.pdf');
            case 'top-clientes':
                $clientes = HistorialVenta::getResumenClientes($fechas['inicio'], $fechas['fin'], $request->get('top', 10));
                $pdf = Pdf::loadView('reportes.ventas.pdf.top_clientes', compact('clientes', 'fechas'));
                return $pdf->download('top_clientes.pdf');
            case 'top-productos':
                $productos = $this->getTopProductos($fechas['inicio'], $fechas['fin'], $request->get('top', 10));
                $pdf = Pdf::loadView('reportes.ventas.pdf.top_productos', compact('productos', 'fechas'));
                return $pdf->download('top_productos.pdf');
            default:
                return back()->with('error', 'Tipo de exportación no válido');
        }
    }

    // Métodos privados auxiliares
    private function getFechasFiltro(Request $request)
    {
        $filtro = $request->get('filtro_fecha', 'este_mes');
        $fechaInicio = $request->get('fecha_inicio');
        $fechaFin = $request->get('fecha_fin');

        if ($fechaInicio && $fechaFin) {
            return [
                'inicio' => $fechaInicio,
                'fin' => $fechaFin
            ];
        }

        switch ($filtro) {
            case 'hoy':
                return [
                    'inicio' => now()->format('Y-m-d'),
                    'fin' => now()->format('Y-m-d')
                ];
            case 'esta_semana':
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
            ->whereBetween('h.F_FECHA', [$fechaInicio, $fechaFin])
            ->whereNotNull('h.F_CODBAR')
            ->where('h.F_CODBAR', '!=', '')
            ->groupBy('cg.ean', 'cg.descripcion')
            ->orderBy($orderBy, 'DESC')
            ->limit($limit)
            ->get();
    }

    private function getVentasPorDia($fechaInicio, $fechaFin)
    {
        return DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz')
            ->select(
                DB::raw('F_FECHA as fecha'),
                DB::raw('SUM(CAST(F_MONTO AS DECIMAL(18,2))) as total_ventas'),
                DB::raw('COUNT(DISTINCT F_NUMTICKE) as transacciones')
            )
            ->whereBetween('F_FECHA', [$fechaInicio, $fechaFin])
            ->groupBy('F_FECHA')
            ->orderBy('F_FECHA')
            ->get();
    }
}