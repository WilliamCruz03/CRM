<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cliente;
use App\Models\Cotizaciones\Cotizacion;
use App\Models\DashboardPreferencia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Definición central de todos los cards disponibles
    private $cardsDisponibles = [
        // Cards de acceso (estos se muestran según permisos del módulo, no por preferencia)
        ['key' => 'acceso_clientes', 'nombre' => 'Acceso a Clientes', 'tipo' => 'acceso', 'modulo' => 'clientes'],
        ['key' => 'acceso_cotizaciones', 'nombre' => 'Acceso a Cotizaciones', 'tipo' => 'acceso', 'modulo' => 'ventas'],
        
        // Cards KPI
        ['key' => 'kpi_total_clientes', 'nombre' => 'Total Clientes', 'tipo' => 'kpi', 'modulo' => 'clientes'],
        ['key' => 'kpi_contactos_proximos', 'nombre' => 'Contactos Próximos', 'tipo' => 'kpi', 'modulo' => 'clientes'],
        ['key' => 'kpi_total_cotizaciones', 'nombre' => 'Total Cotizaciones', 'tipo' => 'kpi', 'modulo' => 'ventas'],
        ['key' => 'kpi_cotizaciones_pendientes', 'nombre' => 'Cotizaciones Pendientes', 'tipo' => 'kpi', 'modulo' => 'ventas'],
        ['key' => 'kpi_monto_total_mes', 'nombre' => 'Monto Total del Mes', 'tipo' => 'kpi', 'modulo' => 'ventas'],
        
        // Cards de gráficos
        ['key' => 'grafico_estados_cotizaciones', 'nombre' => 'Estados de Cotizaciones', 'tipo' => 'grafico', 'modulo' => 'ventas'],
        
        // Cards de tablas
        ['key' => 'tabla_ultimos_contactos', 'nombre' => 'Últimos Contactos', 'tipo' => 'tabla', 'modulo' => 'clientes'],
        ['key' => 'tabla_ultimas_cotizaciones', 'nombre' => 'Últimas Cotizaciones', 'tipo' => 'tabla', 'modulo' => 'ventas'],
        
        // Cards de resumen
        ['key' => 'resumen_rapido', 'nombre' => 'Resumen Rápido', 'tipo' => 'resumen', 'modulo' => 'clientes'],
        ['key' => 'resumen_ventas_mensual', 'nombre' => 'Resumen de Ventas Mensual', 'tipo' => 'resumen', 'modulo' => 'ventas'],
    ];

    // Cards que se basan en permisos (no en preferencias)
    private $cardsBasadosEnPermisos = ['acceso_clientes', 'acceso_cotizaciones'];

    public function index()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Verificar si tiene algún permiso en el sistema
        $tieneAlgunPermiso = $user->permisosGranulares()
            ->where(function($query) {
                $query->where('mostrar', true)
                    ->orWhere('ver', true)
                    ->orWhere('crear', true)
                    ->orWhere('editar', true)
                    ->orWhere('eliminar', true);
            })
            ->exists();
        
        if (!$tieneAlgunPermiso) {
            return view('dashboard.sin-acceso', ['usuario' => $user->nombre_completo]);
        }
        
        // Verificar permisos de módulos
        $permisoDirectorio = $user->permisosGranulares()
            ->where('modulo', 'clientes')
            ->where('submodulo', 'directorio')
            ->first();
        
        $permisoCotizaciones = $user->permisosGranulares()
            ->where('modulo', 'ventas')
            ->where('submodulo', 'cotizaciones')
            ->first();
        
        $tienePermisoClientes = $permisoDirectorio && $permisoDirectorio->mostrar && ($permisoDirectorio->ver || $permisoDirectorio->crear || $permisoDirectorio->editar);
        $tienePermisoVentas = $permisoCotizaciones && $permisoCotizaciones->mostrar && ($permisoCotizaciones->ver || $permisoCotizaciones->crear || $permisoCotizaciones->editar);
        
        // Obtener preferencias del dashboard (solo para cards que no son de acceso)
        $preferencias = DashboardPreferencia::where('id_personal_empresa', $user->id_personal_empresa)
            ->where('mostrar', true)
            ->pluck('card_key')
            ->toArray();
        
        // Preparar datos para cada card
        $datosCards = [];
        
        // 1. Cards de acceso (basados en permisos, no en preferencias)
        if ($tienePermisoClientes) {
            $datosCards['acceso_clientes'] = $this->cargarDatosCard('acceso_clientes', null, $user);
        }
        if ($tienePermisoVentas) {
            $datosCards['acceso_cotizaciones'] = $this->cargarDatosCard('acceso_cotizaciones', null, $user);
        }
        
        // 2. Cards no acceso (basados en preferencias)
        foreach ($this->cardsDisponibles as $card) {
            $cardKey = $card['key'];
            
            // Saltar cards de acceso (ya procesados)
            if (in_array($cardKey, $this->cardsBasadosEnPermisos)) {
                continue;
            }
            
            // Verificar si el usuario tiene este card en sus preferencias
            if (!in_array($cardKey, $preferencias)) {
                continue;
            }
            
            // Verificar permisos del módulo asociado
            if ($card['modulo'] === 'clientes' && !$tienePermisoClientes) {
                continue;
            }
            if ($card['modulo'] === 'ventas' && !$tienePermisoVentas) {
                continue;
            }
            
            $datosCards[$cardKey] = $this->cargarDatosCard($cardKey, $card, $user);
        }
        
        // Obtener módulos con acceso para el header
        $modulosAcceso = $user->permisosGranulares()
            ->where(function($query) {
                $query->where('mostrar', true)
                    ->orWhere('ver', true)
                    ->orWhere('crear', true)
                    ->orWhere('editar', true)
                    ->orWhere('eliminar', true);
            })
            ->distinct()
            ->pluck('modulo')
            ->unique()
            ->toArray();
        
        // Datos legacy para compatibilidad con la vista actual
        $mostrarCardClientes = $tienePermisoClientes;
        $mostrarCardCotizaciones = $tienePermisoVentas;
        
        $permisosClientes = [
            'ver' => $permisoDirectorio && $permisoDirectorio->ver === true,
            'crear' => $permisoDirectorio && $permisoDirectorio->crear === true,
            'editar' => $permisoDirectorio && $permisoDirectorio->editar === true,
            'eliminar' => $permisoDirectorio && $permisoDirectorio->eliminar === true,
            'mostrar' => $permisoDirectorio && $permisoDirectorio->mostrar === true,
        ];
        
        $permisosCotizaciones = [
            'ver' => $permisoCotizaciones && $permisoCotizaciones->ver === true,
            'crear' => $permisoCotizaciones && $permisoCotizaciones->crear === true,
            'editar' => $permisoCotizaciones && $permisoCotizaciones->editar === true,
            'eliminar' => $permisoCotizaciones && $permisoCotizaciones->eliminar === true,
            'mostrar' => $permisoCotizaciones && $permisoCotizaciones->mostrar === true,
        ];
        
        // ==============================================
        // Inicializar variables
        // ==============================================
        $mesAnterior = now()->subMonth();
        
        // Variables para KPI de cotizaciones
        $totalCotizaciones = 0;
        $cotizacionesPendientes = 0;
        $estadosCotizaciones = ['aceptadas' => 0, 'pendientes' => 0, 'rechazadas' => 0];
        $montosEsteMesCotizaciones = 0;
        $montosEsteMesPedidos = 0;
        $porcentajeCambioCotizaciones = 0;
        $porcentajeCambioPedidos = 0;
        $porcentajeCotizaciones = 0;
        $ultimasCotizaciones = [];
        $tasaConversion = 0;
        
        // Variables para KPI de clientes
        $totalClientes = $tienePermisoClientes ? Cliente::where('status', 'CLIENTE')->count() : 0;
        $contactosProximos = 0;
        $ultimosContactos = [];
        
        // Variables para control de visibilidad
        $mostrarKpiMontoTotalMes = false;
        $mostrarResumenVentasMensual = false;
        $resumenVentasMensual = null;
        
        // ==============================================
        // VERIFICAR PREFERENCIAS
        // ==============================================
        // Verificar si el KPI de monto total del mes está en preferencias
        if ($tienePermisoVentas && in_array('kpi_monto_total_mes', $preferencias)) {
            $mostrarKpiMontoTotalMes = true;
        }

        // Verificar si el card de resumen de ventas mensual está en preferencias
        // Usando el mismo permiso que $mostrarKpiMontoTotalMes
        if ($tienePermisoVentas && $mostrarKpiMontoTotalMes) {
            $mostrarResumenVentasMensual = true;
            $resumenVentasMensual = $this->getResumenVentasMensual();
        }
        
        // ==============================================
        // DATOS DE COTIZACIONES - MENSUALES
        // ==============================================
        if ($tienePermisoVentas) {
            // Obtener fechas del mes actual
            $inicioMes = now()->startOfMonth();
            $finMes = now()->endOfMonth();
            
            // TOTALES DEL MES (todas las cotizaciones activas del mes)
            $totalCotizaciones = Cotizacion::where('activo', 1)
                ->whereBetween('fecha_creacion', [$inicioMes, $finMes])
                ->count();
            
            // EN PROCESO (fase 1) del mes
            $cotizacionesPendientes = Cotizacion::where('activo', 1)
                ->where('id_fase', 1)
                ->whereBetween('fecha_creacion', [$inicioMes, $finMes])
                ->count();
            
            // ESTADOS del mes
            $estadosCotizaciones = [
                "aceptadas" => Cotizacion::where('activo', 1)
                    ->where('id_fase', 2)
                    ->whereBetween('fecha_creacion', [$inicioMes, $finMes])
                    ->count(),
                "pendientes" => Cotizacion::where('activo', 1)
                    ->where('id_fase', 1)
                    ->whereBetween('fecha_creacion', [$inicioMes, $finMes])
                    ->count(),
                "rechazadas" => Cotizacion::where('activo', 1)
                    ->where('id_fase', 3)
                    ->whereBetween('fecha_creacion', [$inicioMes, $finMes])
                    ->count()
            ];
            
            // Calcular montos de cotizaciones del mes actual y mes anterior
            $montosEsteMesCotizaciones = Cotizacion::where('activo', 1)
                ->where('es_pedido', '!=', 1)
                ->whereBetween('fecha_creacion', [$inicioMes, $finMes])
                ->sum('importe_total');
            
            $inicioMesAnterior = $mesAnterior->copy()->startOfMonth();
            $finMesAnterior = $mesAnterior->copy()->endOfMonth();
            
            $montosMesAnteriorCotizaciones = Cotizacion::where('activo', 1)
                ->where('es_pedido', '!=', 1)
                ->whereBetween('fecha_creacion', [$inicioMesAnterior, $finMesAnterior])
                ->sum('importe_total');
            
            // Calcular porcentaje de cambio
            if ($montosMesAnteriorCotizaciones > 0) {
                $porcentajeCambioCotizaciones = (($montosEsteMesCotizaciones - $montosMesAnteriorCotizaciones) / $montosMesAnteriorCotizaciones) * 100;
            } else {
                $porcentajeCambioCotizaciones = $montosEsteMesCotizaciones > 0 ? 100 : 0;
            }
            
            // Calcular montos de pedidos del mes actual y mes anterior
            $montosEsteMesPedidos = Cotizacion::where('activo', 1)
                ->where('es_pedido', 1)
                ->whereBetween('fecha_creacion', [$inicioMes, $finMes])
                ->sum('importe_total');
            
            $montosMesAnteriorPedidos = Cotizacion::where('activo', 1)
                ->where('es_pedido', 1)
                ->whereBetween('fecha_creacion', [$inicioMesAnterior, $finMesAnterior])
                ->sum('importe_total');
            
            if ($montosMesAnteriorPedidos > 0) {
                $porcentajeCambioPedidos = (($montosEsteMesPedidos - $montosMesAnteriorPedidos) / $montosMesAnteriorPedidos) * 100;
            }
            
            // Si el KPI no está activo, ponemos los montos en 0
            if (!$mostrarKpiMontoTotalMes) {
                $montosEsteMesCotizaciones = 0;
                $montosEsteMesPedidos = 0;
                $porcentajeCambioCotizaciones = 0;
                $porcentajeCambioPedidos = 0;
            }
            
            // Calcular porcentaje de cotizaciones vs mes anterior
            $cotizacionesMesAnterior = Cotizacion::where('activo', 1)
                ->whereBetween('fecha_creacion', [$inicioMesAnterior, $finMesAnterior])
                ->count();
            
            if ($cotizacionesMesAnterior > 0) {
                $porcentajeCotizaciones = (($totalCotizaciones - $cotizacionesMesAnterior) / $cotizacionesMesAnterior) * 100;
            }
            
            // Últimas cotizaciones del mes
            $cotizacionesEnProceso = Cotizacion::with('cliente', 'fase')
                ->where('activo', 1)
                ->where('es_pedido', '!=', 1)
                ->where('id_fase', 1)
                ->whereBetween('fecha_creacion', [$inicioMes, $finMes])
                ->orderBy('fecha_creacion', 'desc')
                ->limit(3)
                ->get();

            // Si hay menos de 3, completar con "Completadas" (fase 2) que NO son pedidos
            $cotizacionesCompletadas = collect();
            if ($cotizacionesEnProceso->count() < 3) {
                $restantes = 3 - $cotizacionesEnProceso->count();
                $cotizacionesCompletadas = Cotizacion::with('cliente', 'fase')
                    ->where('activo', 1)
                    ->where('es_pedido', '!=', 1)
                    ->where('id_fase', 2)
                    ->whereBetween('fecha_creacion', [$inicioMes, $finMes])
                    ->orderBy('fecha_creacion', 'desc')
                    ->limit($restantes)
                    ->get();
            }

            // Unir ambas colecciones
            $ultimasCotizaciones = $cotizacionesEnProceso->concat($cotizacionesCompletadas)
                ->map(function($cotizacion) {
                    $estado = $cotizacion->fase->fase ?? 'Desconocido';
                    $estadoMap = [
                        'En proceso' => 'pendiente',
                        'Completada' => 'aceptada',
                        'Cancelada' => 'rechazada'
                    ];
                    
                    return (object)[
                        'id' => $cotizacion->id_cotizacion,
                        'cliente' => (object)['nombre' => $cotizacion->cliente->nombre_completo ?? 'N/A'],
                        'estado' => $estadoMap[$estado] ?? 'pendiente',
                        'total' => $cotizacion->importe_total
                    ];
                });
            
            // Tasa de conversión del mes (cotizaciones completadas / total cotizaciones)
            if ($totalCotizaciones > 0) {
                $tasaConversion = ($estadosCotizaciones['aceptadas'] / $totalCotizaciones) * 100;
            }
        }
        
        // ==============================================
        // DATOS PARA RESUMEN RÁPIDO
        // ==============================================
        $clienteTopData = $this->getClienteTopCRM();
        $clienteTop = $clienteTopData->nombre;
        $ticketPromedio = $this->getTicketPromedioCRM();
        $frecuenciaPromedio = $this->getFrecuenciaPromedioCRM($clienteTopData->id);
        $tasaConversion = $this->getTasaConversionCRM();
        
        // ==============================================
        // RETORNAR VISTA CON TODAS LAS VARIABLES
        // ==============================================
        return view("dashboard.index", compact(
            "totalClientes",
            "totalCotizaciones",
            "cotizacionesPendientes",
            "contactosProximos",
            "estadosCotizaciones",
            "montosEsteMesCotizaciones",
            "montosEsteMesPedidos",
            "porcentajeCambioCotizaciones",
            "porcentajeCambioPedidos",
            "porcentajeCotizaciones",
            "ultimosContactos",
            "ultimasCotizaciones",
            "modulosAcceso",
            "mostrarCardClientes",
            "mostrarCardCotizaciones",
            "permisosClientes",
            "permisosCotizaciones",
            "tasaConversion",
            "clienteTop",
            "ticketPromedio",
            "frecuenciaPromedio",
            "tieneAlgunPermiso",
            "datosCards",
            "mostrarKpiMontoTotalMes",
            "mostrarResumenVentasMensual",
            "resumenVentasMensual"
        ));
    }
    
    private function cargarDatosCard($cardKey, $card, $user)
    {
        // Aquí puedes cargar datos específicos para cada card si es necesario
        // Por ahora retornamos el card con sus datos básicos
        return $card ?? ['key' => $cardKey];
    }

    /**
     * Obtener el cliente con mayor monto total en pedidos completados (status 3)
     */
    private function getClienteTopCRM()
    {
        $fechaInicio = \Carbon\Carbon::now()->startOfMonth();
        $fechaFin = \Carbon\Carbon::now()->endOfMonth();
        
        // Buscar cliente con más monto en pedidos completados (status 3)
        $clienteTop = Cotizacion::where('activo', 1)
            ->where('id_fase', 3) // Status 3 = Completado
            ->where('es_pedido', 1) // Solo pedidos
            ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin])
            ->select('id_cliente', \DB::raw('SUM(importe_total) as total_gastado'))
            ->groupBy('id_cliente')
            ->orderBy('total_gastado', 'DESC')
            ->with('cliente')
            ->first();
        
        if (!$clienteTop || !$clienteTop->cliente) {
            return (object) [
                'nombre' => 'Sin datos',
                'id' => null,
                'total_gastado' => 0
            ];
        }
        
        return (object) [
            'nombre' => $clienteTop->cliente->nombre_completo,
            'id' => $clienteTop->id_cliente,
            'total_gastado' => $clienteTop->total_gastado
        ];
    }

    /**
     * Calcular ticket promedio de pedidos completados (status 3)
     */
    private function getTicketPromedioCRM()
    {
        $fechaInicio = \Carbon\Carbon::now()->startOfMonth();
        $fechaFin = \Carbon\Carbon::now()->endOfMonth();
        
        $promedio = Cotizacion::where('activo', 1)
            ->where('id_fase', 3) // Status 3 = Completado
            ->where('es_pedido', 1)
            ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin])
            ->avg('importe_total');
        
        return $promedio ?? 0;
    }

    /**
     * Calcular frecuencia promedio de compra del cliente top (mes actual)
     * Basado en pedidos completados (status 3)
     */
    private function getFrecuenciaPromedioCRM($clienteId)
    {
        if (!$clienteId) return 0;
        
        $fechaInicio = \Carbon\Carbon::now()->startOfMonth();
        $fechaFin = \Carbon\Carbon::now()->endOfMonth();
        
        // Obtener fechas de pedidos completados del cliente
        $fechasCompras = Cotizacion::where('activo', 1)
            ->where('id_cliente', $clienteId)
            ->where('id_fase', 3) // Status 3 = Completado
            ->where('es_pedido', 1)
            ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin])
            ->orderBy('fecha_creacion', 'ASC')
            ->pluck('fecha_creacion')
            ->toArray();
        
        $totalCompras = count($fechasCompras);
        
        if ($totalCompras < 2) {
            return 0;
        }
        
        $totalDias = 0;
        for ($i = 1; $i < $totalCompras; $i++) {
            $fechaAnterior = new \Carbon\Carbon($fechasCompras[$i - 1]);
            $fechaActual = new \Carbon\Carbon($fechasCompras[$i]);
            $totalDias += $fechaAnterior->diffInDays($fechaActual);
        }
        
        $frecuencia = round($totalDias / ($totalCompras - 1), 1);
        return max(0, $frecuencia);
    }

    /**
     * Calcular tasa de conversión (cotizaciones que pasaron a pedidos completados status 3)
     */
    private function getTasaConversionCRM()
    {
        $fechaInicio = \Carbon\Carbon::now()->startOfMonth();
        $fechaFin = \Carbon\Carbon::now()->endOfMonth();
        
        // Total de cotizaciones del mes (no pedidos)
        $totalCotizaciones = Cotizacion::where('activo', 1)
            ->where('es_pedido', '!=', 1)
            ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin])
            ->count();
        
        if ($totalCotizaciones == 0) return 0;
        
        // Total de pedidos completados (status 3) del mes
        $pedidosCompletados = Cotizacion::where('activo', 1)
            ->where('es_pedido', 1)
            ->where('id_fase', 3) // Status 3 = Completado
            ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin])
            ->count();
        
        return ($pedidosCompletados / $totalCotizaciones) * 100;
    }

    /**
     * Obtener resumen de ventas mensuales desde historial_ventas_matriz
     */
    private function getResumenVentasMensual()
    {
        try {
            $fechaInicio = \Carbon\Carbon::now()->startOfMonth();
            $fechaFin = \Carbon\Carbon::now()->endOfMonth();
            $mesAnterior = \Carbon\Carbon::now()->subMonth();
            
            // IDs del público en general
            $idsPublico = ['0000000007295', '0000000004489'];
            
            // Total general del mes actual
            $totalGeneral = DB::connection('sqlsrvV')
                ->table('historial_ventas_matriz')
                ->whereBetween('FECHA_DT', [$fechaInicio, $fechaFin])
                ->where(function($q) {
                    $q->whereNull('F_STATUS')
                    ->orWhereNotIn('F_STATUS', ['C', 'D']);
                })
                ->where(DB::raw('CAST(F_MONTO AS DECIMAL(18,2))'), '>', 0)
                ->sum(DB::raw('CAST(F_MONTO AS DECIMAL(18,2))'));
            
            // Total de clientes registrados
            $totalRegistrados = DB::connection('sqlsrvV')
                ->table('historial_ventas_matriz')
                ->whereBetween('FECHA_DT', [$fechaInicio, $fechaFin])
                ->whereNotIn('IDCLIENTE', $idsPublico)
                ->where(function($q) {
                    $q->whereNull('F_STATUS')
                    ->orWhereNotIn('F_STATUS', ['C', 'D']);
                })
                ->where(DB::raw('CAST(F_MONTO AS DECIMAL(18,2))'), '>', 0)
                ->sum(DB::raw('CAST(F_MONTO AS DECIMAL(18,2))'));
            
            // Total de público en general
            $totalPublico = DB::connection('sqlsrvV')
                ->table('historial_ventas_matriz')
                ->whereBetween('FECHA_DT', [$fechaInicio, $fechaFin])
                ->whereIn('IDCLIENTE', $idsPublico)
                ->where(function($q) {
                    $q->whereNull('F_STATUS')
                    ->orWhereNotIn('F_STATUS', ['C', 'D']);
                })
                ->where(DB::raw('CAST(F_MONTO AS DECIMAL(18,2))'), '>', 0)
                ->sum(DB::raw('CAST(F_MONTO AS DECIMAL(18,2))'));
            
            // Total mes anterior
            $totalMesAnterior = DB::connection('sqlsrvV')
                ->table('historial_ventas_matriz')
                ->whereBetween('FECHA_DT', [
                    $mesAnterior->startOfMonth(),
                    $mesAnterior->endOfMonth()
                ])
                ->where(function($q) {
                    $q->whereNull('F_STATUS')
                    ->orWhereNotIn('F_STATUS', ['C', 'D']);
                })
                ->where(DB::raw('CAST(F_MONTO AS DECIMAL(18,2))'), '>', 0)
                ->sum(DB::raw('CAST(F_MONTO AS DECIMAL(18,2))'));
            
            // Porcentaje de cambio
            $porcentajeCambio = 0;
            if ($totalMesAnterior > 0) {
                $porcentajeCambio = (($totalGeneral - $totalMesAnterior) / $totalMesAnterior) * 100;
            }
            
            // Top 3 clientes
            $topClientes = DB::connection('sqlsrvV')
                ->table('historial_ventas_matriz as h')
                ->join('fp_central_matriz.dbo.catalogo_cliente_maestro as c', 'h.IDCLIENTE', '=', 'c.idtarjetaclientefrecuente')
                ->whereBetween('h.FECHA_DT', [$fechaInicio, $fechaFin])
                ->whereNotIn('h.IDCLIENTE', $idsPublico)
                ->where(function($q) {
                    $q->whereNull('h.F_STATUS')
                    ->orWhereNotIn('h.F_STATUS', ['C', 'D']);
                })
                ->where(DB::raw('CAST(h.F_MONTO AS DECIMAL(18,2))'), '>', 0)
                ->select(
                    'c.id_Cliente',
                    'c.Nombre',
                    'c.apPaterno',
                    'c.apMaterno',
                    DB::raw('SUM(CAST(h.F_MONTO AS DECIMAL(18,2))) as monto_total')
                )
                ->groupBy('c.id_Cliente', 'c.Nombre', 'c.apPaterno', 'c.apMaterno')
                ->orderBy('monto_total', 'DESC')
                ->limit(3)
                ->get();
            
            // Calcular porcentajes
            $porcentajeRegistrados = $totalGeneral > 0 ? ($totalRegistrados / $totalGeneral) * 100 : 0;
            $porcentajePublico = $totalGeneral > 0 ? ($totalPublico / $totalGeneral) * 100 : 0;
            
            return (object) [
                'total_general' => $totalGeneral ?? 0,
                'total_registrados' => $totalRegistrados ?? 0,
                'total_publico' => $totalPublico ?? 0,
                'porcentaje_cambio' => $porcentajeCambio,
                'porcentaje_registrados' => $porcentajeRegistrados,
                'porcentaje_publico' => $porcentajePublico,
                'top_clientes' => $topClientes,
                'ids_publico' => $idsPublico,
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error en getResumenVentasMensual: ' . $e->getMessage());
            return (object) [
                'total_general' => 0,
                'total_registrados' => 0,
                'total_publico' => 0,
                'porcentaje_cambio' => 0,
                'porcentaje_registrados' => 0,
                'porcentaje_publico' => 0,
                'top_clientes' => collect(),
                'ids_publico' => [],
            ];
        }
    }
}