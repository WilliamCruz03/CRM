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
            $filtroFecha = $request->input('filtro_fecha', 'este_mes');
            
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
            
            $query = DB::connection('sqlsrvV')
                ->table('historial_ventas_matriz')
                ->join('fp_central_matriz.dbo.catalogo_cliente_maestro as c', 'historial_ventas_matriz.IDCLIENTE', '=', 'c.idtarjetaclientefrecuente')
                ->whereNotIn('historial_ventas_matriz.IDCLIENTE', $idsExcluir)
                ->whereBetween('historial_ventas_matriz.FECHA_DT', [$fechaInicio, $fechaFin])
                ->where(function($q) {
                    $q->whereNull('historial_ventas_matriz.F_STATUS')
                    ->orWhereNotIn('historial_ventas_matriz.F_STATUS', ['C', 'D']);
                })
                ->where(DB::raw('CAST(historial_ventas_matriz.F_MONTO AS DECIMAL(18,2))'), '!=', 0);
            
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
                    DB::raw('CASE WHEN COUNT(DISTINCT F_NUMTICKE) > 0 THEN AVG(CAST(F_MONTO AS DECIMAL(18,2))) ELSE 0 END as ticket_promedio'),
                    DB::raw('MAX(FECHA_DT) as ultima_compra')
                )
                ->groupBy('c.id_Cliente', 'c.Nombre', 'c.apPaterno', 'c.apMaterno')
                ->havingRaw('SUM(CAST(F_MONTO AS DECIMAL(18,2))) > 0');
            
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
                    'indicacion_id' => $indicacionId,
                    'filtro_fecha' => $filtroFecha
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

        // Obtener filtros de la URL (para mantenerlos en la vista)
        $filtroFecha = $request->input('filtro_fecha', 'este_mes');
        
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
            'clientes', 'fechaInicio', 'fechaFin', 'top', 'sortBy', 'searchCliente', 'indicaciones',
            'filtroFecha'
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
                'frecuenciaTexto', 'frecuenciaBadgeColor', 'top', 'sortBy', 'searchCliente',
                'filtroFecha'
            ));
        }

        // Generar una clave única para esta consulta basada en los filtros
        $cacheKey = 'detalle_cliente_' . md5($clienteId . $fechaInicio . $fechaFin . $sortBy);
        
        // Si los datos ya están en sesión y no hay cambios, usarlos
        if (session()->has($cacheKey) && !$request->has('refresh')) {
            $data = session()->get($cacheKey);
            return view('reportes.compras_cliente.detalle_cliente', $data);
        }

        // ============================================
        // 1. OBTENER EANs DEL CLIENTE
        // ============================================
        $eanData = DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz')
            ->where('IDCLIENTE', $cliente->idtarjetaclientefrecuente)
            ->whereBetween('FECHA_DT', [$fechaInicio, $fechaFin])
            ->whereNotNull('F_CODBAR')
            ->where('F_CODBAR', '!=', '')
            ->select('F_CODBAR', 'F_STATUS', 'F_MONTO', 'F_NUMTICKE')
            ->get();

        if ($eanData->isEmpty()) {
            $gruposMadre = collect();
            $familias = collect();
            $totalGeneral = 0;
            
            return view('reportes.compras_cliente.detalle_cliente', compact(
                'cliente', 'familias', 'gruposMadre', 'totalGeneral', 'fechaInicio', 'fechaFin',
                'frecuenciaTexto', 'frecuenciaBadgeColor', 'top', 'sortBy', 'searchCliente',
                'filtroFecha'
            ));
        }

        // Extraer EANs únicos
        $eanList = $eanData->pluck('F_CODBAR')->unique()->toArray();

        // ============================================
        // 2. GRUPOS MADRE - EN CHUNKS (evita error 2100)
        // ============================================
        $gruposMadreMap = collect();
        
        foreach (array_chunk($eanList, 1000) as $chunk) {
            $result = DB::connection('sqlsrvM')
                ->table('catalogo_maestro as cm')
                ->join('grupos_familias as gf', 'gf.numfamilia', '=', 'cm.numFam')
                ->whereIn('cm.EAN', $chunk)
                ->select('cm.EAN', 'gf.id_grupo_madre', 'gf.descripciongrupomadre')
                ->get();
            
            $gruposMadreMap = $gruposMadreMap->merge($result);
        }

        // Agrupar por id_grupo_madre
        $gruposMadreMap = $gruposMadreMap->groupBy('id_grupo_madre')
            ->map(function($items) {
                return [
                    'id' => $items->first()->id_grupo_madre,
                    'descripcion' => $items->first()->descripciongrupomadre,
                    'eans' => $items->pluck('EAN')->toArray()
                ];
            });

        // ============================================
        // 3. PROCESAR DATOS EN PHP
        // ============================================
        $gruposMadre = collect();
        $totalGeneral = 0;

        foreach ($gruposMadreMap as $grupoId => $grupoData) {
            $eansGrupo = $grupoData['eans'];
            
            $datosGrupo = $eanData->filter(function($item) use ($eansGrupo) {
                return in_array($item->F_CODBAR, $eansGrupo);
            });

            if ($datosGrupo->isEmpty()) {
                continue;
            }

            $montoTotal = 0;
            $montoCanceladas = 0;
            $montoDevoluciones = 0;
            $tickets = [];

            foreach ($datosGrupo as $item) {
                $monto = floatval($item->F_MONTO);
                $status = $item->F_STATUS;
                $ticket = $item->F_NUMTICKE;

                $tickets[] = $ticket;

                if (is_null($status) || !in_array($status, ['C', 'D'])) {
                    $montoTotal += $monto;
                } elseif ($status === 'C') {
                    $montoCanceladas += abs($monto);
                } elseif ($status === 'D') {
                    $montoDevoluciones += abs($monto);
                }
            }

            $ticketsUnicos = array_unique($tickets);
            $cantidadProductos = $datosGrupo->count();

            $grupo = (object) [
                'id_grupo_madre' => $grupoId,
                'descripciongrupomadre' => $grupoData['descripcion'],
                'transacciones' => count($ticketsUnicos),
                'cantidad_productos' => $cantidadProductos,
                'monto_total' => $montoTotal,
                'monto_canceladas' => $montoCanceladas,
                'monto_devoluciones' => $montoDevoluciones,
            ];

            $grupo->subtotal = $montoTotal + $montoCanceladas + $montoDevoluciones;
            $grupo->porc_completadas = $grupo->subtotal > 0 ? ($montoTotal / $grupo->subtotal) * 100 : 0;
            $grupo->porc_canceladas = $grupo->subtotal > 0 ? ($montoCanceladas / $grupo->subtotal) * 100 : 0;
            $grupo->porc_devoluciones = $grupo->subtotal > 0 ? ($montoDevoluciones / $grupo->subtotal) * 100 : 0;

            $gruposMadre->push($grupo);
            $totalGeneral += $montoTotal;
        }

        $gruposMadre = $gruposMadre->sortByDesc('monto_total')->values();

        // ============================================
        // 4. FAMILIAS - EN CHUNKS (evita error 2100)
        // ============================================
        $familiasMap = collect();

        foreach (array_chunk($eanList, 1000) as $chunk) {
            $result = DB::connection('sqlsrvM')
                ->table('catalogo_maestro as cm')
                ->join('grupos_familias as gf', 'gf.numfamilia', '=', 'cm.numFam')
                ->whereIn('cm.EAN', $chunk)
                ->select('cm.EAN', 'gf.numfamilia', 'gf.descripcionfamilia')
                ->get();
            
            $familiasMap = $familiasMap->merge($result);
        }

        $familiasMap = $familiasMap->groupBy('numfamilia')
            ->map(function($items) {
                return [
                    'num_familia' => $items->first()->numfamilia,
                    'nombre_familia' => $items->first()->descripcionfamilia,
                    'eans' => $items->pluck('EAN')->toArray()
                ];
            });

        $familias = collect();

        foreach ($familiasMap as $familiaId => $familiaData) {
            $eansFamilia = $familiaData['eans'];
            
            $datosFamilia = $eanData->filter(function($item) use ($eansFamilia) {
                return in_array($item->F_CODBAR, $eansFamilia) && 
                    (is_null($item->F_STATUS) || !in_array($item->F_STATUS, ['C', 'D']));
            });

            if ($datosFamilia->isNotEmpty()) {
                $montoTotal = $datosFamilia->sum(function($item) {
                    return floatval($item->F_MONTO);
                });

                $familias->push((object) [
                    'num_familia' => $familiaData['num_familia'],
                    'nombre_familia' => $familiaData['nombre_familia'],
                    'monto_total' => $montoTotal
                ]);
            }
        }

        $familias = $familias->sortByDesc('monto_total')->values();

        // ============================================
        // 5. FRECUENCIA DE COMPRAS
        // ============================================
        $fechasCompras = DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz')
            ->where('IDCLIENTE', $cliente->idtarjetaclientefrecuente)
            ->whereBetween('FECHA_DT', [$fechaInicio, $fechaFin])
            ->select('FECHA_DT')
            ->distinct()
            ->orderBy('FECHA_DT', 'asc')
            ->pluck('FECHA_DT')
            ->toArray();

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
            
            if ($frecuenciaCompra <= 3) {
                $frecuenciaTexto = "Cada {$frecuenciaCompra} días <span class='badge bg-success'>Frecuente</span>";
                $frecuenciaBadgeColor = 'success';
            } elseif ($frecuenciaCompra <= 5) {
                $frecuenciaTexto = "Cada {$frecuenciaCompra} días <span class='badge bg-warning text-dark'>Regular</span>";
                $frecuenciaBadgeColor = 'warning';
            } elseif ($frecuenciaCompra <= 10) {
                $frecuenciaTexto = "Cada {$frecuenciaCompra} días <span class='badge bg-info'>Ocasional</span>";
                $frecuenciaBadgeColor = 'info';
            } else {
                $frecuenciaTexto = "Cada {$frecuenciaCompra} días <span class='badge bg-secondary'>No Frecuente</span>";
                $frecuenciaBadgeColor = 'secondary';
            }
        } elseif (count($fechasCompras) == 1) {
            $frecuenciaTexto = 'Primera compra en el período';
            $frecuenciaBadgeColor = 'info';
        } else {
            $frecuenciaTexto = 'Sin compras en el período';
            $frecuenciaBadgeColor = 'secondary';
        }

        $viewData = compact(
            'cliente', 'familias', 'gruposMadre', 'totalGeneral', 'fechaInicio', 'fechaFin',
            'frecuenciaTexto', 'frecuenciaBadgeColor', 'top', 'sortBy', 'searchCliente'
        );

        // Guardar en sesión para la próxima vez (expira en 15 minutos)
        session()->put($cacheKey, $viewData);
        session()->put($cacheKey . '_expires', now()->addMinutes(15));

        return view('reportes.compras_cliente.detalle_cliente', $viewData);

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

        // ============================================
        // 1. OBTENER EANs DEL GRUPO MADRE
        // ============================================
        $eanGrupo = DB::connection('sqlsrvM')
            ->table('catalogo_maestro as cm')
            ->join('grupos_familias as gf', 'gf.numfamilia', '=', 'cm.numFam')
            ->where('gf.id_grupo_madre', $grupoMadreId)
            ->pluck('cm.EAN')
            ->toArray();

        if (empty($eanGrupo)) {
            $productos = collect();
            $totalGeneral = 0;
            return view('reportes.compras_cliente.detalle_grupo_madre', compact(
                'cliente', 'grupoMadre', 'productos', 'totalGeneral', 'fechaInicio',
                'fechaFin', 'top', 'sortBy', 'filtroFecha', 'indicacionId', 'searchCliente'
            ));
        }

        // ============================================
        // 2. VENTAS DEL GRUPO - EN CHUNKS (evita error 2100)
        // ============================================
        $ventasData = collect();

        foreach (array_chunk($eanGrupo, 1000) as $chunk) {
            $result = DB::connection('sqlsrvV')
                ->table('historial_ventas_matriz')
                ->where('IDCLIENTE', $cliente->idtarjetaclientefrecuente)
                ->whereBetween('FECHA_DT', [$fechaInicio, $fechaFin])
                ->whereIn('F_CODBAR', $chunk)
                ->whereNotNull('F_CODBAR')
                ->where('F_CODBAR', '!=', '')
                ->select('F_CODBAR', 'F_STATUS', 'F_MONTO', 'F_NUMTICKE', 'FECHA_DT')
                ->get();
            
            $ventasData = $ventasData->merge($result);
        }

        if ($ventasData->isEmpty()) {
            $productos = collect();
            $totalGeneral = 0;
            return view('reportes.compras_cliente.detalle_grupo_madre', compact(
                'cliente', 'grupoMadre', 'productos', 'totalGeneral', 'fechaInicio',
                'fechaFin', 'top', 'sortBy', 'filtroFecha', 'indicacionId', 'searchCliente'
            ));
        }

        // ============================================
        // 3. INFORMACIÓN DE PRODUCTOS - EN CHUNKS
        // ============================================
        $productosInfo = collect();

        foreach (array_chunk($eanGrupo, 1000) as $chunk) {
            $result = DB::connection('sqlsrvM')
                ->table('catalogo_maestro as cm')
                ->join('grupos_familias as gf', 'gf.numfamilia', '=', 'cm.numFam')
                ->whereIn('cm.EAN', $chunk)
                ->select('cm.EAN', 'cm.DESCRIPCION', 'gf.descripcionfamilia')
                ->get();
            
            $productosInfo = $productosInfo->merge($result);
        }

        $productosInfo = $productosInfo->keyBy('EAN');

        // ============================================
        // 4. PROCESAR DATOS EN PHP
        // ============================================
        $productos = collect();

        foreach ($ventasData->groupBy('F_CODBAR') as $ean => $items) {
            $info = $productosInfo->get($ean);
            
            if (!$info) {
                continue;
            }

            $montoTotal = 0;
            $montoCanceladas = 0;
            $montoDevoluciones = 0;
            $tickets = [];
            $ultimaVenta = null;

            foreach ($items as $item) {
                $monto = floatval($item->F_MONTO);
                $status = $item->F_STATUS;
                $ticket = $item->F_NUMTICKE;
                $fecha = $item->FECHA_DT;

                $tickets[] = $ticket;
                
                if ($ultimaVenta === null || $fecha > $ultimaVenta) {
                    $ultimaVenta = $fecha;
                }

                if (is_null($status) || !in_array($status, ['C', 'D'])) {
                    $montoTotal += $monto;
                } elseif ($status === 'C') {
                    $montoCanceladas += abs($monto);
                } elseif ($status === 'D') {
                    $montoDevoluciones += abs($monto);
                }
            }

            $ticketsUnicos = array_unique($tickets);

            $productos->push((object) [
                'ean' => $ean,
                'descripcion' => $info->DESCRIPCION,
                'nombre_familia' => $info->descripcionfamilia ?? 'Sin Familia',
                'transacciones' => count($ticketsUnicos),
                'cantidad_vendida' => $items->count(),
                'monto_total' => $montoTotal,
                'monto_canceladas' => $montoCanceladas,
                'monto_devoluciones' => $montoDevoluciones,
                'subtotal' => $montoTotal + $montoCanceladas + $montoDevoluciones,
                'ultima_venta' => $ultimaVenta
            ]);
        }

        $productos = $productos->sortByDesc('monto_total')->values();
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
            $filtroFecha = $request->input('filtro_fecha', 'este_mes');
            
            // Consulta usando orden_pedido_detalle para obtener el importe
            $query = OrdenPedido::with('cotizacion.cliente')
                ->where('orden_pedido.activo', 1)
                ->where('orden_pedido.status', '!=', 4)
                ->whereBetween('orden_pedido.fecha_pedido', [$fechaInicio, $fechaFin])
                ->join('orden_pedido_detalle as opd', 'orden_pedido.id_pedido', '=', 'opd.id_pedido')
                ->join('crm_cotizaciones as cotizacion', 'orden_pedido.id_cotizacion', '=', 'cotizacion.id_cotizacion')
                ->where('opd.se_elimino', '!=', 1);
            
            if ($clienteId && is_numeric($clienteId) && $clienteId > 0) {
                $query->where('cotizacion.id_cliente', (int) $clienteId);
            }
            
            $resumen = $query->select(
                    'cotizacion.id_cliente',
                    DB::raw('COUNT(DISTINCT orden_pedido.id_pedido) as total_pedidos'),
                    DB::raw('SUM(opd.importe) as monto_total'),
                    DB::raw('AVG(opd.importe) as monto_promedio')
                )
                ->groupBy('cotizacion.id_cliente');
            
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
                    'monto_total' => floatval($item->monto_total ?? 0),
                    'monto_promedio' => floatval($item->monto_promedio ?? 0),
                ];
            });
            
            // Siempre devolver filtros, incluso si no hay resultados
            return response()->json([
                'success' => true,
                'data' => $resultados,
                'filtros' => [
                    'fecha_inicio' => $fechaInicio instanceof \Carbon\Carbon ? $fechaInicio->format('Y-m-d') : $fechaInicio,
                    'fecha_fin' => $fechaFin instanceof \Carbon\Carbon ? $fechaFin->format('Y-m-d') : $fechaFin,
                    'top' => $top,
                    'sort_by' => $sortBy,
                    'filtro_fecha' => $filtroFecha,
                    'cliente_id' => $clienteId,
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en pedidosClienteData: ' . $e->getMessage());
            
            // En caso de error, también devolver filtros
            $fechas = $this->getFechasFiltro($request);
            $fechaInicio = $fechas['inicio'] ?? date('Y-m-d');
            $fechaFin = $fechas['fin'] ?? date('Y-m-d');
            $filtroFecha = $request->input('filtro_fecha', 'este_mes');
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los datos: ' . $e->getMessage(),
                'data' => [],
                'filtros' => [
                    'fecha_inicio' => $fechaInicio instanceof \Carbon\Carbon ? $fechaInicio->format('Y-m-d') : $fechaInicio,
                    'fecha_fin' => $fechaFin instanceof \Carbon\Carbon ? $fechaFin->format('Y-m-d') : $fechaFin,
                    'filtro_fecha' => $filtroFecha,
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
            
            // 1. Pedidos del cliente con importe desde detalles
            $pedidos = OrdenPedido::with('cotizacion')
                ->whereHas('cotizacion', function($q) use ($clienteId) {
                    $q->where('id_cliente', $clienteId);
                })
                ->whereBetween('orden_pedido.fecha_pedido', [$fechaInicio, $fechaFin])
                ->where('orden_pedido.activo', 1)
                ->join('orden_pedido_detalle as opd', 'orden_pedido.id_pedido', '=', 'opd.id_pedido')
                ->where('opd.se_elimino', '!=', 1)
                ->select(
                    'orden_pedido.id_pedido',
                    'orden_pedido.folio_pedido',
                    'orden_pedido.fecha_pedido',
                    'orden_pedido.status',
                    DB::raw('SUM(opd.importe) as importe_total')
                )
                ->groupBy('orden_pedido.id_pedido', 'orden_pedido.folio_pedido', 'orden_pedido.fecha_pedido', 'orden_pedido.status')
                ->orderBy('orden_pedido.fecha_pedido', 'DESC')
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
                        'importe_total' => floatval($pedido->importe_total ?? 0),
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
                    ->where('opd.se_elimino', '!=', 1)
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
            
            // Consulta usando orden_pedido_detalle
            $query = OrdenPedido::with('cotizacion.cliente')
                ->where('orden_pedido.activo', 1)
                ->where('orden_pedido.status', '!=', 4)
                ->whereBetween('orden_pedido.fecha_pedido', [$fechaInicio, $fechaFin])
                ->join('orden_pedido_detalle as opd', 'orden_pedido.id_pedido', '=', 'opd.id_pedido')
                ->join('crm_cotizaciones as cotizacion', 'orden_pedido.id_cotizacion', '=', 'cotizacion.id_cotizacion')
                ->where('opd.se_elimino', '!=', 1);
            
            if ($clienteId && is_numeric($clienteId) && $clienteId > 0) {
                $query->where('cotizacion.id_cliente', (int) $clienteId);
                $cliente = Cliente::find($clienteId);
                $clienteNombre = $cliente ? $cliente->nombre_completo : null;
            }
            
            $resultados = $query->select(
                    'cotizacion.id_cliente',
                    DB::raw('COUNT(DISTINCT orden_pedido.id_pedido) as total_pedidos'),
                    DB::raw('SUM(opd.importe) as monto_total'),
                    DB::raw('AVG(opd.importe) as monto_promedio')
                )
                ->groupBy('cotizacion.id_cliente');
            
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
                $cliente = Cliente::find($item->id_cliente);
                return [
                    'cliente_nombre' => $cliente->nombre_completo ?? 'N/A',
                    'total_pedidos' => $item->total_pedidos,
                    'monto_total' => floatval($item->monto_total ?? 0),
                    'monto_promedio' => floatval($item->monto_promedio ?? 0),
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
                return $this->exportarPedidosClienteExcel($data, $filtros);
            }
            
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
                ->where(function($q) {
                    $q->whereNull('hv.F_STATUS')
                    ->orWhereNotIn('hv.F_STATUS', ['C', 'D']);
                })
                ->where(DB::raw('CAST(hv.F_MONTO AS DECIMAL(18,2))'), '!=', 0)
                ->select(
                    'c.id_Cliente',
                    'c.Nombre',
                    'c.apPaterno',
                    'c.apMaterno',
                    'c.idtarjetaclientefrecuente',
                    DB::raw('COUNT(DISTINCT hv.F_NUMTICKE) as total_compras'),
                    DB::raw('SUM(CAST(hv.F_MONTO AS DECIMAL(18,2))) as monto_total'),
                    DB::raw('CASE WHEN COUNT(DISTINCT hv.F_NUMTICKE) > 0 THEN (SUM(CAST(hv.F_MONTO AS DECIMAL(18,2))) / COUNT(DISTINCT hv.F_NUMTICKE)) ELSE 0 END as monto_promedio'),
                    DB::raw('MIN(hv.FECHA_DT) as fecha_primera_compra'),
                    DB::raw('MAX(hv.FECHA_DT) as fecha_ultima_compra')
                )
                ->groupBy('c.id_Cliente', 'c.Nombre', 'c.apPaterno', 'c.apMaterno', 'c.idtarjetaclientefrecuente')
                ->havingRaw('SUM(CAST(hv.F_MONTO AS DECIMAL(18,2))) > 0');
            
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
                        ->where(function($q) {
                            $q->whereNull('F_STATUS')
                            ->orWhereNotIn('F_STATUS', ['C', 'D']);
                        })
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
                        ->where(function($q) {
                            $q->whereNull('F_STATUS')
                            ->orWhereNotIn('F_STATUS', ['C', 'D']);
                        })
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
            
            // Obtener compras agrupadas por ticket con status
            $compras = DB::connection('sqlsrvV')
                ->table('historial_ventas_matriz')
                ->where('IDCLIENTE', $cliente->idtarjetaclientefrecuente)
                ->whereBetween('FECHA_DT', [$fechaInicio, $fechaFin])
                ->select(
                    'FECHA_DT as fecha',
                    'F_NUMTICKE as ticket',
                    'F_STATUS',
                    DB::raw('SUM(CAST(F_MONTO AS DECIMAL(18,2))) as monto')
                )
                ->groupBy('FECHA_DT', 'F_NUMTICKE', 'F_STATUS')
                ->orderBy('FECHA_DT', 'DESC')
                ->get();
            
            // Calcular acumulado (solo montos positivos)
            $acumulado = 0;
            foreach ($compras as $compra) {
                $monto = $compra->monto > 0 ? $compra->monto : 0;
                $acumulado += $monto;
                $compra->acumulado = $acumulado;
                // Agregar status legible
                $compra->status_label = $this->getStatusLabel($compra->F_STATUS);
            }
            
            $totalCompras = $compras->count();
            $montoTotal = $compras->sum(function($item) {
                return $item->monto > 0 ? $item->monto : 0;
            });
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

    // Función auxiliar para status
    private function getStatusLabel($status)
    {
        if ($status === 'C') return 'Cancelado';
        if ($status === 'D') return 'Devolución';
        return 'Completado';
    }

    private function getTarjetaByClienteId($clienteId)
    {
        $cliente = Cliente::find($clienteId);
        return $cliente ? $cliente->idtarjetaclientefrecuente : null;
    }

    // ========================================
    // Exportar a Excel y PDF Compras por Cliente
    // =========================================

    /**
     * Exportar a Excel
     */
    public function exportarExcel(Request $request)
    {
        try {
            // Aumentar límites de memoria y tiempo de ejecución
            ini_set('memory_limit', '2048M');
            ini_set('max_execution_time', 600); // 10 minutos
            
            $tipo = $request->input('tipo', 'clientes');
            $fechas = $this->getFechasFiltro($request);
            
            // Obtener filtros adicionales
            $top = $request->input('top', 'todos');
            $sortBy = $request->input('sort_by', 'monto_total');
            $searchCliente = $request->input('search_cliente');
            $indicacionId = $request->input('indicacion_id');
            $filtroFecha = $request->input('filtro_fecha', 'este_mes');
            
            switch ($tipo) {
                case 'clientes':
                    $response = $this->clientesData($request);
                    $data = json_decode($response->getContent(), true);
                    
                    if (empty($data['data'])) {
                        return back()->with('error', 'No hay datos para exportar en el período seleccionado.');
                    }
                    
                    $clientes = collect($data['data'])->map(function($item) {
                        return (object) $item;
                    });
                    
                    $fechaActual = now()->format('Ymd_His');
                    
                    return Excel::download(
                        new VentasClienteExport($clientes, $fechas, $top, $sortBy, $searchCliente, $indicacionId), 
                        "reporte_clientes_{$fechaActual}.xlsx"
                    );
                    
                case 'top-clientes':
                    $top = $request->input('top', 10);
                    $fechaActual = now()->format('Ymd_His');
                    
                    $response = $this->clientesData($request);
                    $data = json_decode($response->getContent(), true);
                    
                    if (empty($data['data'])) {
                        return back()->with('error', 'No hay datos para exportar en el período seleccionado.');
                    }
                    
                    $clientes = collect($data['data'])->map(function($item) {
                        return (object) $item;
                    })->take((int)$top);
                    
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
        } catch (\Exception $e) {
            \Log::error('Error al exportar Excel: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el archivo Excel: ' . $e->getMessage());
        }
    }

    /**
     * Exportar a PDF
     */
    public function exportarPdf(Request $request)
    {
        try {
            // Aumentar límites de memoria y tiempo de ejecución
            ini_set('memory_limit', '2048M');
            ini_set('max_execution_time', 600); // 10 minutos
            
            $tipo = $request->input('tipo', 'clientes');
            $fechas = $this->getFechasFiltro($request);
            
            // Obtener filtros adicionales
            $top = $request->input('top', 'todos');
            $sortBy = $request->input('sort_by', 'monto_total');
            $searchCliente = $request->input('search_cliente');
            $indicacionId = $request->input('indicacion_id');
            $filtroFecha = $request->input('filtro_fecha', 'este_mes');
            
            $mensajeAdvertencia = null;
            
            switch ($tipo) {
                case 'clientes':
                    $response = $this->clientesData($request);
                    $data = json_decode($response->getContent(), true);
                    
                    if (empty($data['data'])) {
                        return back()->with('error', 'No hay datos para exportar en el período seleccionado.');
                    }
                    
                    $clientes = collect($data['data'])->map(function($item) {
                        return (object) $item;
                    });
                    
                    // Si hay más de 500 registros, limitar para PDF
                    if ($clientes->count() > 500) {
                        $mensajeAdvertencia = 'Nota: El PDF se ha limitado a 500 clientes (de ' . $clientes->count() . '). Para ver todos los registros, use la exportación a Excel.';
                        \Log::warning('PDF Clientes limitado a 500 (solicitado: ' . $clientes->count() . ')');
                        $clientes = $clientes->take(500);
                    }
                    
                    $pdf = Pdf::loadView('reportes.compras_cliente.pdf.clientes', compact('clientes', 'fechas', 'top', 'sortBy', 'searchCliente', 'indicacionId', 'mensajeAdvertencia'));
                    $pdf->setPaper('a4', 'landscape');
                    return $pdf->download('reporte_clientes_' . now()->format('Ymd_His') . '.pdf');
                    
                case 'top-clientes':
                    $top = $request->input('top', 10);
                    
                    $response = $this->clientesData($request);
                    $data = json_decode($response->getContent(), true);
                    
                    if (empty($data['data'])) {
                        return back()->with('error', 'No hay datos para exportar en el período seleccionado.');
                    }
                    
                    $clientes = collect($data['data'])->map(function($item) {
                        return (object) $item;
                    })->take((int)$top);
                    
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
        } catch (\Exception $e) {
            \Log::error('Error al exportar PDF: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el archivo PDF: ' . $e->getMessage());
        }
    }

    public function exportarMontosPromedioExcel(Request $request)
    {
        try {
            // Aumentar límites de memoria y tiempo de ejecución
            ini_set('memory_limit', '2048M');
            ini_set('max_execution_time', 600); // 10 minutos
            
            $fechas = $this->getFechasFiltro($request);
            $sortBy = $request->input('sort_by', 'monto_promedio');
            $top = $request->input('top', 'todos');
            
            // Obtener los mismos datos que en montosPromedioData
            $response = $this->montosPromedioData($request);
            $data = json_decode($response->getContent(), true);
            
            if (empty($data['data'])) {
                return back()->with('error', 'No hay datos para exportar en el período seleccionado.');
            }
            
            // Asegurar que los datos sean objetos
            $clientes = collect($data['data'])->map(function($item) {
                return (object) $item;
            });
            
            $fechaActual = now()->format('Ymd_His');
            
            return Excel::download(
                new MontosPromedioExport($clientes, $fechas, $sortBy),
                "montos_promedio_{$fechaActual}.xlsx"
            );
            
        } catch (\Exception $e) {
            \Log::error('Error al exportar Excel Montos Promedio: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el archivo Excel: ' . $e->getMessage());
        }
    }

    public function exportarMontosPromedioPdf(Request $request)
    {
        try {
            // Aumentar límites de memoria y tiempo de ejecución
            ini_set('memory_limit', '2048M');
            ini_set('max_execution_time', 600); // 10 minutos
            
            $fechas = $this->getFechasFiltro($request);
            $sortBy = $request->input('sort_by', 'monto_promedio');
            $top = $request->input('top', 'todos');
            
            // Obtener los mismos datos que en montosPromedioData
            $response = $this->montosPromedioData($request);
            $data = json_decode($response->getContent(), true);
            
            if (empty($data['data'])) {
                return back()->with('error', 'No hay datos para exportar en el período seleccionado.');
            }
            
            $clientes = collect($data['data'])->map(function($item) {
                return (object) $item;
            });
            
            // Si hay más de 500 registros, limitar para PDF y mostrar advertencia
            $mensajeAdvertencia = null;
            if ($clientes->count() > 500) {
                $mensajeAdvertencia = 'Nota: El PDF se ha limitado a 500 clientes (de ' . $clientes->count() . '). Para ver todos los registros, use la exportación a Excel.';
                \Log::warning('PDF Montos Promedio limitado a 500 clientes (solicitado: ' . $clientes->count() . ')');
                $clientes = $clientes->take(500);
            }
            
            $pdf = Pdf::loadView('reportes.montos_promedio_compra.pdf.montos_promedio', compact('clientes', 'fechas', 'sortBy', 'mensajeAdvertencia'));
            $pdf->setPaper('a4', 'landscape');
            
            return $pdf->download("montos_promedio_" . now()->format('Ymd_His') . ".pdf");
            
        } catch (\Exception $e) {
            \Log::error('Error al exportar PDF Montos Promedio: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el archivo PDF: ' . $e->getMessage());
        }
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
            
            // Usar sqlsrvV para historial_ventas_matriz
            $query = DB::connection('sqlsrvV')
                ->table('historial_ventas_matriz as hv')
                ->join('fp_central_matriz.dbo.sucursales as s', 's.id_sucursal', '=', 'hv.id_sucursal')
                ->whereBetween('hv.FECHA_DT', [$fechaInicio, $fechaFin])
                ->where(function($q) {
                    $q->whereNull('hv.F_STATUS')
                    ->orWhereNotIn('hv.F_STATUS', ['C', 'D']);
                })
                ->where(DB::raw('CAST(hv.F_MONTO AS DECIMAL(18,2))'), '>', 0)
                ->select(
                    's.id_sucursal',
                    's.nombre',
                    DB::raw('COUNT(DISTINCT hv.F_NUMTICKE) as total_ventas'),
                    DB::raw('SUM(CAST(hv.F_MONTO AS DECIMAL(18,2))) as monto_total'),
                    DB::raw('CASE WHEN COUNT(DISTINCT hv.F_NUMTICKE) > 0 THEN (SUM(CAST(hv.F_MONTO AS DECIMAL(18,2))) / COUNT(DISTINCT hv.F_NUMTICKE)) ELSE 0 END as ticket_promedio'),
                    DB::raw('COUNT(DISTINCT hv.IDCLIENTE) as clientes_atendidos')
                )
                ->groupBy('s.id_sucursal', 's.nombre')
                ->havingRaw('SUM(CAST(hv.F_MONTO AS DECIMAL(18,2))) > 0');
            
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
            
            // Convertir los valores a números
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
        $sortBy = $request->input('sort_by', 'ventas');
        
        $response = $this->sucursalesPreferidasData($request);
        $data = json_decode($response->getContent(), true);
        
        $sucursales = collect($data['data'] ?? [])->map(function($item) {
            return (object) $item;
        });
        
        $pdf = Pdf::loadView('reportes.sucursales_preferidas.pdf.sucursales_preferidas', compact('sucursales', 'fechas', 'sortBy'));
        
        return $pdf->download("sucursales_preferidas_" . now()->format('Ymd_His') . ".pdf");
    }
}