<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cliente;
use App\Models\Cotizaciones\Cotizacion;
use App\Models\DashboardPreferencia;

class DashboardController extends Controller
{
    // Definición central de todos los cards disponibles
    private $cardsDisponibles = [
        // Cards de acceso
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
    ];

    public function index()
    {
        // Verificar autenticación - si no está logueado, redirigir al login
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
        
        // Si no tiene NINGÚN permiso en el sistema, mostrar sin-acceso
        if (!$tieneAlgunPermiso) {
            return view('dashboard.sin-acceso', ['usuario' => $user->nombre_completo]);
        }
        
        // Obtener preferencias del dashboard para este usuario
        $preferencias = DashboardPreferencia::where('id_personal_empresa', $user->id_personal_empresa)
            ->where('mostrar', true)
            ->pluck('card_key')
            ->toArray();
        
        // Si no tiene preferencias, usar todas por defecto
        if (empty($preferencias)) {
            $preferencias = array_column($this->cardsDisponibles, 'key');
        }
        
        // Verificar qué cards mostrar basado en permisos de módulos
        $permisoDirectorio = $user->permisosGranulares()
            ->where('modulo', 'clientes')
            ->where('submodulo', 'directorio')
            ->first();
        
        $permisoCotizaciones = $user->permisosGranulares()
            ->where('modulo', 'ventas')
            ->where('submodulo', 'cotizaciones')
            ->first();
        
        $tienePermisoClientes = $permisoDirectorio && ($permisoDirectorio->ver || $permisoDirectorio->crear || $permisoDirectorio->editar);
        $tienePermisoVentas = $permisoCotizaciones && ($permisoCotizaciones->ver || $permisoCotizaciones->crear || $permisoCotizaciones->editar);
        
        // Preparar datos para cada card
        $datosCards = [];
        foreach ($this->cardsDisponibles as $card) {
            $cardKey = $card['key'];
            
            // Solo mostrar cards que el usuario tiene en sus preferencias
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
            
            // Cargar datos según el tipo de card
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
        $mostrarCardClientes = isset($datosCards['acceso_clientes']);
        $mostrarCardCotizaciones = isset($datosCards['acceso_cotizaciones']);
        
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
        
        // Datos reales desde la base de datos
        $totalClientes = $tienePermisoClientes ? Cliente::where('status', 'CLIENTE')->count() : 0;
        
        $totalCotizaciones = 0;
        $cotizacionesPendientes = 0;
        $estadosCotizaciones = ['aceptadas' => 0, 'pendientes' => 0, 'rechazadas' => 0];
        $montosEsteMes = 0;
        $porcentajeCambio = 0;
        $porcentajeCotizaciones = 0;
        $ultimasCotizaciones = [];
        $tasaConversion = 0;
        
        if ($tienePermisoVentas) {
            $totalCotizaciones = Cotizacion::where('activo', 1)->count();
            $cotizacionesPendientes = Cotizacion::where('activo', 1)->where('id_fase', 1)->count();
            $estadosCotizaciones = [
                "aceptadas" => Cotizacion::where('activo', 1)->where('id_fase', 2)->count(),
                "pendientes" => Cotizacion::where('activo', 1)->where('id_fase', 1)->count(),
                "rechazadas" => Cotizacion::where('activo', 1)->where('id_fase', 3)->count()
            ];
            
            // Monto total de cotizaciones este mes
            $montosEsteMes = Cotizacion::where('activo', 1)
                ->whereMonth('fecha_creacion', now()->month)
                ->whereYear('fecha_creacion', now()->year)
                ->sum('importe_total');
            
            // Calcular variación porcentual vs mes anterior
            $mesAnterior = now()->subMonth();
            $montosMesAnterior = Cotizacion::where('activo', 1)
                ->whereMonth('fecha_creacion', $mesAnterior->month)
                ->whereYear('fecha_creacion', $mesAnterior->year)
                ->sum('importe_total');
            
            if ($montosMesAnterior > 0) {
                $porcentajeCambio = (($montosEsteMes - $montosMesAnterior) / $montosMesAnterior) * 100;
            }
            
            // Calcular variación para cotizaciones totales
            $cotizacionesMesAnterior = Cotizacion::where('activo', 1)
                ->whereMonth('fecha_creacion', $mesAnterior->month)
                ->whereYear('fecha_creacion', $mesAnterior->year)
                ->count();
            
            if ($cotizacionesMesAnterior > 0) {
                $porcentajeCotizaciones = (($totalCotizaciones - $cotizacionesMesAnterior) / $cotizacionesMesAnterior) * 100;
            }
            
            // Últimas cotizaciones (últimas 3)
            $ultimasCotizaciones = Cotizacion::with('cliente', 'fase')
                ->where('activo', 1)
                ->orderBy('fecha_creacion', 'desc')
                ->limit(3)
                ->get()
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
            
            if ($totalCotizaciones > 0) {
                $tasaConversion = ($estadosCotizaciones['aceptadas'] / $totalCotizaciones) * 100;
            }
        }
        
        // Datos placeholder
        $contactosProximos = 0;
        $ultimosContactos = [];
        $clienteTop = 'Jorge Hernández';
        
        // Ticket promedio (placeholder)
        $ticketPromedio = 330.00;
        
        // Frecuencia promedio (placeholder)
        $frecuenciaPromedio = 18;
        
        return view("dashboard.index", compact(
            "totalClientes",
            "totalCotizaciones",
            "cotizacionesPendientes",
            "contactosProximos",
            "estadosCotizaciones",
            "montosEsteMes",
            "porcentajeCambio",
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
            "datosCards"
        ));
    }
    
    private function cargarDatosCard($cardKey, $card, $user)
    {
        // Aquí puedes cargar datos específicos para cada card si es necesario
        // Por ahora retornamos el card con sus datos básicos
        return $card;
    }
}