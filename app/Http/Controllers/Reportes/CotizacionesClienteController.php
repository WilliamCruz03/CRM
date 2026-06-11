<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Cotizaciones\Cotizacion;
use App\Models\Cotizaciones\CotizacionDetalle;
use App\Models\TmpCatalogo;
use App\Models\CatalogoGeneral;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\CotizacionesClienteExport;

class CotizacionesClienteController extends Controller
{
    /**
     * Listado principal de clientes con cotizaciones
     */
    public function index(Request $request): View
    {
        $permisos = [
            'ver' => auth()->user()->puede('reportes', 'cotizaciones_cliente', 'ver'),
            'exportar' => auth()->user()->puede('reportes', 'cotizaciones_cliente', 'ver'),
        ];

        return view('reportes.cotizaciones_cliente.index', compact('permisos'));
    }

    /**
     * Obtener datos vía AJAX para el listado principal
     */
    public function data(Request $request): JsonResponse
    {
        if (!auth()->user()->puede('reportes', 'cotizaciones_cliente', 'ver')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }

        try {
            $fechas = $this->getFechasFiltro($request);
            $fechaInicio = $fechas['inicio'];
            $fechaFin = $fechas['fin'];
            
            $searchCliente = $request->input('search_cliente', '');
            $top = $request->input('top', 'todos');
            $sortBy = $request->input('sort_by', 'cotizaciones_desc');
            
            // Consulta base de clientes con cotizaciones
            $query = Cotizacion::query()
                ->join('fp_central_matriz.dbo.catalogo_cliente_maestro as c', 'crm_cotizaciones.id_cliente', '=', 'c.id_Cliente')
                ->whereBetween('crm_cotizaciones.fecha_creacion', [$fechaInicio, $fechaFin])
                ->where('crm_cotizaciones.activo', 1)
                ->select(
                    'c.id_Cliente',
                    'c.Nombre',
                    'c.apPaterno',
                    'c.apMaterno',
                    DB::raw('COUNT(DISTINCT crm_cotizaciones.id_cotizacion) as total_cotizaciones'),
                    DB::raw('SUM(CASE WHEN crm_cotizaciones.id_fase = 1 THEN 1 ELSE 0 END) as en_proceso'),
                    DB::raw('SUM(CASE WHEN crm_cotizaciones.id_fase = 2 THEN 1 ELSE 0 END) as completadas'),
                    DB::raw('SUM(CASE WHEN crm_cotizaciones.id_fase = 3 THEN 1 ELSE 0 END) as canceladas'),
                    DB::raw('SUM(crm_cotizaciones.importe_total) as importe_total'),
                    DB::raw('AVG(crm_cotizaciones.importe_total) as ticket_promedio'),
                    DB::raw('MAX(crm_cotizaciones.fecha_creacion) as ultima_cotizacion')
                )
                ->groupBy('c.id_Cliente', 'c.Nombre', 'c.apPaterno', 'c.apMaterno');
            
            // Filtro por cliente
            if (!empty($searchCliente)) {
                $query->having(DB::raw("CONCAT(c.Nombre, ' ', c.apPaterno, ' ', COALESCE(c.apMaterno, ''))"), 'LIKE', "%{$searchCliente}%");
            }
            
            // ============================================
            // APLICAR ORDENAMIENTO
            // ============================================
            switch($sortBy) {
                case 'cotizaciones_desc':
                    $query->orderBy('total_cotizaciones', 'DESC');
                    break;
                case 'cotizaciones_asc':
                    $query->orderBy('total_cotizaciones', 'ASC');
                    break;
                case 'monto_desc':
                    $query->orderBy('importe_total', 'DESC');
                    break;
                case 'monto_asc':
                    $query->orderBy('importe_total', 'ASC');
                    break;
                default:
                    $query->orderBy('total_cotizaciones', 'DESC');
            }
            
            // Aplicar top
            if ($top !== 'todos') {
                $query->limit((int)$top);
            }
            
            $clientes = $query->get();
            
            return response()->json([
                'success' => true,
                'data' => $clientes,
                'filtros' => [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en cotizaciones cliente data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Detalle de cotizaciones por cliente
     */
    public function detalleCliente(Request $request, $clienteId): View
    {
        if (!auth()->user()->puede('reportes', 'cotizaciones_cliente', 'ver')) {
            abort(403);
        }
        
        $cliente = Cliente::findOrFail($clienteId);
        
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];
        $statusFilter = $request->input('status_filter', 'todos');
        
        return view('reportes.cotizaciones_cliente.detalle_cliente', compact(
            'cliente', 'fechaInicio', 'fechaFin', 'statusFilter'
        ));
    }
    
    /**
     * Obtener datos del cliente (cotizaciones y grupos madre)
     */
    public function detalleData(Request $request, $clienteId): JsonResponse
    {
        if (!auth()->user()->puede('reportes', 'cotizaciones_cliente', 'ver')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            \Log::info('=== detalleData INICIO ===');
            \Log::info('Cliente ID: ' . $clienteId);
            
            $fechas = $this->getFechasFiltro($request);
            $fechaInicio = $fechas['inicio'];
            $fechaFin = $fechas['fin'];
            $statusFilter = $request->input('status_filter', 'todos');
            
            \Log::info('Fechas: ' . $fechaInicio . ' - ' . $fechaFin);
            
            // 1. Cotizaciones del cliente
            $query = Cotizacion::where('id_cliente', $clienteId)
                ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin])
                ->where('activo', 1);
            
            if ($statusFilter !== 'todos') {
                $estadoMap = ['proceso' => 1, 'completadas' => 2, 'canceladas' => 3];
                if (isset($estadoMap[$statusFilter])) {
                    $query->where('id_fase', $estadoMap[$statusFilter]);
                }
            }
            
            $cotizaciones = $query->orderBy('fecha_creacion', 'DESC')
                ->get(['id_cotizacion', 'folio', 'fecha_creacion', 'importe_total', 'id_fase']);

            \Log::info('Cotizaciones encontradas: ' . $cotizaciones->count());

            // Asignar el nombre del estado a cada cotización
            foreach ($cotizaciones as $cotizacion) {
                $cotizacion->estado_nombre = $this->getEstadoNombre($cotizacion->id_fase);
                \Log::info('Cotización: ' . $cotizacion->folio . ' - id_fase: ' . $cotizacion->id_fase . ' - estado: ' . $cotizacion->estado_nombre);
            }
            
        // 2. Datos para gráfica de grupos madre
        try {
            $gruposMadre = DB::connection('sqlsrv')  // ← Cambiar de sqlsrvV a sqlsrv
                ->table('crm_cotizaciones_detalle as ccd')
                ->join('crm_cotizaciones as c', 'ccd.id_cotizacion', '=', 'c.id_cotizacion')
                ->join('fp_central_matriz.dbo.catalogo_maestro as cm', 'cm.EAN', '=', 'ccd.codbar')
                ->join('fp_central_matriz.dbo.grupos_familias as gf', 'gf.numfamilia', '=', 'cm.numFam')
                ->where('c.id_cliente', $clienteId)
                ->whereBetween('c.fecha_creacion', [$fechaInicio, $fechaFin])
                ->where('c.activo', 1)
                ->whereNotNull('ccd.codbar')
                ->select(
                    'gf.id_grupo_madre',
                    'gf.descripciongrupomadre',
                    DB::raw('SUM(ccd.importe) as monto_total')
                )
                ->groupBy('gf.id_grupo_madre', 'gf.descripciongrupomadre')
                ->orderBy('monto_total', 'DESC')
                ->get();
                
            \Log::info('Grupos madre encontrados: ' . $gruposMadre->count());
        } catch (\Exception $e) {
            \Log::error('Error en consulta de grupos madre: ' . $e->getMessage());
            $gruposMadre = collect();
        }
            
            $totalGeneral = $gruposMadre->sum('monto_total');
            
            foreach ($gruposMadre as $grupo) {
                $grupo->porcentaje = $totalGeneral > 0 ? ($grupo->monto_total / $totalGeneral) * 100 : 0;
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'cotizaciones' => $cotizaciones,
                    'gruposMadre' => $gruposMadre,
                    'totalGeneral' => $totalGeneral,
                    'resumen' => [
                        'total_cotizaciones' => $cotizaciones->count(),
                        'importe_total' => $cotizaciones->sum('importe_total'),
                        'ticket_promedio' => $cotizaciones->avg('importe_total'),
                        'ultima_cotizacion' => $cotizaciones->first()?->fecha_creacion
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error en cotizaciones detalle data: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener productos de una cotización específica
     */
    public function getProductos($cotizacionId): JsonResponse
    {
        if (!auth()->user()->puede('reportes', 'cotizaciones_cliente', 'ver')) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso'], 403);
        }
        
        try {
            $productos = CotizacionDetalle::where('id_cotizacion', $cotizacionId)
                ->where('activo', 1)
                ->get();
            
            foreach ($productos as $producto) {
                $esExterno = str_starts_with($producto->codbar, 'T');
                
                if ($esExterno) {
                    $tmpProducto = TmpCatalogo::where('ean', $producto->codbar)->first();
                    $producto->descripcion = $tmpProducto?->descripcion ?? 'Producto externo';
                } else {
                    $productoInfo = CatalogoGeneral::where('ean', $producto->codbar)->first();
                    $producto->descripcion = $productoInfo->descripcion ?? 'Producto no encontrado';
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $productos
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error al obtener productos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Vista completa de productos de una cotización
     */
    public function vistaProductos(Request $request, $clienteId, $cotizacionId): View
    {
        if (!auth()->user()->puede('reportes', 'cotizaciones_cliente', 'ver')) {
            abort(403);
        }
        
        $cliente = Cliente::findOrFail($clienteId);
        
        $cotizacion = Cotizacion::where('id_cotizacion', $cotizacionId)
            ->where('id_cliente', $clienteId)
            ->firstOrFail();
        
        // Obtener productos de la cotización
        $productos = CotizacionDetalle::where('id_cotizacion', $cotizacionId)
            ->where('activo', 1)
            ->get();
        
        foreach ($productos as $producto) {
            $esExterno = str_starts_with($producto->codbar, 'T');
            
            if ($esExterno) {
                $tmpProducto = TmpCatalogo::where('ean', $producto->codbar)->first();
                $producto->descripcion = $tmpProducto?->descripcion ?? 'Producto externo';
            } else {
                $productoInfo = CatalogoGeneral::where('ean', $producto->codbar)->first();
                $producto->descripcion = $productoInfo->descripcion ?? 'Producto no encontrado';
            }
        }
        
        // Capturar filtros para regresar
        $filtroFecha = $request->input('filtro_fecha', 'este_mes');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $statusFilter = $request->input('status_filter', 'todos');
        $top = $request->input('top', 'todos');
        $sortBy = $request->input('sort_by', 'cotizaciones_desc');
        
        return view('reportes.cotizaciones_cliente.productos', compact(
            'cliente', 'cotizacion', 'productos', 'filtroFecha', 'fechaInicio', 'fechaFin', 'statusFilter', 'top', 'sortBy'
        ));
    }
    
    /**
     * Obtener fechas según filtro
     */
    private function getFechasFiltro(Request $request): array
    {
        $filtroFecha = $request->input('filtro_fecha', 'este_mes');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        
        if ($filtroFecha !== 'personalizado' && (!$fechaInicio || !$fechaFin)) {
            $hoy = now();
            switch ($filtroFecha) {
                case 'hoy':
                    $fechaInicio = $hoy->format('Y-m-d');
                    $fechaFin = $hoy->format('Y-m-d');
                    break;
                case 'esta_semana':
                    $fechaInicio = $hoy->copy()->startOfWeek()->format('Y-m-d');
                    $fechaFin = $hoy->copy()->endOfWeek()->format('Y-m-d');
                    break;
                case 'este_mes':
                    $fechaInicio = $hoy->copy()->startOfMonth()->format('Y-m-d');
                    $fechaFin = $hoy->copy()->endOfMonth()->format('Y-m-d');
                    break;
                case 'este_ano':
                    $fechaInicio = $hoy->copy()->startOfYear()->format('Y-m-d');
                    $fechaFin = $hoy->copy()->endOfYear()->format('Y-m-d');
                    break;
                default:
                    $fechaInicio = $hoy->copy()->startOfMonth()->format('Y-m-d');
                    $fechaFin = $hoy->copy()->endOfMonth()->format('Y-m-d');
            }
        }
        
        return ['inicio' => $fechaInicio, 'fin' => $fechaFin];
    }
    
    private function getEstadoNombre($idFase): string
    {
        $idFase = (int) $idFase;
        
        return match($idFase) {
            1 => 'En proceso',
            2 => 'Completada',
            3 => 'Cancelada',
            default => 'Desconocido'
        };
    }

    /**
     * Exportar a Excel
     */
    public function exportarExcel(Request $request)
    {
        if (!auth()->user()->puede('reportes', 'cotizaciones_cliente', 'ver')) {
            abort(403);
        }
        
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];
        $statusFilter = $request->input('status_filter', 'todos');
        $searchCliente = $request->input('search_cliente', '');
        $top = $request->input('top', 'todos');
        
        $query = Cotizacion::query()
            ->join('fp_central_matriz.dbo.catalogo_cliente_maestro as c', 'crm_cotizaciones.id_cliente', '=', 'c.id_Cliente')
            ->whereBetween('crm_cotizaciones.fecha_creacion', [$fechaInicio, $fechaFin])
            ->where('crm_cotizaciones.activo', 1)
            ->select(
                'c.id_Cliente',
                'c.Nombre',
                'c.apPaterno',
                'c.apMaterno',
                DB::raw('COUNT(DISTINCT crm_cotizaciones.id_cotizacion) as total_cotizaciones'),
                DB::raw('SUM(crm_cotizaciones.importe_total) as importe_total'),
                DB::raw('AVG(crm_cotizaciones.importe_total) as ticket_promedio'),
                DB::raw('MAX(crm_cotizaciones.fecha_creacion) as ultima_cotizacion')
            )
            ->groupBy('c.id_Cliente', 'c.Nombre', 'c.apPaterno', 'c.apMaterno');
        
        if ($statusFilter !== 'todos') {
            $estadoMap = ['proceso' => 1, 'completadas' => 2, 'canceladas' => 3];
            if (isset($estadoMap[$statusFilter])) {
                $query->where('crm_cotizaciones.id_fase', $estadoMap[$statusFilter]);
            }
        }
        
        if (!empty($searchCliente)) {
            $query->having(DB::raw("CONCAT(c.Nombre, ' ', c.apPaterno, ' ', COALESCE(c.apMaterno, ''))"), 'LIKE', "%{$searchCliente}%");
        }
        
        if ($top !== 'todos') {
            $query->limit((int)$top);
        }
        
        $clientes = $query->orderBy('importe_total', 'DESC')->get();
        
        $fechasExport = [
            'inicio' => $fechaInicio,
            'fin' => $fechaFin
        ];
        
        return (new CotizacionesClienteExport($clientes, $fechasExport, $statusFilter))
            ->download('cotizaciones_clientes_' . date('Y-m-d_His') . '.xlsx');
    }

    /**
     * Exportar a PDF
     */
    public function exportarPdf(Request $request)
    {
        if (!auth()->user()->puede('reportes', 'cotizaciones_cliente', 'ver')) {
            abort(403);
        }
        
        $fechas = $this->getFechasFiltro($request);
        $fechaInicio = $fechas['inicio'];
        $fechaFin = $fechas['fin'];
        $statusFilter = $request->input('status_filter', 'todos');
        $searchCliente = $request->input('search_cliente', '');
        $top = $request->input('top', 'todos');
        
        $query = Cotizacion::query()
            ->join('fp_central_matriz.dbo.catalogo_cliente_maestro as c', 'crm_cotizaciones.id_cliente', '=', 'c.id_Cliente')
            ->whereBetween('crm_cotizaciones.fecha_creacion', [$fechaInicio, $fechaFin])
            ->where('crm_cotizaciones.activo', 1)
            ->select(
                'c.id_Cliente',
                'c.Nombre',
                'c.apPaterno',
                'c.apMaterno',
                DB::raw('COUNT(DISTINCT crm_cotizaciones.id_cotizacion) as total_cotizaciones'),
                DB::raw('SUM(crm_cotizaciones.importe_total) as importe_total'),
                DB::raw('AVG(crm_cotizaciones.importe_total) as ticket_promedio'),
                DB::raw('MAX(crm_cotizaciones.fecha_creacion) as ultima_cotizacion')
            )
            ->groupBy('c.id_Cliente', 'c.Nombre', 'c.apPaterno', 'c.apMaterno');
        
        if ($statusFilter !== 'todos') {
            $estadoMap = ['proceso' => 1, 'completadas' => 2, 'canceladas' => 3];
            if (isset($estadoMap[$statusFilter])) {
                $query->where('crm_cotizaciones.id_fase', $estadoMap[$statusFilter]);
            }
        }
        
        if (!empty($searchCliente)) {
            $query->having(DB::raw("CONCAT(c.Nombre, ' ', c.apPaterno, ' ', COALESCE(c.apMaterno, ''))"), 'LIKE', "%{$searchCliente}%");
        }
        
        if ($top !== 'todos') {
            $query->limit((int)$top);
        }
        
        $clientes = $query->orderBy('importe_total', 'DESC')->get();
        
        $pdf = Pdf::loadView('reportes.cotizaciones_cliente.pdf', compact('clientes', 'fechaInicio', 'fechaFin', 'statusFilter'));
        $pdf->setPaper('letter', 'landscape');
        
        return $pdf->download('cotizaciones_clientes_' . date('Y-m-d_His') . '.pdf');
    }
}