<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PersonalEmpresa;
use App\Models\PermisoPersonal;
use App\Models\Catalogo\Accion;
use App\Models\Catalogo\ModuloClientes;
use App\Models\Catalogo\ModuloVentas;
use App\Models\Catalogo\ModuloSeguridad;
use App\Models\Catalogo\ModuloReportes;
use Illuminate\Support\Facades\DB;

class PermisosPorDefectoSeeder extends Seeder
{
    public function run(): void
    {
        // Crear módulos base para el usuario William035
        $user = PersonalEmpresa::where('usuario', 'William035')->first();
        
        if ($user) {
            // Crear módulo clientes
            $moduloClientes = ModuloClientes::create([
                'clientes' => true,
                'enfermedades' => true,
                'intereses' => true
            ]);
            
            // Crear módulo ventas
            $moduloVentas = ModuloVentas::create([
                'cotizaciones' => true,
                'pedidos_anticipo' => true,
                'seguimiento_ventas' => true,
                'seguimiento_cotizaciones' => true,
                'agenda_contactos' => true
            ]);
            
            // Crear módulo seguridad
            $moduloSeguridad = ModuloSeguridad::create([
                'usuarios' => true,
                'permisos' => true,
                'respaldos' => true
            ]);
            
            // Crear módulo reportes
            $moduloReportes = ModuloReportes::create([
                'compras_cliente' => true,
                'frecuencia_compra' => true,
                'montos_promedio' => true,
                'sucursales_preferidas' => true,
                'cotizaciones_cliente' => true,
                'cotizaciones_concretadas' => true
            ]);
            
            // Obtener todas las acciones
            $acciones = Accion::all();
            
            // Asignar permisos para clientes (para cada acción)
            foreach ($acciones as $accion) {
                PermisoPersonal::create([
                    'id_personal_empresa' => $user->id_personal_empresa,
                    'id_cliente_modulo' => $moduloClientes->id_cliente_modulo,
                    'id_accion' => $accion->id_accion,
                    'permitido' => true
                ]);
            }
            
            // Asignar permisos para ventas
            foreach ($acciones as $accion) {
                PermisoPersonal::create([
                    'id_personal_empresa' => $user->id_personal_empresa,
                    'id_ventas_modulo' => $moduloVentas->id_ventas_modulo,
                    'id_accion' => $accion->id_accion,
                    'permitido' => true
                ]);
            }
            
            // Asignar permisos para seguridad
            foreach ($acciones as $accion) {
                PermisoPersonal::create([
                    'id_personal_empresa' => $user->id_personal_empresa,
                    'id_seguridad_modulo' => $moduloSeguridad->id_seguridad_modulo,
                    'id_accion' => $accion->id_accion,
                    'permitido' => true
                ]);
            }
            
            // Asignar permisos para reportes
            foreach ($acciones as $accion) {
                PermisoPersonal::create([
                    'id_personal_empresa' => $user->id_personal_empresa,
                    'id_reportes_modulo' => $moduloReportes->id_reportes_modulo,
                    'id_accion' => $accion->id_accion,
                    'permitido' => true
                ]);
            }
        }
    }
}