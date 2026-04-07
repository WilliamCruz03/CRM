<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cliente;
use App\Models\Cotizaciones\Cotizacion;

class DashboardController extends Controller
{
    public function index()
    {
        // Verificar autenticación - si no está logueado, redirigir al login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // ============================================
        // VERIFICAR SI TIENE ALGÚN PERMISO EN GENERAL
        // ============================================
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
            return view('dashboard.sin-acceso', [
                'usuario' => $user->nombre_completo
            ]);
        }
        
        // ============================================
        // DEFINIR MÓDULOS QUE SE MUESTRAN EN EL DASHBOARD
        // ============================================
        $modulosDashboard = [
            'clientes' => ['submodulo' => 'directorio'],
            'ventas' => ['submodulo' => 'cotizaciones']
        ];
        
        // ============================================
        // OBTENER PERMISOS SOLO PARA MÓDULOS DEL DASHBOARD
        // ============================================
        $permisosDashboard = $user->permisosGranulares()
            ->where(function($query) use ($modulosDashboard) {
                foreach ($modulosDashboard as $modulo => $config) {
                    $query->orWhere(function($q) use ($modulo, $config) {
                        $q->where('modulo', $modulo)
                          ->where('submodulo', $config['submodulo']);
                    });
                }
            })
            ->where(function($query) {
                $query->where('mostrar', true)
                    ->orWhere('ver', true)
                    ->orWhere('crear', true)
                    ->orWhere('editar', true)
                    ->orWhere('eliminar', true);
            })
            ->get();
        
        // ============================================
        // VERIFICAR CARDS DEL DASHBOARD
        // ============================================
        
        // Verificar si tiene algún permiso en clientes (directorio)
        $permisoDirectorio = $permisosDashboard->first(function($permiso) {
            return $permiso->modulo === 'clientes' && $permiso->submodulo === 'directorio';
        });
        
        // Mostrar card de clientes si tiene AL MENOS UN permiso activo
        $mostrarCardClientes = $permisoDirectorio ? true : false;
        
        // Verificar si tiene algún permiso en ventas (cotizaciones)
        $permisoCotizaciones = $permisosDashboard->first(function($permiso) {
            return $permiso->modulo === 'ventas' && $permiso->submodulo === 'cotizaciones';
        });
        
        // Mostrar card de cotizaciones si tiene AL MENOS UN permiso activo
        $mostrarCardCotizaciones = $permisoCotizaciones ? true : false;
        
        // ============================================
        // PERMISOS PARA ACCIONES DENTRO DE CADA CARD
        // ============================================
        
        // Permisos específicos para clientes (directorio)
        $permisosClientes = [
            'ver' => $permisoDirectorio && $permisoDirectorio->ver === true,
            'crear' => $permisoDirectorio && $permisoDirectorio->crear === true,
            'editar' => $permisoDirectorio && $permisoDirectorio->editar === true,
            'eliminar' => $permisoDirectorio && $permisoDirectorio->eliminar === true,
            'mostrar' => $permisoDirectorio && $permisoDirectorio->mostrar === true,
        ];
        
        // Permisos específicos para cotizaciones
        $permisosCotizaciones = [
            'ver' => $permisoCotizaciones && $permisoCotizaciones->ver === true,
            'crear' => $permisoCotizaciones && $permisoCotizaciones->crear === true,
            'editar' => $permisoCotizaciones && $permisoCotizaciones->editar === true,
            'eliminar' => $permisoCotizaciones && $permisoCotizaciones->eliminar === true,
            'mostrar' => $permisoCotizaciones && $permisoCotizaciones->mostrar === true,
        ];
        
        // ============================================
        // DATOS REALES DESDE LA BASE DE DATOS
        // ============================================
        
        // Total de clientes activos (solo si tiene permiso de ver)
        $totalClientes = 0;
        if ($permisosClientes['ver']) {
            $totalClientes = Cliente::where('status', '!=', 'BLOQUEADO')
                ->whereNotNull('status')
                ->count();
        }
        
        // Total de cotizaciones activas (solo si tiene permiso de ver)
        $totalCotizaciones = 0;
        $cotizacionesPendientes = 0;
        $estadosCotizaciones = ['aceptadas' => 0, 'pendientes' => 0, 'rechazadas' => 0];
        $montosEsteMes = 0;
        $porcentajeCambio = 0;
        $porcentajeCotizaciones = 0;
        $ultimasCotizaciones = [];
        $tasaConversion = 0;
        
        if ($permisosCotizaciones['ver']) {
            $totalCotizaciones = Cotizacion::where('activo', 1)->count();
            
            // Cotizaciones pendientes (fase "En proceso" - id_fase = 1)
            $cotizacionesPendientes = Cotizacion::where('activo', 1)
                ->where('id_fase', 1)
                ->count();
            
            // Estados de cotizaciones
            $estadosCotizaciones = [
                "aceptadas" => Cotizacion::where('activo', 1)
                    ->where('id_fase', 2) // Completada
                    ->count(),
                "pendientes" => Cotizacion::where('activo', 1)
                    ->where('id_fase', 1) // En proceso
                    ->count(),
                "rechazadas" => Cotizacion::where('activo', 1)
                    ->where('id_fase', 3) // Cancelada
                    ->count()
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
            
            // Calcular porcentaje de conversión (aceptadas / total)
            if ($totalCotizaciones > 0) {
                $tasaConversion = ($estadosCotizaciones['aceptadas'] / $totalCotizaciones) * 100;
            }
        }
        
        // Datos placeholder para métricas
        $contactosProximos = 0;
        $ultimosContactos = [];
        $clienteTop = 'Jorge Hernández';
        
        // Ticket promedio (placeholder)
        $ticketPromedio = 330.00;
        
        // Frecuencia promedio (placeholder)
        $frecuenciaPromedio = 18;
        
        // Obtener módulos con acceso para mostrar en el header
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
            "tieneAlgunPermiso"
        ));
    }
}