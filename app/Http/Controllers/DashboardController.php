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
        
        // Obtener todos los submódulos donde el usuario tiene mostrar = true
        $submodulosVisibles = $user->permisosGranulares()
            ->where('mostrar', true)
            ->get();
        
        // Si no tiene ningún submódulo visible, mostrar sin-acceso
        if ($submodulosVisibles->isEmpty()) {
            return view('dashboard.sin-acceso', [
                'usuario' => $user->nombre_completo
            ]);
        }
        
        // Verificar si tiene al menos un submódulo con ver = true
        $tieneAlgunVer = $submodulosVisibles->contains(function($permiso) {
            return $permiso->ver === true;
        });
        
        // Si tiene mostrar pero ningún ver, mostrar sin-acceso
        if (!$tieneAlgunVer) {
            return view('dashboard.sin-acceso', [
                'usuario' => $user->nombre_completo
            ]);
        }
        
        // Verificar permisos específicos para cada card del dashboard
        $mostrarCardClientes = false;
        $mostrarCardCotizaciones = false;
        
        // Buscar el permiso específico para clientes -> directorio
        $permisoDirectorio = $submodulosVisibles->first(function($permiso) {
            return $permiso->modulo === 'clientes' && $permiso->submodulo === 'directorio';
        });
        
        if ($permisoDirectorio && $permisoDirectorio->ver === true) {
            $mostrarCardClientes = true;
        }
        
        // Buscar el permiso específico para ventas -> cotizaciones
        $permisoCotizaciones = $submodulosVisibles->first(function($permiso) {
            return $permiso->modulo === 'ventas' && $permiso->submodulo === 'cotizaciones';
        });
        
        if ($permisoCotizaciones && $permisoCotizaciones->ver === true) {
            $mostrarCardCotizaciones = true;
        }
        
        // Si no tiene ningún card para mostrar, mostrar sin-acceso
        if (!$mostrarCardClientes && !$mostrarCardCotizaciones) {
            return view('dashboard.sin-acceso', [
                'usuario' => $user->nombre_completo
            ]);
        }
        
        // ============================================
        // DATOS REALES DESDE LA BASE DE DATOS
        // ============================================
        
        // Total de clientes activos (excluye bloqueados)
        $totalClientes = Cliente::where('status', '!=', 'BLOQUEADO')
            ->whereNotNull('status')
            ->count();
        
        // Total de cotizaciones activas
        $totalCotizaciones = Cotizacion::where('activo', 1)->count();
        
        // Cotizaciones pendientes (fase "En proceso" - id_fase = 1)
        $cotizacionesPendientes = Cotizacion::where('activo', 1)
            ->where('id_fase', 1)
            ->count();
        
        // Contactos próximos (placeholder - se implementará con agenda)
        $contactosProximos = 0;
        
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
        
        $porcentajeCambio = 0;
        if ($montosMesAnterior > 0) {
            $porcentajeCambio = (($montosEsteMes - $montosMesAnterior) / $montosMesAnterior) * 100;
        }
        
        // Calcular variación para cotizaciones totales
        $cotizacionesMesAnterior = Cotizacion::where('activo', 1)
            ->whereMonth('fecha_creacion', $mesAnterior->month)
            ->whereYear('fecha_creacion', $mesAnterior->year)
            ->count();
        
        $porcentajeCotizaciones = 0;
        if ($cotizacionesMesAnterior > 0) {
            $porcentajeCotizaciones = (($totalCotizaciones - $cotizacionesMesAnterior) / $cotizacionesMesAnterior) * 100;
        }
        
        // Últimos contactos (placeholder)
        $ultimosContactos = [];
        
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
        
        // Si no hay cotizaciones, usar array vacío
        if ($ultimasCotizaciones->isEmpty()) {
            $ultimasCotizaciones = [];
        }
        
        // Calcular porcentaje de conversión (aceptadas / total)
        $tasaConversion = 0;
        if ($totalCotizaciones > 0) {
            $tasaConversion = ($estadosCotizaciones['aceptadas'] / $totalCotizaciones) * 100;
        }
        
        // Obtener cliente con más compras (placeholder)
        $clienteTop = 'Jorge Hernández';
        
        // Ticket promedio (placeholder)
        $ticketPromedio = 330.00;
        
        // Frecuencia promedio (placeholder)
        $frecuenciaPromedio = 18;
        
        $modulosAcceso = $user->modulosConAcceso();

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
            "tasaConversion",
            "clienteTop",
            "ticketPromedio",
            "frecuenciaPromedio"
        ));
    }
}