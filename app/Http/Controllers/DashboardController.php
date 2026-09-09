<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cliente;
use App\Models\Cotizaciones\Cotizacion;
use App\Models\DashboardPreferencia;
use Illuminate\Support\Facades\DB;
use App\Models\AgendaContacto\AgendaContacto;
use App\Models\Pedidos\OrdenPedido;
use App\Models\Pedidos\OrdenPedidoSucursal;
Use App\Models\Sucursal;
use App\Models\PersonalEmpresa;

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

        // Nuevos cards
        ['key' => 'kpi_tasa_conversion', 'nombre' => 'Tasa de Conversión', 'tipo' => 'kpi', 'modulo' => 'ventas'],
        ['key' => 'kpi_pedidos_sucursal', 'nombre' => 'Pedidos por Sucursal', 'tipo' => 'kpi', 'modulo' => 'ventas'],
        ['key' => 'kpi_ventas_vendedor', 'nombre' => 'Ventas por Vendedor', 'tipo' => 'kpi', 'modulo' => 'ventas'],
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
        // Inicializar variables y fechas (UNA SOLA VEZ)
        // ==============================================
        $mesAnterior = now()->subMonth();
        $fechaInicio = now()->startOfMonth();
        $fechaFin = now()->endOfMonth();
        
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
        // INDEPENDIENTE de $mostrarKpiMontoTotalMes
        if ($tienePermisoVentas && in_array('resumen_ventas_mensual', $preferencias)) {
            $mostrarResumenVentasMensual = true;
            $resumenVentasMensual = $this->getResumenVentasMensual();
        }

        // Variables para saber si hay cards habilitados
        $hayCardsClientes = false;
        $hayCardsVentas = false;

        // Verificar cards de clientes habilitados
        if ($tienePermisoClientes) {
            $cardsClientes = ['kpi_total_clientes', 'kpi_contactos_proximos', 'tabla_ultimos_contactos', 'resumen_rapido'];
            foreach ($cardsClientes as $cardKey) {
                if (in_array($cardKey, $preferencias)) {
                    $hayCardsClientes = true;
                    break;
                }
            }
        }

        // Verificar cards de ventas habilitados
        if ($tienePermisoVentas) {
            $cardsVentas = ['kpi_total_cotizaciones', 'kpi_cotizaciones_pendientes', 'kpi_monto_total_mes', 'grafico_estados_cotizaciones', 'tabla_ultimas_cotizaciones', 'resumen_ventas_mensual'];
            foreach ($cardsVentas as $cardKey) {
                if (in_array($cardKey, $preferencias)) {
                    $hayCardsVentas = true;
                    break;
                }
            }
        }

        $hayCardsHabilitados = $hayCardsClientes || $hayCardsVentas;
        $tieneAccesoAModulos = $tienePermisoClientes || $tienePermisoVentas;
        $mostrarMensajeSinCards = $tieneAccesoAModulos && !$hayCardsHabilitados;

        // ==============================================
        // DATOS DE CONTACTOS PRÓXIMOS
        // ==============================================
        if ($tienePermisoClientes && in_array('kpi_contactos_proximos', $preferencias)) {
            try {
                $minutosNotificacion = DB::connection('sqlsrv')
                    ->table('crm_configuraciones')
                    ->where('nombre', 'notificaciones_minutos')
                    ->value('valor') ?? 60;
            } catch (\Exception $e) {
                $minutosNotificacion = 60;
            }
            
            $ahora = now();
            
            // Contar contactos que cumplen estas condiciones:
            // 1. Son pendientes (estado = 1) y activos
            // 2. Y (fecha/hora es futura O está dentro del rango de notificación)
            $contactosProximos = AgendaContacto::where('estado', 1)
                ->where('activo', 1)
                ->where(function($query) use ($ahora, $minutosNotificacion) {
                    // Caso 1: Contactos futuros (fecha/hora >= ahora)
                    $query->whereRaw("CAST(fecha AS DATETIME) + CAST(hora AS DATETIME) >= ?", [$ahora])
                        // Caso 2: Contactos que ya pasaron pero están dentro del rango de notificación
                        ->orWhereRaw("
                            CAST(fecha AS DATETIME) + CAST(hora AS DATETIME) >= ? 
                            AND CAST(fecha AS DATETIME) + CAST(hora AS DATETIME) <= ?
                        ", [
                            $ahora->copy()->subMinutes($minutosNotificacion),
                            $ahora
                        ]);
                })
                ->count();
        } else {
            $contactosProximos = 0;
        }

        // ==============================================
        // DATOS DE CONTACTOS - ÚLTIMOS CONTACTOS
        // ==============================================
        if ($tienePermisoClientes && in_array('tabla_ultimos_contactos', $preferencias)) {
            $ultimosContactos = AgendaContacto::where('activo', 1)
                ->whereBetween('fecha', [$fechaInicio, $fechaFin])
                ->orderBy('fecha', 'desc')
                ->orderBy('hora', 'desc')
                ->limit(3)
                ->get()
                ->map(function($contacto) {
                    // Obtener cliente desde la otra base
                    $cliente = DB::connection('sqlsrvM')
                        ->table('catalogo_cliente_maestro')
                        ->where('id_Cliente', $contacto->id_cliente)
                        ->first();
                    
                    $nombreCliente = $cliente ? trim(($cliente->Nombre ?? '') . ' ' . ($cliente->apPaterno ?? '') . ' ' . ($cliente->apMaterno ?? '')) : 'N/A';
                    
                    return (object)[
                        'cliente' => (object)['nombre' => $nombreCliente ?: 'N/A'],
                        'fecha_contacto' => \Carbon\Carbon::parse($contacto->fecha),
                        'completado' => $contacto->estado == 2, // Realizado
                        'estado_nombre' => $contacto->estado_nombre,
                    ];
                });
        } else {
            $ultimosContactos = collect();
        }
                
        // ==============================================
        // DATOS DE COTIZACIONES - MENSUALES
        // ==============================================
        if ($tienePermisoVentas) {
            // Monto TOTAL de cotizaciones CREADAS en el mes (TODAS las fases, incluyendo convertidas)
            $montosEsteMesCotizaciones = Cotizacion::where('activo', 1)
                ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin])
                ->sum('importe_total');

            // Monto TOTAL de cotizaciones que se convirtieron en pedido este mes
            $idsPedidosMes = OrdenPedido::where('activo', 1)
                ->whereIn('status', [2, 3])
                ->whereBetween('fecha_pedido', [$fechaInicio, $fechaFin])
                ->pluck('id_cotizacion')
                ->unique()
                ->toArray();

            $montosEsteMesPedidos = Cotizacion::where('activo', 1)
                ->whereIn('id_cotizacion', $idsPedidosMes)
                ->sum('importe_total');
            
            // Porcentajes de cambio vs mes anterior (para cotizaciones)
            $inicioMesAnterior = $mesAnterior->copy()->startOfMonth();
            $finMesAnterior = $mesAnterior->copy()->endOfMonth();
            
            $montosMesAnteriorCotizaciones = Cotizacion::where('activo', 1)
                ->where('es_pedido', '!=', 1)
                ->whereBetween('fecha_creacion', [$inicioMesAnterior, $finMesAnterior])
                ->sum('importe_total');
            
            if ($montosMesAnteriorCotizaciones > 0) {
                $porcentajeCambioCotizaciones = (($montosEsteMesCotizaciones - $montosMesAnteriorCotizaciones) / $montosMesAnteriorCotizaciones) * 100;
            } else {
                $porcentajeCambioCotizaciones = $montosEsteMesCotizaciones > 0 ? 100 : 0;
            }
            
            // Porcentajes de cambio vs mes anterior (para pedidos)
            $idsPedidosMesAnterior = OrdenPedido::where('activo', 1)
                ->whereIn('status', [2, 3])
                ->whereBetween('fecha_pedido', [$inicioMesAnterior, $finMesAnterior])
                ->pluck('id_cotizacion')
                ->unique()
                ->toArray();
            
            $montosMesAnteriorPedidos = Cotizacion::where('activo', 1)
                ->whereIn('id_cotizacion', $idsPedidosMesAnterior)
                ->sum('importe_total');
            
            if ($montosMesAnteriorPedidos > 0) {
                $porcentajeCambioPedidos = (($montosEsteMesPedidos - $montosMesAnteriorPedidos) / $montosMesAnteriorPedidos) * 100;
            } else {
                $porcentajeCambioPedidos = $montosEsteMesPedidos > 0 ? 100 : 0;
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
                ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin])
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
                    ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin])
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
        $clienteTopId = $clienteTopData->id;
        $clienteTopGastado = $clienteTopData->total_gastado;
        $clienteTopPedidos = $clienteTopData->total_pedidos;
        $tasaConversionClienteTop = $this->getTasaConversionCRM($clienteTopId);
        $ticketPromedio = $this->getTicketPromedioCRM();
        $frecuenciaPromedio = $this->getFrecuenciaPromedioCRM($clienteTopId);

        // ==============================================
        // KPI - TASA DE CONVERSION, PEDIDOS POR SUCURSAL, VENTAS POR VENDEDOR
        // ==============================================
        $tasaConversion = 0;
        $tasaConversionData = null;
        $pedidosSucursal = null;
        $ventasVendedor = null;
        $mostrarKpiTasaConversion = false;
        $mostrarKpiPedidosSucursal = false;
        $mostrarKpiVentasVendedor = false;

        // Indicadores de tiempo
        $tiempoPromedioCotizacionAPedido = 0;
        $tiempoPromedioPedidoAEntrega = 0;

        // Verificar si el usuario es CRM (tiene el perfil CRM activo)
        $esCrm = $user->es_crm ?? false;

        if ($tienePermisoVentas && $esCrm) {
            // Mostrar siempre estos KPI a usuarios CRM con permisos de ventas
            $mostrarKpiTasaConversion = true;
            $tasaConversionData = $this->getTasaConversionGeneral();
            $tasaConversion = $tasaConversionData->tasa;
            
            $mostrarKpiPedidosSucursal = true;
            $pedidosSucursal = $this->getPedidosPorSucursal();
            
            $mostrarKpiVentasVendedor = true;
            $ventasVendedor = $this->getVentasPorVendedor();
            
            // Calcular tiempos promedio
            $tiempoPromedioCotizacionAPedido = $this->getTiempoPromedioCotizacionAPedido();
            $tiempoPromedioPedidoAEntrega = $this->getTiempoPromedioPedidoAEntrega();
        }
                
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
            "tasaConversionData",
            "pedidosSucursal",
            "clienteTop",
            "clienteTopGastado",
            "clienteTopPedidos",
            "tasaConversionClienteTop",
            "ticketPromedio",
            "frecuenciaPromedio",
            "tieneAlgunPermiso",
            "datosCards",
            "mostrarKpiMontoTotalMes",
            "mostrarResumenVentasMensual",
            "resumenVentasMensual",
            "hayCardsHabilitados",
            "mostrarMensajeSinCards",
            "tieneAccesoAModulos",
            "pedidosSucursal",
            "ventasVendedor",
            "mostrarKpiTasaConversion",
            "mostrarKpiPedidosSucursal",
            "mostrarKpiVentasVendedor",
            "tiempoPromedioCotizacionAPedido",
            "tiempoPromedioPedidoAEntrega",
            "tienePermisoVentas",
            "preferencias"
        ));
    }
    
    private function cargarDatosCard($cardKey, $card, $user)
    {
        // Aquí puedes cargar datos específicos para cada card si es necesario
        // Por ahora retornamos el card con sus datos básicos
        return $card ?? ['key' => $cardKey];
    }

    /**
     * Obtener el cliente con mayor monto total en pedidos (status 2 o 3)
     * Basado en pedidos generados en el mes actual
     */
    private function getClienteTopCRM()
    {
        $fechaInicio = \Carbon\Carbon::now()->startOfMonth();
        $fechaFin = \Carbon\Carbon::now()->endOfMonth();
        
        // Obtener IDs de cotizaciones convertidas en el mes
        $idsCotizacionesConvertidas = OrdenPedido::where('activo', 1)
            ->whereIn('status', [2, 3])
            ->whereBetween('fecha_pedido', [$fechaInicio, $fechaFin])
            ->pluck('id_cotizacion')
            ->unique()
            ->toArray();
        
        if (empty($idsCotizacionesConvertidas)) {
            return (object) [
                'nombre' => 'Sin datos',
                'id' => null,
                'total_gastado' => 0,
                'total_pedidos' => 0
            ];
        }
        
        // Cliente con mayor gasto en cotizaciones convertidas del mes
        $clienteTop = Cotizacion::where('activo', 1)
            ->whereIn('id_cotizacion', $idsCotizacionesConvertidas)
            ->select(
                'id_cliente',
                DB::raw('SUM(importe_total) as total_gastado'),
                DB::raw('COUNT(id_cotizacion) as total_pedidos')
            )
            ->groupBy('id_cliente')
            ->orderBy('total_gastado', 'DESC')
            ->first();
        
        if (!$clienteTop) {
            return (object) [
                'nombre' => 'Sin datos',
                'id' => null,
                'total_gastado' => 0,
                'total_pedidos' => 0
            ];
        }
        
        $cliente = Cliente::find($clienteTop->id_cliente);
        
        return (object) [
            'nombre' => $cliente ? $cliente->nombre_completo : 'Sin datos',
            'id' => $clienteTop->id_cliente,
            'total_gastado' => $clienteTop->total_gastado,
            'total_pedidos' => $clienteTop->total_pedidos
        ];
    }

    /**
     * Calcular ticket promedio de pedidos (status 2 o 3) del mes actual
     */
    private function getTicketPromedioCRM()
    {
        $fechaInicio = \Carbon\Carbon::now()->startOfMonth();
        $fechaFin = \Carbon\Carbon::now()->endOfMonth();
        
        $promedio = OrdenPedido::where('orden_pedido.activo', 1)
            ->whereIn('orden_pedido.status', [2, 3])
            ->whereBetween('orden_pedido.fecha_pedido', [$fechaInicio, $fechaFin])
            ->join('crm_cotizaciones', 'orden_pedido.id_cotizacion', '=', 'crm_cotizaciones.id_cotizacion')
            ->where('crm_cotizaciones.activo', 1)
            ->avg('crm_cotizaciones.importe_total');
        
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
        
        // Obtener cliente para obtener idtarjetaclientefrecuente
        $cliente = Cliente::find($clienteId);
        if (!$cliente || !$cliente->idtarjetaclientefrecuente) {
            return 0;
        }
        
        // Obtener fechas de compras desde historial_ventas_matriz
        $fechasCompras = DB::connection('sqlsrvV')
            ->table('historial_ventas_matriz')
            ->where('IDCLIENTE', $cliente->idtarjetaclientefrecuente)
            ->whereBetween('FECHA_DT', [$fechaInicio, $fechaFin])
            ->select('FECHA_DT')
            ->distinct()
            ->orderBy('FECHA_DT', 'asc')
            ->pluck('FECHA_DT')
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
     * Calcular tasa de conversión del cliente top
     * (Cotizaciones del cliente convertidas en el mes / Cotizaciones del cliente en el mes) * 100
     */
    private function getTasaConversionCRM($clienteId = null)
    {
        if (!$clienteId) {
            return 0;
        }
        
        $fechaInicio = \Carbon\Carbon::now()->startOfMonth();
        $fechaFin = \Carbon\Carbon::now()->endOfMonth();
        
        // 1. Total de cotizaciones del cliente en el mes
        $totalCotizaciones = Cotizacion::where('activo', 1)
            ->where('es_pedido', '!=', 1)
            ->where('id_cliente', $clienteId)
            ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin])
            ->count();
        
        if ($totalCotizaciones == 0) {
            return 0;
        }
        
        // 2. IDs de cotizaciones del cliente que generaron pedidos en el mes
        $idsCotizacionesConvertidas = OrdenPedido::where('activo', 1)
            ->whereIn('status', [2, 3])
            ->whereBetween('fecha_pedido', [$fechaInicio, $fechaFin])
            ->pluck('id_cotizacion')
            ->unique()
            ->toArray();
        
        // 3. Cotizaciones del cliente que se convirtieron y son del mes
        $cotizacionesConvertidasDelMes = Cotizacion::where('activo', 1)
            ->where('id_cliente', $clienteId)
            ->whereIn('id_cotizacion', $idsCotizacionesConvertidas)
            ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin])
            ->count();
        
        return ($cotizacionesConvertidasDelMes / $totalCotizaciones) * 100;
    }

    /**
     * Obtener resumen de ventas mensuales desde historial_ventas_matriz
     */
    private function getResumenVentasMensual()
    {
        try {
            $fechaInicio = \Carbon\Carbon::now()->startOfMonth();
            $fechaFin = \Carbon\Carbon::now()->endOfMonth();
            $mesAnteriorInicio = \Carbon\Carbon::now()->subMonthNoOverflow()->startOfMonth();
            $mesAnteriorFin = \Carbon\Carbon::now()->subMonthNoOverflow()->endOfMonth();
            
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

            // Redondear a 2 decimales en PHP
            $totalGeneral = round($totalGeneral, 2);
            
            // Total mes anterior
            $totalMesAnterior = DB::connection('sqlsrvV')
                ->table('historial_ventas_matriz')
                ->whereBetween('FECHA_DT', [$mesAnteriorInicio, $mesAnteriorFin])
                ->where(function($q) {
                    $q->whereNull('F_STATUS')
                    ->orWhereNotIn('F_STATUS', ['C', 'D']);
                })
                ->where(DB::raw('CAST(F_MONTO AS DECIMAL(18,2))'), '>', 0)
                ->sum(DB::raw('CAST(F_MONTO AS DECIMAL(18,2))'));
            
            // Cálculo correcto del porcentaje de cambio
            $porcentajeCambio = 0;
            if ($totalMesAnterior > 0) {
                $porcentajeCambio = (($totalGeneral - $totalMesAnterior) / $totalMesAnterior) * 100;
            }
            
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
            
            // Calcular porcentajes de participación
            $porcentajeRegistrados = $totalGeneral > 0 ? ($totalRegistrados / $totalGeneral) * 100 : 0;
            $porcentajePublico = $totalGeneral > 0 ? ($totalPublico / $totalGeneral) * 100 : 0;
            
            return (object) [
                'total_general' => $totalGeneral ?? 0,
                'total_anterior' => $totalMesAnterior ?? 0,
                'porcentaje_cambio' => $porcentajeCambio,
                'total_registrados' => $totalRegistrados ?? 0,
                'total_publico' => $totalPublico ?? 0,
                'porcentaje_registrados' => $porcentajeRegistrados,
                'porcentaje_publico' => $porcentajePublico,
                'top_clientes' => $topClientes,
                'ids_publico' => $idsPublico,
            ];
            
        } catch (\Exception $e) {
            \Log::error('Error en getResumenVentasMensual: ' . $e->getMessage());
            return (object) [
                'total_general' => 0,
                'total_anterior' => 0,
                'porcentaje_cambio' => 0,
                'total_registrados' => 0,
                'total_publico' => 0,
                'porcentaje_registrados' => 0,
                'porcentaje_publico' => 0,
                'top_clientes' => collect(),
                'ids_publico' => [],
            ];
        }
    }

    /**
     * Calcular tasa de conversion general (cotizaciones a pedidos)
     * 
     * Tasa = (Cotizaciones convertidas en el mes) / (Total cotizaciones del mes) * 100
     */
    private function getTasaConversionGeneral()
    {
        $fechaInicio = now()->startOfMonth();
        $fechaFin = now()->endOfMonth();
        
        // 1. TOTAL de cotizaciones CREADAS en el mes (INCLUYENDO las que ya son pedidos)
        $totalCotizacionesMes = Cotizacion::where('activo', 1)
            ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin])
            ->count();
        
        // 2. IDs de cotizaciones que generaron pedidos en el mes (status 2 o 3)
        $idsCotizacionesConvertidas = OrdenPedido::where('activo', 1)
            ->whereIn('status', [2, 3])
            ->whereBetween('fecha_pedido', [$fechaInicio, $fechaFin])
            ->pluck('id_cotizacion')
            ->unique()
            ->toArray();
        
        // 3. Cotizaciones convertidas que son del mes actual
        $cotizacionesConvertidasDelMes = Cotizacion::where('activo', 1)
            ->whereIn('id_cotizacion', $idsCotizacionesConvertidas)
            ->whereBetween('fecha_creacion', [$fechaInicio, $fechaFin])
            ->count();
        
        // Calcular tasa
        $tasa = 0;
        if ($totalCotizacionesMes > 0) {
            $tasa = ($cotizacionesConvertidasDelMes / $totalCotizacionesMes) * 100;
        }
        
        return (object) [
            'tasa' => round($tasa, 2),
            'total' => $totalCotizacionesMes,
            'convertidas' => $cotizacionesConvertidasDelMes
        ];
    }

    /**
     * Obtener resumen de pedidos por sucursal del mes actual
     * Para el KPI de pedidos por sucursal
     * Muestra todas las sucursales ordenadas de mayor a menor
     */
    private function getPedidosPorSucursal()
    {
        $fechaInicio = now()->startOfMonth();
        $fechaFin = now()->endOfMonth();
        
        // Obtener todas las sucursales con pedidos (excluyendo cancelados status 4)
        $distribucion = OrdenPedidoSucursal::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->where('status', '!=', 4) // Excluir cancelados
            ->select('id_sucursal', DB::raw('COUNT(*) as total'))
            ->groupBy('id_sucursal')
            ->orderBy('total', 'DESC')
            ->get()
            ->map(function($item) {
                $sucursal = Sucursal::find($item->id_sucursal);
                return (object) [
                    'sucursal' => $sucursal ? $sucursal->nombre : 'N/A',
                    'total' => $item->total
                ];
            });
        
        // Total de pedidos del mes (excluyendo cancelados)
        $total = OrdenPedidoSucursal::whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->where('status', '!=', 4)
            ->count();
        
        // Obtener la sucursal con mas pedidos (primera de la lista)
        $sucursalTop = $distribucion->first();
        
        return (object) [
            'total' => $total,
            'sucursal_top' => $sucursalTop ? $sucursalTop->sucursal : 'N/A',
            'top_count' => $sucursalTop ? $sucursalTop->total : 0,
            'distribucion' => $distribucion // Todas las sucursales ordenadas
        ];
    }

    /**
     * Obtener resumen de ventas por vendedor del mes actual
     * Para el KPI de ventas por vendedor
     */
    private function getVentasPorVendedor()
    {
        $fechaInicio = now()->startOfMonth();
        $fechaFin = now()->endOfMonth();
        
        // Obtener IDs de cotizaciones que generaron pedidos este mes
        $idsCotizacionesConPedidos = OrdenPedido::where('activo', 1)
            ->whereIn('status', [2, 3])
            ->whereBetween('fecha_pedido', [$fechaInicio, $fechaFin])
            ->pluck('id_cotizacion')
            ->unique()
            ->toArray();
        
        if (empty($idsCotizacionesConPedidos)) {
            return (object) [
                'total' => 0,
                'top_vendedor' => 'N/A',
                'total_pedidos' => 0,
                'top_vendedores' => collect()
            ];
        }
        
        // Obtener todos los vendedores con sus montos
        $vendedores = Cotizacion::where('activo', 1)
            ->whereIn('id_cotizacion', $idsCotizacionesConPedidos)
            ->select(
                'creado_por',
                DB::raw('COUNT(id_cotizacion) as total_pedidos'),
                DB::raw('SUM(importe_total) as monto_total')
            )
            ->groupBy('creado_por')
            ->orderBy('monto_total', 'DESC')
            ->get();
        
        // Total de ventas del mes (suma de todos los vendedores)
        $totalVentas = $vendedores->sum('monto_total');
        
        // Total de pedidos del mes
        $totalPedidos = OrdenPedido::where('activo', 1)
            ->whereIn('status', [2, 3])
            ->whereBetween('fecha_pedido', [$fechaInicio, $fechaFin])
            ->count();
        
        // Obtener el top vendedor
        $vendedorTop = $vendedores->first();
        $topVendedor = null;
        $pedidosTop = 0;
        $montoTop = 0;
        
        if ($vendedorTop && $vendedorTop->creado_por) {
            $vendedor = DB::connection('sqlsrvM')
                ->table('personal_empresa')
                ->where('id_personal_empresa', $vendedorTop->creado_por)
                ->first();
            
            if ($vendedor) {
                $topVendedor = trim($vendedor->Nombre . ' ' . $vendedor->ApPaterno . ' ' . ($vendedor->ApMaterno ?? ''));
                $pedidosTop = $vendedorTop->total_pedidos;
                $montoTop = $vendedorTop->monto_total;
            }
        }
        
        // Top 5 vendedores para desglose
        $topVendedores = $vendedores->take(5)->map(function($item) {
            $vendedor = DB::connection('sqlsrvM')
                ->table('personal_empresa')
                ->where('id_personal_empresa', $item->creado_por)
                ->first();
            
            $nombre = $vendedor ? trim($vendedor->Nombre . ' ' . $vendedor->ApPaterno . ' ' . ($vendedor->ApMaterno ?? '')) : 'N/A';
            
            return (object) [
                'nombre' => $nombre,
                'total_pedidos' => $item->total_pedidos,
                'monto_total' => $item->monto_total
            ];
        });
        
        return (object) [
            'total' => $totalVentas,
            'top_vendedor' => $topVendedor ?: 'N/A',
            'pedidos_top' => $pedidosTop,
            'monto_top' => $montoTop,
            'total_pedidos' => $totalPedidos,
            'top_vendedores' => $topVendedores
        ];
    }

    /**
     * Calcular tiempo promedio de cotización a pedido (en horas)
     * Basado en pedidos generados en el mes actual (status 2 o 3)
     */
    private function getTiempoPromedioCotizacionAPedido()
    {
        $fechaInicio = now()->startOfMonth();
        $fechaFin = now()->endOfMonth();
        
        // Obtener pedidos del mes con su cotización asociada
        $pedidos = OrdenPedido::where('activo', 1)
            ->whereIn('status', [2, 3])
            ->whereBetween('fecha_pedido', [$fechaInicio, $fechaFin])
            ->with('cotizacion')
            ->get();
        
        if ($pedidos->isEmpty()) {
            return 0;
        }
        
        $totalHoras = 0;
        $contador = 0;
        
        foreach ($pedidos as $pedido) {
            if ($pedido->cotizacion && $pedido->cotizacion->fecha_creacion) {
                $fechaCotizacion = \Carbon\Carbon::parse($pedido->cotizacion->fecha_creacion);
                $fechaPedido = \Carbon\Carbon::parse($pedido->created_at);
                $totalHoras += $fechaCotizacion->diffInHours($fechaPedido);
                $contador++;
            }
        }
        
        return $contador > 0 ? round($totalHoras / $contador, 1) : 0;
    }

    /**
     * Calcular tiempo promedio de pedido a entrega (en horas)
     * Basado en pedidos completados en el mes actual (status 3)
     */
    private function getTiempoPromedioPedidoAEntrega()
    {
        $fechaInicio = now()->startOfMonth();
        $fechaFin = now()->endOfMonth();
        
        // Obtener pedidos completados del mes con fecha_entrega_real
        $pedidos = OrdenPedido::where('activo', 1)
            ->where('status', 3)
            ->whereNotNull('fecha_entrega_real')
            ->whereBetween('fecha_pedido', [$fechaInicio, $fechaFin])
            ->get();
        
        // Si no hay pedidos completados, intentar con los que tienen fecha_entrega_real
        if ($pedidos->isEmpty()) {
            // Buscar en orden_pedido_sucursal con folio_ticket para relación con oper_recorridos_choferes
            $pedidosSucursal = OrdenPedidoSucursal::whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->whereNotNull('folio_ticket')
                ->where('status', 3)
                ->get();
            
            if ($pedidosSucursal->isEmpty()) {
                return 0;
            }
            
            $totalHoras = 0;
            $contador = 0;
            
            foreach ($pedidosSucursal as $pedidoSuc) {
                // Buscar en oper_recorridos_choferes por folio_ticket
                $recorrido = DB::connection('sqlsrvM')
                    ->table('oper_recorridos_choferes')
                    ->where('folio_ticket', $pedidoSuc->folio_ticket)
                    ->whereNotNull('hora_regreso')
                    ->first();
                
                if ($recorrido) {
                    $fechaPedido = \Carbon\Carbon::parse($pedidoSuc->created_at);
                    $fechaEntrega = \Carbon\Carbon::parse($recorrido->hora_regreso);
                    $totalHoras += $fechaPedido->diffInHours($fechaEntrega);
                    $contador++;
                }
            }
            
            return $contador > 0 ? round($totalHoras / $contador, 1) : 0;
        }
        
        // Calcular promedio con fecha_entrega_real
        $totalHoras = 0;
        $contador = 0;
        
        foreach ($pedidos as $pedido) {
            if ($pedido->fecha_entrega_real) {
                $fechaPedido = \Carbon\Carbon::parse($pedido->created_at);
                $fechaEntrega = \Carbon\Carbon::parse($pedido->fecha_entrega_real);
                $totalHoras += $fechaPedido->diffInHours($fechaEntrega);
                $contador++;
            }
        }
        
        return $contador > 0 ? round($totalHoras / $contador, 1) : 0;
    }
}